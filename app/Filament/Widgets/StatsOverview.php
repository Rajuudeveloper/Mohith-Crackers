<?php

namespace App\Filament\Widgets;


use App\Models\Agent;
use App\Models\Estimate;
use App\Models\AgentCollection;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Customer;
use App\Models\Product;


class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Agents', Agent::count()),



            Stat::make('Total Customers', Customer::count()),
            Stat::make('Total Products', Product::count()),
            Stat::make('Total Estimates', '₹ ' . number_format(
                Estimate::sum('grand_total'),
                2
            )),


            Stat::make('Total Collected', '₹ ' . number_format(
                AgentCollection::sum('amount'),
                2
            )),
        ];
    }
}
