<?php

namespace App\Filament\Resources\AgentReports\Pages;

use App\Filament\Resources\AgentReports\AgentReportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAgentReport extends EditRecord
{
    protected static string $resource = AgentReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
