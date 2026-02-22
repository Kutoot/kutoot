<?php

use App\Filament\Resources\Campaigns\Pages\EditCampaign;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    $this->actingAs($user);
    $this->user = $user;
});

it('can load the edit campaign page', function () {
    $campaign = Campaign::factory()->create(['creator_id' => $this->user->id]);

    Livewire::test(EditCampaign::class, ['record' => $campaign->getRouteKey()])
        ->assertOk();
});

it('registers media collection on campaign model', function () {
    $campaign = Campaign::factory()->create();

    $collections = collect($campaign->getRegisteredMediaCollections());

    expect($collections->pluck('name')->toArray())->toContain('media');
});

it('registers media conversions on campaign model', function () {
    $campaign = Campaign::factory()->create();

    $image = UploadedFile::fake()->image('test.jpg', 800, 600);
    $media = $campaign->addMedia($image)->toMediaCollection('media');

    expect($media)->not->toBeNull();
    expect($media->hasGeneratedConversion('thumb'))->toBeTrue();
});

it('allows multiple media files in campaign media collection', function () {
    $campaign = Campaign::factory()->create();

    $image1 = UploadedFile::fake()->image('img1.jpg', 400, 400);
    $image2 = UploadedFile::fake()->image('img2.png', 600, 600);

    $campaign->addMedia($image1)->toMediaCollection('media');
    $campaign->addMedia($image2)->toMediaCollection('media');

    expect($campaign->getMedia('media'))->toHaveCount(2);
});
