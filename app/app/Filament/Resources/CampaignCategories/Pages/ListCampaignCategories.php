<?php

namespace App\Filament\Resources\CampaignCategories\Pages;

use App\Filament\Resources\CampaignCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCampaignCategories extends ListRecords
{
    protected static string $resource = CampaignCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
