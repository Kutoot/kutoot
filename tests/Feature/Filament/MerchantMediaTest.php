<?php

use App\Filament\Resources\Merchants\Pages\CreateMerchant;
use App\Filament\Resources\Merchants\Pages\EditMerchant;
use App\Models\Merchant;
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

it('can load the create merchant page', function () {
    Livewire::test(CreateMerchant::class)
        ->assertOk();
});

it('can load the edit merchant page', function () {
    $merchant = Merchant::factory()->create();

    Livewire::test(EditMerchant::class, ['record' => $merchant->getRouteKey()])
        ->assertOk();
});

it('registers media collections on merchant model', function () {
    $merchant = Merchant::factory()->create();

    $collections = collect($merchant->getRegisteredMediaCollections());

    expect($collections->pluck('name')->toArray())->toContain('logo', 'media');
});

it('registers media conversions on merchant model', function () {
    $merchant = Merchant::factory()->create();

    $image = UploadedFile::fake()->image('test.jpg', 800, 600);
    $media = $merchant->addMedia($image)->toMediaCollection('media');

    expect($media)->not->toBeNull();
    expect($media->hasGeneratedConversion('thumb'))->toBeTrue();
});

it('enforces single file for logo collection', function () {
    $merchant = Merchant::factory()->create();

    $logo1 = UploadedFile::fake()->image('logo1.jpg', 200, 200);
    $logo2 = UploadedFile::fake()->image('logo2.jpg', 200, 200);

    $merchant->addMedia($logo1)->toMediaCollection('logo');
    $merchant->addMedia($logo2)->toMediaCollection('logo');

    expect($merchant->getMedia('logo'))->toHaveCount(1);
});

it('allows multiple media files in merchant media collection', function () {
    $merchant = Merchant::factory()->create();

    $image1 = UploadedFile::fake()->image('img1.jpg', 400, 400);
    $image2 = UploadedFile::fake()->image('img2.png', 600, 600);

    $merchant->addMedia($image1)->toMediaCollection('media');
    $merchant->addMedia($image2)->toMediaCollection('media');

    expect($merchant->getMedia('media'))->toHaveCount(2);
});
