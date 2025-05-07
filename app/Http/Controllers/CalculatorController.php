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
            'inn' => 'required|digits_between:10,12',
            'phone' => 'required|regex:/^\+?[78][-\(]?\d{3}\)?-?\d{3}-?\d{2}-?\d{2}$/',
            'email' => 'required|email',
            'region' => 'required',
            'pumping' => 'required|numeric',
            'fuelType' => 'required|in:petrol,gas,diesel',
            'brand' => 'required',
            'services' => 'required',
            'promo' => 'required|numeric'
        ]);

        $fuelType = $request->input('fuelType');
        $pumping = (int)$request->input('pumping');
        $promoDiscount = (int)$request->input('promo');

        $tariff = $this->getTariff($fuelType, $pumping);
        $tariffDiscount = self::TARIFF_DISCOUNTS[$tariff];

        $baseCost = self::FUEL_PRICES[$fuelType] * $pumping;
        $tariffDiscountAmount = ($baseCost * $tariffDiscount) / 100;
        $promoDiscountAmount = ($baseCost * $promoDiscount) / 100;

        $monthlyCost = $baseCost - ($tariffDiscountAmount + $promoDiscountAmount);
        $yearlyCost = $monthlyCost * 12;

        $totalDiscount = $tariffDiscount + $promoDiscount;
        $monthlySavings = $tariffDiscountAmount + $promoDiscountAmount;
        $yearlySavings = $monthlySavings * 12;

        $emailContent = "Результаты расчета:\n\n";
        $emailContent .= "Регион: {$request->input('region')}\n";
        $emailContent .= "Прокачка: {$pumping} тонн\n";
        $emailContent .= "Тип топлива: " . $this->getFuelTypeName($fuelType) . "\n";
        $emailContent .= "Бренд: {$request->input('brand')}\n";
        $emailContent .= "Дополнительные услуги: {$request->input('services')}\n";
        $emailContent .= "Тариф: {$tariff}\n";
        $emailContent .= "Промо-акция: {$promoDiscount}%\n";
        $emailContent .= "Стоимость топлива в месяц: " . $this->formatNumber($monthlyCost) . "\n";
        $emailContent .= "Суммарная скидка: {$totalDiscount}%\n";
        $emailContent .= "Экономия в месяц: " . $this->formatNumber($monthlySavings) . "\n";
        $emailContent .= "Экономия в год: " . $this->formatNumber($yearlySavings) . "\n\n";
        $emailContent .= "Данные формы:\n";
        $emailContent .= "ИНН: {$request->input('inn')}\n";
        $emailContent .= "Телефон: {$request->input('phone')}\n";
        $emailContent .= "Email: {$request->input('email')}\n";

        Mail::raw($emailContent, function ($message) use ($request) {
            $message->to($request->input('email'))
                ->subject('Результаты расчета тарифа');
        });

        return response()->json(['message' => 'Ваша заявка успешно отправлена!']);
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
}
