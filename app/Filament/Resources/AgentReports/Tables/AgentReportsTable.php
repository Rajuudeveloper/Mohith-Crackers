<?php

namespace App\Filament\Resources\AgentReports\Tables;

use App\Models\Agent;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AgentReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(self::getQuery())
            ->columns([
                TextColumn::make('name')
                    ->label('Agent Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_estimate')
                    ->label('Total Estimate')
                    ->sortable()
                    ->formatStateUsing(fn($state) => number_format($state, 2)),

                TextColumn::make('opening_balance_display')
                    ->label('Opening Balance'),

                TextColumn::make('total_received')
                    ->label('Total Amount Received')
                    ->sortable()
                    ->formatStateUsing(fn($state) => number_format($state, 2)),


                TextColumn::make('balance_amount')
                    ->label('Balance Amount')
                    ->sortable()
                    ->formatStateUsing(fn($state) => ($state >= 0 ? '' : '-') . number_format(abs($state), 2)),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    protected static function getQuery(): Builder
    {
        return Agent::query()
            ->leftJoin('customers', 'customers.agent_id', '=', 'agents.id')
            ->leftJoin('estimates', 'estimates.customer_id', '=', 'customers.id')
            ->leftJoin('agent_collections', 'agent_collections.agent_id', '=', 'agents.id')
            ->select([
                'agents.id',
                'agents.name',
                'agents.opening_balance',
                'agents.cr_dr',

                // Total Estimate
                DB::raw('COALESCE(SUM(DISTINCT estimates.grand_total), 0) AS total_estimate'),

                // Total Received
                DB::raw('COALESCE(SUM(DISTINCT agent_collections.amount), 0) AS total_received'),

                // Opening Balance display (UI only)
                DB::raw("
                CASE
                    WHEN agents.cr_dr = 'Dr'
                        THEN CONCAT('+', agents.opening_balance)
                    ELSE
                        CONCAT('-', agents.opening_balance)
                END AS opening_balance_display
            "),

                // FINAL Balance Calculation
                DB::raw("
            (
                (
                    COALESCE(SUM(DISTINCT estimates.grand_total), 0)
                    + CASE
                        WHEN agents.cr_dr = 'Dr'
                            THEN agents.opening_balance
                        ELSE
                            -agents.opening_balance
                      END
                )
                - COALESCE(SUM(DISTINCT agent_collections.amount), 0)
            ) AS balance_amount
            "),
            ])
            ->groupBy(
                'agents.id',
                'agents.name',
                'agents.opening_balance',
                'agents.cr_dr'
            );
    }
}
