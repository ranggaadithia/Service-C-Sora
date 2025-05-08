<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockCorrelationController;

Route::get('/stocks', function (Request $request) {
    $stocks = DB::table('stock_data')->distinct()->pluck('ticker');
    return response()->json($stocks);
});

Route::get('/stock/{ticker}', [StockCorrelationController::class, 'calculate']);
