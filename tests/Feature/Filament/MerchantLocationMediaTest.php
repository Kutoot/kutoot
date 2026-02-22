<?php

use App\Filament\Resources\MerchantLocations\Pages\EditMerchantLocation;
use App\Models\MerchantLocation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    $this->actingAs($user);
});

it('can load the edit merchant location page', function () {
    $location = MerchantLocation::factory()->create();

    Livewire::test(EditMerchantLocation::class, ['record' => $location->getRouteKey()])
        ->assertOk();
});

it('registers media collection on merchant location model', function () {
    $location = MerchantLocation::factory()->create();

    $collections = collect($location->getRegisteredMediaCollections());

    expect($collections->pluck('name')->toArray())->toContain('media');
});

it('registers media conversions on merchant location model', function () {
    $location = MerchantLocation::factory()->create();

    $image = UploadedFile::fake()->image('test.jpg', 800, 600);
    $media = $location->addMedia($image)->toMediaCollection('media');

    expect($media)->not->toBeNull();
    expect($media->hasGeneratedConversion('thumb'))->toBeTrue();
});

it('allows multiple media files in location media collection', function () {
    $location = MerchantLocation::factory()->create();

    $image1 = UploadedFile::fake()->image('img1.jpg', 400, 400);
    $image2 = UploadedFile::fake()->image('img2.png', 600, 600);

    $location->addMedia($image1)->toMediaCollection('media');
    $location->addMedia($image2)->toMediaCollection('media');

    expect($location->getMedia('media'))->toHaveCount(2);
});
