<?php

namespace Tests\Feature\Performance;

use App\Models\Sales;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\SupplierPayment;
use App\Models\User;
use App\Models\UserSession;
use App\Models\UserSessionPair;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthUser;

class PerformanceAnalysisTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthUser;

    public function test_sales_summary_returns_today_week_and_month_totals()
    {
        $this->actingAsAdmin();
        Carbon::setTestNow(Carbon::parse('2025-09-30 10:00:00'));
        Sales::factory()->count(3)->create([
            'total_price' => 30,
            'invoice_date' => Carbon::today(),
        ]);
        Sales::factory()->create([
            'total_price' => 100,
            'invoice_date' => Carbon::parse('2025-08-15'),
        ]);

        $response = $this->getJson('/api/admin/performanceAnalysis');

        $response->assertOk()
            ->assertJsonPath('sales_summary.today', 90)
            ->assertJsonPath('sales_summary.week', 90)
            ->assertJsonPath('sales_summary.month', 90)
            ->assertJsonPath('sales_summary.month_name', 'September');
    }

    public function test_sales_summary_handles_no_sales_gracefully()
    {
        $this->actingAsAdmin();
        Carbon::setTestNow(Carbon::parse('2025-09-30'));

        $response = $this->getJson('/api/admin/performanceAnalysis');

        $response->assertOk()
            ->assertJsonPath('sales_summary.today', 0)
            ->assertJsonPath('sales_summary.week', 0)
            ->assertJsonPath('sales_summary.month', 0)
            ->assertJsonPath('sales_summary.month_name', 'September');
    }

    public function test_supplier_payments_are_grouped_and_summed_correctly()
    {
        $this->actingAsAdmin();
        $supplier1 = Supplier::factory()->create(['name' => 'Supplier A']);
        $supplier2 = Supplier::factory()->create(['name' => 'Supplier B']);
        SupplierPayment::factory()->create(['supplier_id' => $supplier1->id, 'amount' => 100]);
        SupplierPayment::factory()->create(['supplier_id' => $supplier1->id, 'amount' => 50]);
        SupplierPayment::factory()->create(['supplier_id' => $supplier2->id, 'amount' => 200]);

        $response = $this->getJson('/api/admin/performanceAnalysis');

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $supplier1->id,
                'name_supplier' => 'Supplier A',
                'payment_supplier' => 150.0,
            ])
            ->assertJsonFragment([
                'id' => $supplier2->id,
                'name_supplier' => 'Supplier B',
                'payment_supplier' => 200.0,
            ]);
    }

    public function test_total_paid_to_suppliers_is_calculated_correctly()
    {
        $this->actingAsAdmin();

        $supplier1 = Supplier::factory()->create();
        $supplier2 = Supplier::factory()->create();
        SupplierPayment::factory()->create(['supplier_id' => $supplier1->id, 'amount' => 100]);
        SupplierPayment::factory()->create(['supplier_id' => $supplier2->id, 'amount' => 200]);

        $response = $this->getJson('/api/admin/performanceAnalysis');

        $response->assertOk()
            ->assertJsonPath('performance_analysis.total_paid_to_suppliers', 300);
    }

    public function test_supplier_payments_include_supplier_name_and_id()
    {
        $this->actingAsAdmin();
        $supplier = Supplier::factory()->create(['name' => 'Supplier X']);
        SupplierPayment::factory()->create(['supplier_id' => $supplier->id, 'amount' => 120]);

        $response = $this->getJson('/api/admin/performanceAnalysis');

        $response->assertOk()
            ->assertJsonStructure([
                'supplier_payments' => [
                    [
                        'id',
                        'name_supplier',
                        'payment_supplier',
                    ]
                ]
            ])
            ->assertJsonFragment([
                'id' => $supplier->id,
                'name_supplier' => 'Supplier X',
                'payment_supplier' => 120.0,
            ]);
    }

    public function test_total_supplier_orders_cost_is_calculated_for_current_month()
    {
        $this->actingAsAdmin();
        $supplier = Supplier::factory()->create();
        SupplierOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'order_date' => Carbon::now()->subDays(3),
            'total_price' => 500.00,
        ]);
        SupplierOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'order_date' => Carbon::now()->subMonth(),
            'total_price' => 1000.00,
        ]);

        $response = $this->getJson('/api/admin/performanceAnalysis');

        $response->assertOk()
            ->assertJsonPath('performance_analysis.supplier_orders_cost', 500);
    }

    public function test_pharmacist_work_hours_are_calculated_from_session_pairs()
    {
        $this->actingAsAdmin();
        $pharmacist = User::factory()->create(['name' => 'John', 'hourly_rate' => 20]);
        $login = UserSession::factory()->create(['user_id' => $pharmacist->id, 'occurred_at' => Carbon::now()->subHours(3)]);
        $logout = UserSession::factory()->create(['user_id' => $pharmacist->id, 'occurred_at' => Carbon::now()->subHours(1)]);
        UserSessionPair::factory()->create(['user_id' => $pharmacist->id, 'login_session_id' => $login->id, 'logout_session_id' => $logout->id]);

        $response = $this->getJson('/api/admin/performanceAnalysis');

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $pharmacist->id,
                'name' => 'John',
                'total_hours' => 2,
            ])
            ->assertJsonPath('pharmacists_work_hours.total_hours', 2);
    }

    public function test_total_pharmacist_salaries_are_computed_correctly()
    {
        $this->actingAsAdmin();
        $pharmacist = User::factory()->create(['hourly_rate' => 30]);
        $login = UserSession::factory()->create(['user_id' => $pharmacist->id, 'occurred_at' => Carbon::now()->subHours(5)]);
        $logout = UserSession::factory()->create(['user_id' => $pharmacist->id, 'occurred_at' => Carbon::now()->subHours(1)]);
        UserSessionPair::factory()->create(['user_id' => $pharmacist->id, 'login_session_id' => $login->id, 'logout_session_id' => $logout->id]);

        $response = $this->getJson('/api/admin/performanceAnalysis');

        $response->assertOk()
            ->assertJsonPath('performance_analysis.total_pharmacist_salaries', 120);
    }

    public function test_profit_estimate_is_sales_minus_costs_and_salaries()
    {
        $this->actingAsAdmin();
        Sales::factory()->create([
            'invoice_date' => Carbon::now(),
            'total_price' => 1000,
        ]);
        $supplier = Supplier::factory()->create();
        SupplierOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'order_date' => Carbon::now(),
            'total_price' => 300,
        ]);
        $pharmacist = User::factory()->create(['hourly_rate' => 20]);
        $login = UserSession::factory()->create(['user_id' => $pharmacist->id, 'occurred_at' => Carbon::now()->subHours(11)]);
        $logout = UserSession::factory()->create(['user_id' => $pharmacist->id, 'occurred_at' => Carbon::now()->subHours(1)]);
        UserSessionPair::factory()->create(['user_id' => $pharmacist->id, 'login_session_id' => $login->id, 'logout_session_id' => $logout->id]);

        $response = $this->getJson('/api/admin/performanceAnalysis');

        $response->assertOk()
            ->assertJsonPath('performance_analysis.profit_estimate', 500);
    }
}
