<?php

namespace App\Filament\Resources\CampaignCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CampaignCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('icon'),
            ]);
    }
}
