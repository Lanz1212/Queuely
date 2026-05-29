<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Queue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

/**
 * Widget Filament: LatestQueues
 * Menampilkan tabel 10 antrean terbaru di panel dashboard admin/operator.
 */
class LatestQueues extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    /**
     * Mengkonfigurasi tabel untuk widget.
     * Mendefinisikan query data antrean terbaru dan kolom-kolom yang ditampilkan.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Queue::query()->latest('created_at')->limit(10)
            )
            ->columns([
                TextColumn::make('queue_number')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('service.name')
                    ->label('Layanan'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'waiting' => 'warning',
                        'called' => 'info',
                        'heading_to_gate' => 'info',
                        'loading' => 'primary',
                        'completed' => 'success',
                        'skipped' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('gate.name')
                    ->label('Loket')
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Waktu Daftar')
                    ->dateTime('H:i')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
