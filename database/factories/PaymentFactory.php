<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'invoice_id' => Invoice::factory(),
            'amount' => $this->faker->randomFloat(2, 100, 1000),
            'payment_method' => 'stripe',
            'status' => 'completed',
            'payment_date' => now(),
        ];
    }
}
