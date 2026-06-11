<?php

namespace App\Filament\Resources\Queues\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class QueuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('queue_number')
                    ->label('Nomor Antrian')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('service.name')
                    ->label('Layanan')
                    ->sortable(),
                TextColumn::make('gate.name')
                    ->label('Loket')
                    ->placeholder('-')
                    ->sortable(),
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
                TextColumn::make('driver_name')
                    ->label('Nama Driver')
                    ->searchable(),
                TextColumn::make('vehicle_plate')
                    ->label('No Polisi/Plat Kendaraan')
                    ->searchable(),
                TextColumn::make('company')
                    ->label('Ekspedisi')
                    ->searchable(),
                TextColumn::make('registered_at')
                    ->label('Tgl Daftar')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('called_at')
                    ->label('Dipanggil')
                    ->dateTime('H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('completed_at')
                    ->label('Selesai')
                    ->dateTime('H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('service_id')
                    ->label('Layanan')
                    ->relationship('service', 'name'),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'waiting' => 'Menunggu',
                        'called' => 'Dipanggil',
                        'heading_to_gate' => 'Menuju Loket',
                        'loading' => 'Loading',
                        'completed' => 'Selesai',
                        'skipped' => 'Dibatalkan',
                    ]),
                Filter::make('date')
                    ->form([
                        DatePicker::make('tanggal')
                            ->label('Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('queue_date', '=', $date),
                            );
                    }),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->recordActions([
                EditAction::make()
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isAdmin() ?? false),
                ]),
            ]);
    }
}
