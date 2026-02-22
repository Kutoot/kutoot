<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MigrateMerchantLogosToMediaLibrary extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:migrate-merchant-logos';

    /**
     * @var string
     */
    protected $description = 'Migrate existing merchant logo files from the logo column into a Spatie Media Library collection.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $merchants = DB::table('merchants')
            ->whereNotNull('logo')
            ->where('logo', '!=', '')
            ->get(['id', 'name', 'logo']);

        if ($merchants->isEmpty()) {
            $this->info('No merchant logos to migrate.');

            return self::SUCCESS;
        }

        $this->info("Found {$merchants->count()} merchant(s) with logos to migrate.");

        $migrated = 0;
        $skipped = 0;

        foreach ($merchants as $row) {
            $merchant = Merchant::find($row->id);

            if (! $merchant) {
                $this->warn("Merchant #{$row->id} not found, skipping.");
                $skipped++;

                continue;
            }

            // Skip if the merchant already has a logo in the media collection
            if ($merchant->hasMedia('logo')) {
                $this->line("Merchant \"{$row->name}\" already has a media logo, skipping.");
                $skipped++;

                continue;
            }

            $logoPath = $row->logo;

            // Check if the file exists on the public disk
            if (Storage::disk('public')->exists($logoPath)) {
                $merchant->addMediaFromDisk($logoPath, 'public')
                    ->toMediaCollection('logo');

                $this->line("Migrated logo for \"{$row->name}\".");
                $migrated++;
            } else {
                $this->warn("Logo file not found for \"{$row->name}\": {$logoPath}");
                $skipped++;
            }
        }

        $this->info("Migration complete: {$migrated} migrated, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
