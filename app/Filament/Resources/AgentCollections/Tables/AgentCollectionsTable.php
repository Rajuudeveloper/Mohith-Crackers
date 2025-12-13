<?php

namespace App\Filament\Resources\AgentCollections\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\CreateAction;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\DateColumn;

class AgentCollectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // ✅ AGENT NAME FROM MASTER
                TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable(),

                // ✅ MODE LABEL FROM CONFIG
                TextColumn::make('payment_mode')
                    ->label('Mode')
                    ->formatStateUsing(fn($state) => config('payment.modes')[$state] ?? '-')
                    ->badge(),

                TextColumn::make('amount')->money('INR'),
                TextColumn::make('payment_date')
                    ->label('Date')
                    ->date('Y-m-d')  // or 'd M Y' for nicer format
                    ->sortable(),

                TextColumn::make('notes')->limit(30),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit Agent Collection')
                    ->modalSubmitActionLabel('Update'),

                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading('Create Agent Collection')
                    ->modalSubmitActionLabel('Save'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
