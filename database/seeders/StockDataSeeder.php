<?php

namespace Database\Seeders;

use League\Csv\Reader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StockDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csv = Reader::createFromPath(public_path('stock_data/merge_stock_data.csv'), 'r');
        $csv->setHeaderOffset(0); // gunakan baris pertama sebagai header

        foreach ($csv as $record) {
            DB::table('stock_data')->insert([
                'ticker' => $record['ticker'],
                'date' => $record['date'],
                'open' => (float)$record['open'],
                'high' => (float)$record['high'],
                'low' => (float)$record['low'],
                'close' => (float)$record['close'],
                'volume' => (int)$record['volume'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
