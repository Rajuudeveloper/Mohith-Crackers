<?php

namespace App\Filament\Resources\Estimates\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;

class EstimatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('estimate_no')
                    ->label('Estimate No')
                    ->sortable(),

                TextColumn::make('estimate_date')
                    ->label('Date')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('customer.agent.name')
                    ->label('Agent')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('inr')
                    ->sortable(),
            ])

            ->filters([
                Filter::make('estimate_date')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('to')->label('To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q) =>
                                $q->whereDate('estimate_date', '>=', $data['from'])
                            )
                            ->when($data['to'], fn ($q) =>
                                $q->whereDate('estimate_date', '<=', $data['to'])
                            );
                    }),
            ])

            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn ($record) => route('estimates.custom.edit', $record->id)),


                DeleteAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            ->defaultSort('id', 'desc');
    }
}
