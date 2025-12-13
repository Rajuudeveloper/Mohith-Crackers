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
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Agent;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\ActionGroup;

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
                    ->date('d-m-Y')
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
                    ->sortable()
                    ->summarize(Sum::make()),
            ])

            ->filters([
                Filter::make('estimate_date')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('to')->label('To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn($q) =>
                                $q->whereDate('estimate_date', '>=', $data['from'])
                            )
                            ->when(
                                $data['to'],
                                fn($q) =>
                                $q->whereDate('estimate_date', '<=', $data['to'])
                            );
                    }),
                SelectFilter::make('agent')
                    ->label('Agent')
                    ->relationship('customer.agent', 'name'),
            ])

            ->actions([
                ActionGroup::make([
                    Action::make('edit')
                        ->label('Edit')
                        ->icon('heroicon-o-pencil-square')
                        ->url(fn($record) => route('estimates.custom.edit', $record->id)),

                    Action::make('view_pdf')
                        ->label('View PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn($record) => route('estimates.custom.pdf', [$record->id, 'view']))
                        ->openUrlInNewTab(),

                    Action::make('download_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn($record) => route('estimates.custom.pdf', [$record->id, 'download']))
                        ->openUrlInNewTab(),

                    DeleteAction::make(),
                ])
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            ->defaultSort('id', 'desc');
    }
}
