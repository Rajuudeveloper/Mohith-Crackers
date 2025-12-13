<?php

namespace App\Filament\Resources\Agents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AgentForm
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
                    ->default(null),
                TextInput::make('mobile')
                    ->tel()
                    ->default(null),
                TextInput::make('gst_no')
                    ->label('GST No')
                    ->default(null),
                TextInput::make('opening_balance')
                    ->numeric()
                    ->default(0),
                Select::make('cr_dr')
                    ->options(['Cr' => 'Cr', 'Dr' => 'Dr'])
                    ->default('Dr')
                    ->required(),
                Textarea::make('address')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
