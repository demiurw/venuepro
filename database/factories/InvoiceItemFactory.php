<?php

namespace Database\Factories;

use App\Models\InvoiceItem;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition()
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $unitPrice = $this->faker->randomFloat(2, 20, 200);
        return [
            'company_id' => Company::factory(),
            'invoice_id' => Invoice::factory(),
            'resource' => 'Room Booking',
            'description' => 'Booking for ' . $this->faker->company . ' event',
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            // 'total' would typically be calculated by a model event or mutator
        ];
    }
}
