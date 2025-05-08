<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockCorrelationController extends Controller
{
    public function calculate($ticker)
    {
        $ticker = strtoupper($ticker);

        // Ambil 5 data terakhir dari ticker utama
        $mainStock = DB::table('stock_data')
            ->where('ticker', $ticker)
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        if ($mainStock->count() < 5) {
            return response()->json(['error' => 'Not enough data for main ticker'], 400);
        }

        $dates = $mainStock->pluck('date')->map(fn($d) => date('Y-m-d', strtotime($d)))->toArray();
        $mainPrices = $mainStock->pluck('close')->toArray();

        // Ambil semua saham lain yang punya data di tanggal-tanggal itu
        $otherStocks = DB::table('stock_data')
            ->whereIn('date', $dates)
            ->where('ticker', '!=', $ticker)
            ->orderBy('ticker')
            ->orderBy('date', 'desc')
            ->get();

        // Group by ticker
        $grouped = [];
        foreach ($otherStocks as $stock) {
            $dateStr = date('Y-m-d', strtotime($stock->date));
            if (!in_array($dateStr, $dates)) continue;

            if (!isset($grouped[$stock->ticker])) {
                $grouped[$stock->ticker] = [];
            }
            $grouped[$stock->ticker][] = $stock->close;
        }

        // Hitung korelasi untuk yang punya 5 data
        $result = [];
        foreach ($grouped as $otherTicker => $prices) {
            if (count($prices) === 5) {
                $corr = $this->calculatePearson($mainPrices, $prices);
                $result[] = [
                    'ticker' => $otherTicker,
                    'correlation' => round($corr, 4),
                ];
            }
        }

        // Urutkan dari yang paling korelasinya tinggi
        usort($result, fn($a, $b) => $b['correlation'] <=> $a['correlation']);

        return response()->json([
            'correlation_with' => $ticker,
            'result' => $result
        ]);
    }

    private function calculatePearson(array $x, array $y): float
    {
        $n = count($x);
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumX2 = array_sum(array_map(fn($xi) => $xi * $xi, $x));
        $sumY2 = array_sum(array_map(fn($yi) => $yi * $yi, $y));
        $sumXY = array_sum(array_map(fn($xi, $yi) => $xi * $yi, $x, $y));

        $numerator = $n * $sumXY - $sumX * $sumY;
        $denominator = sqrt(($n * $sumX2 - $sumX ** 2) * ($n * $sumY2 - $sumY ** 2));

        return $denominator == 0 ? 0 : $numerator / $denominator;
    }
}
