<?php

namespace App\Filament\Resources\AgentCollections\Pages;

use App\Filament\Resources\AgentCollections\AgentCollectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAgentCollection extends CreateRecord
{
    protected static string $resource = AgentCollectionResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
