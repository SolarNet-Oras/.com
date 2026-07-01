<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Router;
use App\Models\ServicePlan;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InvoiceTestSeeder extends Seeder
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Run the database seeds for invoice testing
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding test data for Invoice & Payment testing...');

        // Create a test router
        $router = Router::firstOrCreate(
            ['host' => '192.168.1.1'],
            [
                'name' => 'Test MikroTik Router',
                'username' => 'admin',
                'password' => 'testpass',
                'port' => 8728,
                'is_active' => true,
                'connection_status' => 'online',
            ]
        );
        $this->command->info('✅ Router created: ' . $router->name);

        // Create service plans
        $plans = [
            [
                'name' => 'Basic 10Mbps',
                'price' => 25.00,
                'download_speed' => 10,
                'upload_speed' => 2,
                'priority' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Standard 25Mbps',
                'price' => 40.00,
                'download_speed' => 25,
                'upload_speed' => 5,
                'priority' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Premium 50Mbps',
                'price' => 75.00,
                'download_speed' => 50,
                'upload_speed' => 10,
                'priority' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $planData) {
            $plan = ServicePlan::firstOrCreate(
                ['name' => $planData['name']],
                $planData
            );
            $this->command->info('✅ Service Plan created: ' . $plan->name . ' ($' . $plan->price . '/mo)');
        }

        // Get the plans for customer assignment
        $basicPlan = ServicePlan::where('name', 'Basic 10Mbps')->first();
        $standardPlan = ServicePlan::where('name', 'Standard 25Mbps')->first();
        $premiumPlan = ServicePlan::where('name', 'Premium 50Mbps')->first();

        // Create test customers
        $customers = [
            [
                'account_number' => 'CUST-001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1234567001',
                'address' => '123 Main Street',
                'status' => 'active',
                'router_id' => $router->id,
                'service_plan_id' => $basicPlan->id,
                'ip_address' => '10.0.0.10',
                'mac_address' => '00:11:22:33:44:01',
            ],
            [
                'account_number' => 'CUST-002',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+1234567002',
                'address' => '456 Oak Avenue',
                'status' => 'active',
                'router_id' => $router->id,
                'service_plan_id' => $standardPlan->id,
                'ip_address' => '10.0.0.11',
                'mac_address' => '00:11:22:33:44:02',
            ],
            [
                'account_number' => 'CUST-003',
                'first_name' => 'Bob',
                'last_name' => 'Johnson',
                'email' => 'bob.johnson@example.com',
                'phone' => '+1234567003',
                'address' => '789 Pine Road',
                'status' => 'active',
                'router_id' => $router->id,
                'service_plan_id' => $premiumPlan->id,
                'ip_address' => '10.0.0.12',
                'mac_address' => '00:11:22:33:44:03',
            ],
        ];

        $createdCustomers = [];
        foreach ($customers as $customerData) {
            $customer = Customer::firstOrCreate(
                ['account_number' => $customerData['account_number']],
                $customerData
            );
            $createdCustomers[] = $customer;
            $this->command->info('✅ Customer created: ' . $customer->first_name . ' ' . $customer->last_name . ' (' . $customer->account_number . ')');
        }

        // Generate invoices for customers
        $billingPeriodStart = Carbon::now()->startOfMonth();
        $billingPeriodEnd = Carbon::now()->endOfMonth();

        // Customer 1: Draft invoice
        $invoice1 = $this->invoiceService->generateInvoice(
            $createdCustomers[0],
            $billingPeriodStart,
            $billingPeriodEnd
        );
        $this->command->info('✅ Invoice generated (Draft): ' . $invoice1->invoice_number . ' for ' . $createdCustomers[0]->first_name . ' ($' . $invoice1->total . ')');

        // Customer 2: Sent invoice with additional items
        $invoice2 = $this->invoiceService->generateInvoice(
            $createdCustomers[1],
            $billingPeriodStart,
            $billingPeriodEnd,
            [
                [
                    'description' => 'Router Installation Fee',
                    'quantity' => 1,
                    'unit_price' => 50.00,
                ],
            ]
        );
        $this->invoiceService->markAsSent($invoice2);
        $this->command->info('✅ Invoice generated (Sent): ' . $invoice2->invoice_number . ' for ' . $createdCustomers[1]->first_name . ' ($' . $invoice2->total . ') with additional items');

        // Customer 3: Partial payment invoice
        $invoice3 = $this->invoiceService->generateInvoice(
            $createdCustomers[2],
            $billingPeriodStart,
            $billingPeriodEnd,
            [
                [
                    'description' => 'Solar Panel Installation',
                    'quantity' => 1,
                    'unit_price' => 200.00,
                ],
            ]
        );
        $this->invoiceService->markAsSent($invoice3);
        
        // Record a partial payment
        $this->invoiceService->recordPayment($invoice3, [
            'amount' => 150.00,
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'transaction_id' => 'TXN-' . now()->format('YmdHis'),
            'reference' => 'Partial payment',
            'notes' => 'First installment',
        ]);
        $this->command->info('✅ Invoice generated (Partial): ' . $invoice3->invoice_number . ' for ' . $createdCustomers[2]->first_name . ' ($' . $invoice3->fresh()->balance . ' remaining)');

        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Routers: ' . Router::count());
        $this->command->info('   - Service Plans: ' . ServicePlan::count());
        $this->command->info('   - Customers: ' . Customer::count());
        $this->command->info('   - Invoices: ' . Invoice::count());
        $this->command->info('');
        $this->command->info('✨ Test data seeded successfully!');
    }
}

