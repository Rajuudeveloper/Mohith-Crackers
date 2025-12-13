<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('uom_name')
                    ->searchable(),

                TextColumn::make('packs_per_case')
                    ->label('Packs')
                    ->searchable(),

                TextColumn::make('price')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('hsn_code')
                    ->label('HSN Code')
                    ->searchable(),

                TextColumn::make('opening_stock')
                    ->numeric()
                    ->sortable(),

                ImageColumn::make('image'),

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
                    ->modalHeading('Edit Product')
                    ->modalSubmitActionLabel('Update'),

                DeleteAction::make(),
            ])

            // ✅ CREATE IN MODAL (TOP BUTTON)
            ->headerActions([
                CreateAction::make()
                    ->modalHeading('Create Product')
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
