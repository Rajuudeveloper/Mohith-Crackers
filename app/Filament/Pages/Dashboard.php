<?php


namespace App\Filament\Pages;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Pages\Dashboard as BaseDashboard;


class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static bool $shouldRegisterNavigation = false;


    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverview::class,
        ];
    }
}
