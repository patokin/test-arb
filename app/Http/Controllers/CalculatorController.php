<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CalculatorController extends Controller
{
    // Fuel prices per ton
    private const FUEL_PRICES = [
        'petrol' => 500200, // Бензин: 500,200 ₽/ton
        'gas' => 200100,    // Газ: 200,100 ₽/ton
        'diesel' => 320700  // ДТ: 320,700 ₽/ton
    ];

    // Tariff discounts
    private const TARIFF_DISCOUNTS = [
        'Эконом' => 3,
        'Избранный' => 5,
        'Премиум' => 7
    ];

    public function index()
    {
        return view('calculator');
    }

    public function calculate(Request $request)
    {
        $fuelType = $request->input('fuelType');
        $pumping = (int)$request->input('pumping');
        $promoDiscount = (int)$request->input('promo');

        // Get tariff based on fuel type and pumping volume
        $tariff = $this->getTariff($fuelType, $pumping);

        // Get tariff discount percentage
        $tariffDiscount = self::TARIFF_DISCOUNTS[$tariff];

        // Calculate base cost
        $baseCost = self::FUEL_PRICES[$fuelType] * $pumping;

        // Calculate discounts
        $tariffDiscountAmount = ($baseCost * $tariffDiscount) / 100;
        $promoDiscountAmount = ($baseCost * $promoDiscount) / 100;

        $monthlyCost = $baseCost - ($tariffDiscountAmount + $promoDiscountAmount);
        $yearlyCost = $monthlyCost * 12;

        return response()->json([
            'monthly_savings' => $this->formatNumber($monthlyCost),
            'yearly_savings' => $this->formatNumber($yearlyCost)
        ]);
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'region' => 'required|string',
            'pumping' => 'required|numeric',
            'fuelType' => 'required|string',
            'brand' => 'required|string',
            'services' => 'nullable|string',
            'promo' => 'required|numeric',
            'tariff' => 'required|string',
            'email' => 'required|email'
        ]);

        // Calculate costs and savings
        $monthlyCost = $this->calculateMonthlyCost(
            $validated['fuelType'],
            $validated['pumping'],
            $validated['brand']
        );

        $totalDiscount = $validated['promo'];
        $monthlySavings = $monthlyCost * ($totalDiscount / 100);
        $yearlySavings = $monthlySavings * 12;

        // Prepare email content
        $emailContent = "Результаты расчета тарифа:\n\n";
        $emailContent .= "Регион: {$validated['region']}\n";
        $emailContent .= "Объем заправки: {$validated['pumping']} тонн\n";
        $emailContent .= "Тип топлива: {$this->getFuelTypeName($validated['fuelType'])}\n";
        $emailContent .= "Бренд: {$validated['brand']}\n";
        $emailContent .= "Дополнительные услуги: " . ($validated['services'] ?: 'не выбраны') . "\n";
        $emailContent .= "Тариф: {$validated['tariff']}\n";
        $emailContent .= "Промо-акция: {$validated['promo']}%\n";
        $emailContent .= "Ежемесячная стоимость топлива: " . $this->formatNumber($monthlyCost) . "\n";
        $emailContent .= "Общая скидка: {$totalDiscount}%\n";
        $emailContent .= "Ежемесячная экономия: " . $this->formatNumber($monthlySavings) . "\n";
        $emailContent .= "Годовая экономия: " . $this->formatNumber($yearlySavings) . "\n\n";
        $emailContent .= "Данные формы:\n";
        $emailContent .= json_encode($validated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Send email
        Mail::raw($emailContent, function($message) use ($validated) {
            $message->to($validated['email'])
                   ->subject('Результаты расчета тарифа');
        });

        return response()->json(['success' => true]);
    }

    private function getTariff(string $fuelType, int $pumping): string
    {
        switch ($fuelType) {
            case 'petrol':
                if ($pumping < 100) return 'Эконом';
                if ($pumping < 300) return 'Избранный';
                return 'Премиум';

            case 'gas':
                if ($pumping < 200) return 'Эконом';
                if ($pumping < 700) return 'Избранный';
                return 'Премиум';

            case 'diesel':
                if ($pumping < 150) return 'Эконом';
                if ($pumping < 350) return 'Избранный';
                return 'Премиум';

            default:
                return 'Эконом';
        }
    }

    private function getFuelTypeName(string $fuelType): string
    {
        return match($fuelType) {
            'petrol' => 'Бензин',
            'gas' => 'Газ',
            'diesel' => 'ДТ',
            default => $fuelType
        };
    }

    private function formatNumber(float $number): string
    {
        return number_format($number, 0, '.', ' ') . ' ₽';
    }

    private function calculateMonthlyCost(string $fuelType, int $pumping, string $brand): float
    {
        // Calculate base cost
        $baseCost = self::FUEL_PRICES[$fuelType] * $pumping;

        // Get tariff based on fuel type and pumping volume
        $tariff = $this->getTariff($fuelType, $pumping);

        // Get tariff discount percentage
        $tariffDiscount = self::TARIFF_DISCOUNTS[$tariff];

        // Calculate tariff discount amount
        $tariffDiscountAmount = ($baseCost * $tariffDiscount) / 100;

        // Return monthly cost after tariff discount
        return $baseCost - $tariffDiscountAmount;
    }
}
