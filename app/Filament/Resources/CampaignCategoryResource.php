<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignCategories\Pages\CreateCampaignCategory;
use App\Filament\Resources\CampaignCategories\Pages\EditCampaignCategory;
use App\Filament\Resources\CampaignCategories\Pages\ListCampaignCategories;
use App\Filament\Resources\CampaignCategories\Schemas\CampaignCategoryForm;
use App\Filament\Resources\CampaignCategories\Tables\CampaignCategoriesTable;
use App\Models\CampaignCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CampaignCategoryResource extends Resource
{
    protected static ?string $model = CampaignCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Promotion Management';

    public static function form(Schema $schema): Schema
    {
        return CampaignCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CampaignCategoriesTable::configure($table);
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
            'index' => ListCampaignCategories::route('/'),
            'create' => CreateCampaignCategory::route('/create'),
            'edit' => EditCampaignCategory::route('/{record}/edit'),
        ];
    }
}
