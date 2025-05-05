<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function index()
    {
        return view('calculator');
    }

    public function calculate(Request $request)
    {
        $region = $request->input('region');
        $pumping = $request->input('pumping');
        $fuelType = $request->input('fuelType');
        $brand = $request->input('brand');
        $services = $request->input('services');
        $promo = $request->input('promo');

        // Calculate savings based on input
        $yearlySavings = $this->calculateYearlySavings($region, $pumping, $fuelType, $brand, $services, $promo);
        $monthlySavings = $yearlySavings / 12;

        return response()->json([
            'yearly_savings' => 'от ' . number_format($yearlySavings, 0, ',', ' ') . ' ₽',
            'monthly_savings' => 'от ' . number_format($monthlySavings, 0, ',', ' ') . ' ₽'
        ]);
    }

    public function submit(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'region' => 'required',
            'pumping' => 'required|numeric|min:0',
            'fuelType' => 'required|in:petrol,gas,diesel',
            'brand' => 'required',
            'services' => 'required',
            'promo' => 'required|numeric'
        ]);

        // Here you would typically save the data to a database
        // For now, we'll just return a success message
        return response()->json([
            'message' => 'Ваша заявка успешно отправлена!'
        ]);
    }

    private function calculateYearlySavings($region, $pumping, $fuelType, $brand, $services, $promo)
    {
        // Base calculations
        $basePrice = $this->getBasePrice($fuelType);
        $volumeMultiplier = $pumping / 100; // Normalize to 100 tons
        $regionMultiplier = $this->getRegionMultiplier($region);
        $brandMultiplier = $this->getBrandMultiplier($brand);
        $servicesMultiplier = $this->getServicesMultiplier($services);
        $promoMultiplier = (100 - $promo) / 100;

        // Calculate total savings
        $yearlySavings = $basePrice * $volumeMultiplier * $regionMultiplier * $brandMultiplier * $servicesMultiplier * $promoMultiplier * 12;

        return $yearlySavings;
    }

    private function getBasePrice($fuelType)
    {
        return match($fuelType) {
            'petrol' => 500200,
            'gas' => 200100,
            'diesel' => 320700,
            default => 0
        };
    }

    private function getRegionMultiplier($region)
    {
        return match($region) {
            '1' => 1.2,
            '2' => 1.1,
            '3' => 1.0,
            default => 1.0
        };
    }

    private function getBrandMultiplier($brand)
    {
        return match($brand) {
            'Shell' => 1.15,
            'Газпром' => 1.1,
            'Роснефть' => 1.05,
            'Татнефть' => 1.1,
            'Лукойл' => 1.15,
            'Башнефть' => 1.05,
            default => 1.0
        };
    }

    private function getServicesMultiplier($services)
    {
        if (empty($services)) return 1.0;
        
        $servicesArray = explode(',', $services);
        $multiplier = 1.0;
        
        foreach ($servicesArray as $service) {
            $multiplier += match($service) {
                'Штрафы' => 0.1,
                'Парковки' => 0.05,
                'ЭДО' => 0.08,
                'Мойки' => 0.07,
                'Отсрочка' => 0.12,
                'Телематика' => 0.15,
                'PPRPAY' => 0.09,
                'СМС' => 0.06,
                'Страховка' => 0.11,
                default => 0
            };
        }
        
        return $multiplier;
    }
} 