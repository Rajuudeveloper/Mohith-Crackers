<?php

namespace App\Filament\Resources\AgentReports\Pages;

use App\Filament\Resources\AgentReports\AgentReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAgentReports extends ListRecords
{
    protected static string $resource = AgentReportResource::class;
}
