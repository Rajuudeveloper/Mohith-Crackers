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
        // Subquery for total estimates per agent
        $estimatesSubquery = DB::table('customers')
            ->join('estimates', 'estimates.customer_id', '=', 'customers.id')
            ->select(
                'customers.agent_id',
                DB::raw('COALESCE(SUM(estimates.grand_total), 0) as total_estimate')
            )
            ->groupBy('customers.agent_id');

        // Subquery for total collections per agent
        $collectionsSubquery = DB::table('agent_collections')
            ->select(
                'agent_id',
                DB::raw('COALESCE(SUM(amount), 0) as total_received')
            )
            ->groupBy('agent_id');

        return Agent::query()
            ->leftJoinSub($estimatesSubquery, 'est', function ($join) {
                $join->on('agents.id', '=', 'est.agent_id');
            })
            ->leftJoinSub($collectionsSubquery, 'col', function ($join) {
                $join->on('agents.id', '=', 'col.agent_id');
            })
            ->select([
                'agents.id',
                'agents.name',
                'agents.opening_balance',
                'agents.cr_dr',

                // Total Estimate
                DB::raw('COALESCE(est.total_estimate, 0) AS total_estimate'),

                // Total Received
                DB::raw('COALESCE(col.total_received, 0) AS total_received'),

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
                    COALESCE(est.total_estimate, 0)
                    + CASE
                        WHEN agents.cr_dr = 'Dr'
                            THEN agents.opening_balance
                        ELSE
                            -agents.opening_balance
                      END
                    - COALESCE(col.total_received, 0)
                ) AS balance_amount
            "),
            ]);
    }
}
