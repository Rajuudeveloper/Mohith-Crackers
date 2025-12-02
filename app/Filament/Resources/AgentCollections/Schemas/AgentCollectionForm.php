<?php

namespace App\Filament\Resources\AgentCollections\Schemas;

use App\Models\Agent;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;

class AgentCollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ✅ AGENT DROPDOWN (FROM AGENTS TABLE)
                Select::make('agent_id')
                    ->label('Agent')
                    ->options(Agent::orderBy('name')->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->required(),

                // ✅ PAYMENT MODE (ID BASED)
                Select::make('payment_mode')
                    ->label('Payment Mode')
                    ->options(config('payment.modes'))
                    ->required(),

                // ✅ AMOUNT
                TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01),

                // ✅ DATE
                DatePicker::make('payment_date')
                    ->label('Date')
                    ->default(now())
                    ->required(),

                // ✅ NOTES
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),

            ]);
    }
}
