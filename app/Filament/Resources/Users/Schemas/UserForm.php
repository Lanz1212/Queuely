<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(fn ($record) => $record === null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->helperText(fn ($record) => $record ? 'Kosongkan jika tidak ingin mengubah password.' : null),
                Select::make('role')
                    ->options(['admin' => 'Admin', 'operator' => 'Operator'])
                    ->default('operator')
                    ->required(),
            ]);
    }
}
