<?php

namespace App\Services\PerformanceAnalysis;

use App\Models\Sales;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\SupplierPayment;
use App\Models\User;
use App\Models\UserSessionPair;
use Carbon\Carbon;

class PerformanceAnalysisService
{
    public function getDateRanges(): array
    {
        return [
            'today' => Carbon::today(),
            'startOfWeek' => Carbon::now()->startOfWeek(Carbon::SUNDAY),
            'startOfMonth' => Carbon::now()->startOfMonth(),
            'now' => Carbon::now()
        ];
    }

    public function getSalesSummary(array $dates): array
    {
        return [
            'today' => (float) Sales::whereDate('invoice_date', $dates['today'])->sum('total_price'),
            'week' => (float) Sales::whereBetween('invoice_date', [$dates['startOfWeek'], $dates['now']])
                ->sum('total_price'),
            'month' => (float) Sales::whereBetween('invoice_date', [$dates['startOfMonth'], $dates['now']])
                ->sum('total_price'),
            'month_name' => $dates['startOfMonth']->format('F')
        ];
    }

    public function getSupplierPaymentsList()
    {

        return
            SupplierPayment::with('supplier')
            ->selectRaw('supplier_id, SUM(amount) as total_paid')
            ->groupBy('supplier_id')
            ->get()
            ->map(fn($payment) => [
                'id' => $payment->supplier->id,
                'name_supplier' => $payment->supplier->name,
                'payment_supplier' => (float) $payment->total_paid
            ])->values();
    }

    public function getTotalPaidToSuppliers()
    {
        return (float) SupplierPayment::sum('amount');
    }

    public function getTotalSupplierOrdersCost(array $dates): float
    {
        $total_supplier_orders_cost = (float) SupplierOrder::whereBetween(
            'order_date',
            [$dates['startOfMonth'], $dates['now']]
        )->sum('total_price');

        return  round($total_supplier_orders_cost, 2);
    }

    public function getSupplierOrdersCostBySupplier(array $dates): array
    {
        return
            SupplierOrder::with('supplier')
            ->whereBetween('order_date', [$dates['startOfMonth'], $dates['now']])
            ->get()
            ->groupBy('supplier_id')
            ->map(function ($orders, $supplierId) {
                return [
                    'supplier_id' => $supplierId,
                    'supplier_name' => optional($orders->first()->supplier)->name,
                    'total_cost' => (float) $orders->sum('total_price'),
                ];
            })->values();
    }

    public function getSuppliersTotalBalance(): float
    {
        return (float) Supplier::sum('balance');
    }

    public function getPharmacistsWorkData(array $dates): array
    {
        $total_pharmacist_salaries = 0;
        $pharmacists_work_data = User::whereNotNull('hourly_rate')->get()->map(function ($user) use ($dates, &$total_pharmacist_salaries) {
            $pairs = UserSessionPair::with(['login', 'logout'])
                ->where('user_id', $user->id)
                ->whereHas('login', fn($q) => $q->where('occurred_at', '>=', $dates['startOfMonth']))
                ->get();

            $total_hours = $pairs->reduce(function ($carry, $pair) {
                if ($pair->logout) {
                    $login = Carbon::parse($pair->login->occurred_at);
                    $logout = Carbon::parse($pair->logout->occurred_at);
                    $carry += $login->diffInMinutes($logout) / 60;
                }
                return $carry;
            }, 0);

            $total_hours = floor($total_hours);

            $total_pharmacist_salaries += round($total_hours * $user->hourly_rate, 2);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'total_hours' => $total_hours
            ];
        });

        $total_pharmacists_hours = $pharmacists_work_data->sum('total_hours');

        return [
            'individual' => $pharmacists_work_data,
            'total_hours' => $total_pharmacists_hours,
            'total_salaries' => $total_pharmacist_salaries
        ];
    }

    public function getPerformanceAnalysis(): array
    {
        $dates = $this->getDateRanges();
        $sales_month = $this->getSalesSummary($dates)['month'];
        $total_suppliers_orders_cost = $this->getTotalSupplierOrdersCost($dates);
        $total_pharmacist_salaries =  $this->getPharmacistsWorkData($dates)['total_salaries'];
        $payments = $this->getTotalPaidToSuppliers();
        $total_balance = $this->getSuppliersTotalBalance();

        return   [
            'sales_month' => $sales_month,
            'supplier_orders_cost' => $total_suppliers_orders_cost,
            'total_paid_to_suppliers' => $payments,
            'suppliers_total_balance' => $total_balance,
            'total_pharmacist_salaries' => $total_pharmacist_salaries,
            'profit_estimate' => round($sales_month - ($total_suppliers_orders_cost + $total_pharmacist_salaries), 2)
        ];
    }

    public function getFullAnalysis(): array
    {
        $dates = $this->getDateRanges();

        return [
            'supplier_payments' => $this->getSupplierPaymentsList(),
            'sales_summary' => $this->getSalesSummary($dates),
            'pharmacists_work_hours' => $this->getPharmacistsWorkData($dates),
            'performance_analysis' => $this->getPerformanceAnalysis()
        ];
    }
}
