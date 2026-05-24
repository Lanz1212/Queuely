<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Queue;
use Carbon\Carbon;

class QueueStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalToday     = Queue::today()->count();
        $completedToday = Queue::today()->where('status', 'completed')->count();
        $waitingToday   = Queue::today()->where('status', 'waiting')->count();

        return [
            Stat::make('Total Antrian Hari Ini', $totalToday)
                ->description('Semua layanan')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Antrian Selesai', $completedToday)
                ->description('Pasien yang telah dilayani')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Menunggu Panggilan', $waitingToday)
                ->description('Antrian yang belum dipanggil')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
