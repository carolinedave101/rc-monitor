<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'label' => 'Bank transfer (USD)',
                'type' => 'bank_transfer',
                'sort' => 10,
                'details' => "Account name: ROYALTRICO LLC\nBank: Demo Bank\nAccount number: 000123456789\nRouting (ABA): 021000021\nSWIFT: DEMOUS33",
            ],
            [
                'label' => 'PayPal',
                'type' => 'paypal',
                'sort' => 20,
                'details' => "Send to: payments@royaltrico.example\nInclude the invoice number in the note.",
            ],
            [
                'label' => 'CashApp',
                'type' => 'cashapp',
                'sort' => 30,
                'details' => "Cashtag: \$ROYALTRICO\nInclude the invoice number in the note.",
            ],
            [
                'label' => 'Crypto (USDT TRC20)',
                'type' => 'crypto',
                'sort' => 40,
                'details' => "Network: TRON (TRC20)\nWallet: TDemoWalletAddress000000000000000\nSend the exact amount and keep the transaction hash for your proof.",
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['label' => $method['label']],
                $method + ['enabled' => true],
            );
        }
    }
}
