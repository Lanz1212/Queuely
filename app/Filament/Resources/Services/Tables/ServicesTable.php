<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('code_prefix')
                    ->searchable(),
                TextColumn::make('activity_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'bongkar' => 'info',
                        'retur'   => 'warning',
                        default   => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bongkar' => 'Bongkar',
                        'retur'   => 'Retur',
                        default   => 'Muat',
                    }),
                TextColumn::make('color')
                    ->searchable(),
                TextColumn::make('estimated_time')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
