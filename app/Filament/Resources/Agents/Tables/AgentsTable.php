<?php

namespace App\Filament\Resources\Agents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AgentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),

                TextColumn::make('mobile')
                    ->searchable(),
                TextColumn::make('gst_no')
                    ->label('GST No')
                    ->searchable(),

                TextColumn::make('opening_balance')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('cr_dr')
                    ->badge(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            // ✅ EDIT & DELETE IN MODAL
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit Agent')
                    ->modalSubmitActionLabel('Update'),

                DeleteAction::make(),
            ])

            // ✅ CREATE IN MODAL (TOP BUTTON)
            ->headerActions([
                CreateAction::make()
                    ->modalHeading('Create Agent')
                    ->modalSubmitActionLabel('Save'),
            ])

            // ✅ BULK DELETE
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            // ✅ DEFAULT SORT
            ->defaultSort('id', 'desc');
    }
}
