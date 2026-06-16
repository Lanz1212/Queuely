<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code_prefix')
                    ->required(),
                Select::make('activity_type')
                    ->required()
                    ->options([
                        'muat'    => 'Muat',
                        'bongkar' => 'Bongkar (Bahan Baku)',
                        'retur'   => 'Retur (Bongkar Produk)',
                    ])
                    ->default('muat'),
                TextInput::make('color')
                    ->default(null),
                TextInput::make('estimated_time')
                    ->required()
                    ->numeric()
                    ->default(15),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
