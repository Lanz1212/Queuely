<?php

namespace App\Filament\Resources\Queues\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class QueueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('queue_number')
                    ->required(),
                TextInput::make('service_id')
                    ->required()
                    ->numeric(),
                TextInput::make('gate_id')
                    ->numeric()
                    ->default(null),
                Select::make('status')
                    ->options([
            'waiting' => 'Waiting',
            'called' => 'Called',
            'heading_to_gate' => 'Heading to gate',
            'loading' => 'Loading',
            'completed' => 'Completed',
        ])
                    ->default('waiting')
                    ->required(),
                TextInput::make('driver_name')
                    ->default(null),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                TextInput::make('qr_code_hash')
                    ->default(null),
                DateTimePicker::make('registered_at'),
                DateTimePicker::make('called_at'),
                DateTimePicker::make('completed_at'),
            ]);
    }
}
