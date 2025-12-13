<?php

namespace App\Filament\Resources\Estimates\Pages;

use App\Filament\Resources\Estimates\EstimateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

use Filament\Actions;
use App\Models\Estimate;

class ListEstimates extends ListRecords
{
    protected static string $resource = EstimateResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         CreateAction::make(),
    //     ];
    // }
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label('Add Estimate')
                ->url(route('estimates.custom.create')),
        ];
    }
    // protected function extraFooter(): ?\Illuminate\Contracts\View\View
    // {
    //     $pageTotal = $this->getTableRecords()->sum('grand_total');
    //     $overallTotal = Estimate::sum('grand_total');

    //     return view('filament.estimates.table-footer', [
    //         'pageTotal' => $pageTotal,
    //         'overallTotal' => $overallTotal,
    //     ]);
    // }
}
