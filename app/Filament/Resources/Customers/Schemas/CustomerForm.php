<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use App\Models\Agent;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('agent_id')
                    ->label('Agent')
                    // ->relationship('agent', 'name')
                    ->options(Agent::orderBy('id', 'desc')->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('mobile')
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                TextInput::make('gst_no')
                    ->label('GST No')
                    ->default(null),
                Textarea::make('address')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
