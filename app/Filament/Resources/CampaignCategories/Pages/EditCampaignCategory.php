<?php

namespace App\Filament\Resources\CampaignCategories\Pages;

use App\Filament\Resources\CampaignCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCampaignCategory extends EditRecord
{
    protected static string $resource = CampaignCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
