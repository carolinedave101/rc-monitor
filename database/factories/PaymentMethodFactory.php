<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'label' => 'Bank transfer',
            'type' => 'bank_transfer',
            'details' => "Account name: ROYALTRICO LLC\nBank: Demo Bank\nAccount number: 000123456789",
            'enabled' => true,
            'sort' => 0,
        ];
    }
}
