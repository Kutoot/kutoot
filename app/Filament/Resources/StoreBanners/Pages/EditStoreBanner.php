<?php

namespace App\Filament\Resources\StoreBanners\Pages;

use App\Filament\Resources\StoreBannerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStoreBanner extends EditRecord
{
    protected static string $resource = StoreBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
