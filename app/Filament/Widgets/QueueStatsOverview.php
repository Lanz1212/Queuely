<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Queue;
use Carbon\Carbon;

/**
 * Widget Filament: QueueStatsOverview
 * Menampilkan kartu ringkasan statistik antrean hari ini di dashboard.
 */
class QueueStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    /**
     * Mengumpulkan data statistik (Total, Selesai, Menunggu) untuk hari ini.
     * 
     * @return array Daftar objek Stat untuk ditampilkan.
     */
    protected function getStats(): array
    {
        // Menggunakan scope today() dari model Queue
        $totalToday     = Queue::today()->count();
        $completedToday = Queue::today()->where('status', 'completed')->count();
        $waitingToday   = Queue::today()->where('status', 'waiting')->count();

        return [
            Stat::make('Total Antrian Hari Ini', $totalToday)
                ->description('Semua layanan')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Antrian Selesai', $completedToday)
                ->description('Truk yang telah selesai proses')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Menunggu Panggilan', $waitingToday)
                ->description('Antrian yang belum dipanggil')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
