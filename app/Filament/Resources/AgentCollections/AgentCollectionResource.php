<?php

namespace App\Filament\Resources\AgentCollections;

use App\Filament\Resources\AgentCollections\Pages\CreateAgentCollection;
use App\Filament\Resources\AgentCollections\Pages\EditAgentCollection;
use App\Filament\Resources\AgentCollections\Pages\ListAgentCollections;
use App\Filament\Resources\AgentCollections\Schemas\AgentCollectionForm;
use App\Filament\Resources\AgentCollections\Tables\AgentCollectionsTable;
use App\Models\AgentCollection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AgentCollectionResource extends Resource
{
    protected static ?string $model = AgentCollection::class;
    protected static ?int $navigationSort = 6;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyRupee;

    protected static ?string $recordTitleAttribute = 'Agent Collections';

    public static function form(Schema $schema): Schema
    {
        return AgentCollectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgentCollectionsTable::configure($table);
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
            'index' => ListAgentCollections::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
