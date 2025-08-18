<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sales;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use App\Models\UserSessionPair;
use Carbon\Carbon;

class PerformanceAnalysisController extends Controller
{
    public function __invoke()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::SUNDAY);
        $startOfMonth = Carbon::now()->startOfMonth();

        // Sales 
        $sales_today = (float) Sales::whereDate('invoice_date', $today)->sum('total_price');
        $sales_week = (float) Sales::whereBetween('invoice_date', [$startOfWeek, Carbon::now()])
            ->sum('total_price');
        $sales_month = (float) Sales::whereBetween('invoice_date', [$startOfMonth, Carbon::now()])
            ->sum('total_price');

        // Supplier Payments 
        $supplier_payments = SupplierPayment::with('supplier')
            ->selectRaw('supplier_id, SUM(amount) as total_paid')
            ->groupBy('supplier_id')
            ->get()
            ->map(fn($payment) => [
                'id' => $payment->supplier->id,
                $payment->supplier->name => (float) $payment->total_paid
            ]);

        $total_paid_to_suppliers = (float) SupplierPayment::sum('amount');

        // Total cost of supplier orders for the month
        $total_supplier_orders_cost = 0;
        $supplier_orders_cost_by_supplier = Supplier::with(['orders.items'])
            ->get()->map(function ($supplier) use ($startOfMonth, &$total_supplier_orders_cost) {
                $total_cost = 0;

                foreach ($supplier->orders->whereBetween('order_date', [$startOfMonth, Carbon::now()]) as $order) {
                    $total_cost += $order->total_price;
                }

                $total_supplier_orders_cost += $total_cost;

                return [$supplier->name => $total_cost];
            });

        $suppliers_total_balance = (float) Supplier::sum('balance');

        // Pharmacist Hours + Salary
        $total_pharmacist_salaries = 0;
        $pharmacists_work_data = User::whereNotNull('hourly_rate')->get()->map(function ($user) use ($startOfMonth, &$total_pharmacist_salaries) {
            $pairs = UserSessionPair::with(['login', 'logout'])
                ->where('user_id', $user->id)
                ->whereHas('login', fn($q) => $q->where('occurred_at', '>=', $startOfMonth))
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
                'id'=>$user->id,
                'name' => $user->name,
                'total_hours' => $total_hours
            ];
        });

        $total_pharmacists_hours = $pharmacists_work_data->sum('total_hours');

        // Performance Analysis
        $performance_analysis = [
            'sales_month' => $sales_month,
            'supplier_orders_cost' => round($total_supplier_orders_cost, 2),
            'total_paid_to_suppliers' => $total_paid_to_suppliers,
            'suppliers_total_balance' => $suppliers_total_balance,
            'total_pharmacist_salaries' => $total_pharmacist_salaries,
            'profit_estimate' => round($sales_month - ($total_supplier_orders_cost + $total_pharmacist_salaries), 2)
        ];

        $data = [
            'supplier_payments' => $supplier_payments,
            'sales_summary' => [
                'today' => $sales_today,
                'week' => $sales_week,
                'month' => $sales_month,
                'month_name' => $startOfMonth->format('F')
            ],
            'pharmacists_work_hours' => [
                'individual' => $pharmacists_work_data,
                'total_hours' => $total_pharmacists_hours
            ],
            'performance_analysis' => $performance_analysis
        ];

        return response()->json($data);
    }
}
