<?php

namespace App\Filament\Resources\AgentReports;

use App\Filament\Resources\AgentReports\Pages\CreateAgentReport;
use App\Filament\Resources\AgentReports\Pages\EditAgentReport;
use App\Filament\Resources\AgentReports\Pages\ListAgentReports;
use App\Filament\Resources\AgentReports\Schemas\AgentReportForm;
use App\Filament\Resources\AgentReports\Tables\AgentReportsTable;
use App\Models\AgentReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AgentReportResource extends Resource
{
    protected static ?string $model = AgentReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Name';

    public static function form(Schema $schema): Schema
    {
        return AgentReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgentReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAgentReports::route('/'),
            'create' => CreateAgentReport::route('/create'),
            'edit' => EditAgentReport::route('/{record}/edit'),
        ];
    }
}
