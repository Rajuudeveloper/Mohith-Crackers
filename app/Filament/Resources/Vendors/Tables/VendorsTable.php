<?php

namespace App\Filament\Resources\Vendors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VendorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('mobile')
                    ->searchable(),

                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('gst_no')
                    ->label('GST No')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])

            // ✅ ROW ACTIONS → OPEN IN MODAL
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit Vendor')
                    ->modalSubmitActionLabel('Update'),

                DeleteAction::make(),
            ])

            // ✅ TOP BAR ACTION → CREATE MODAL
            ->headerActions([
                CreateAction::make()
                    ->modalHeading('Create Vendor')
                    ->modalSubmitActionLabel('Save'),
            ])

            // ✅ BULK DELETE
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
