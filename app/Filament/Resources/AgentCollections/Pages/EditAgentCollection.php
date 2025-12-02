<?php

namespace App\Filament\Resources\AgentCollections\Pages;

use App\Filament\Resources\AgentCollections\AgentCollectionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAgentCollection extends EditRecord
{
    protected static string $resource = AgentCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
