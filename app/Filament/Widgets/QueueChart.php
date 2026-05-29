<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Queue;
use Carbon\Carbon;

/**
 * Widget Filament: QueueChart
 * Menampilkan grafik garis tren jumlah antrean harian selama 7 hari terakhir.
 */
class QueueChart extends ChartWidget
{
    protected ?string $heading = 'Trend Antrian (7 Hari Terakhir)';
    protected static ?int $sort = 2;

    /**
     * Mengambil dan memformat data untuk ditampilkan di grafik.
     * 
     * @return array Konfigurasi dataset dan label untuk grafik.
     */
    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Menghitung mundur dari 6 hari yang lalu hingga hari ini (total 7 hari)
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Queue::whereDate('queue_date', $date)->count();
            
            $data[] = $count;
            $labels[] = $date->format('d M');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Antrian Terdaftar',
                    'data' => $data,
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Menentukan tipe grafik.
     */
    protected function getType(): string
    {
        return 'line';
    }
}
