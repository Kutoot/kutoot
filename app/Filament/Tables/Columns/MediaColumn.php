<?php

namespace App\Filament\Tables\Columns;

use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaColumn extends SpatieMediaLibraryImageColumn
{
    /**
     * Sentinel value returned when no images are available but at least one
     * video exists in the media collection.  The table column component
     * can detect this constant and display a placeholder graphic instead of
     * attempting to render an image.
     */
    public const VIDEO_PLACEHOLDER_SENTINEL = '__VIDEO_PLACEHOLDER__';

    /**
     * {@inheritdoc}
     */
    protected function getState(): mixed
    {
        // if the parent implementation already returns a value that makes
        // sense we could skip the custom logic, but we need access to the
        // underlying media so build it ourselves.
        $record = $this->getRecord();

        if (! $record instanceof Model) {
            return parent::getState();
        }

        $collectionName = $this->getName();

        /** @var Collection<int, Media> $medias */
        $medias = $record->getMedia($collectionName);

        // only keep image-type media
        $images = $medias->filter(function (Media $media) {
            return str_starts_with($media->mime_type, 'image/');
        });

        if ($images->isNotEmpty()) {
            return $images->map(function (Media $media) {
                return $media->getUrl();
            })->toArray();
        }

        // no images, but do we have videos?
        $hasVideo = $medias->contains(function (Media $media) {
            return str_starts_with($media->mime_type, 'video/');
        });

        if ($hasVideo) {
            // signal the caller that we only have video media
            return self::VIDEO_PLACEHOLDER_SENTINEL;
        }

        // fall back to the parent's state for whatever it would normally
        // provide (probably null or an empty array).
        return parent::getState();
    }

    /**
     * Return the URL that should be used when the column is asked to display
     * a placeholder for video-only records.  Consumers may override this
     * method when they subclass `MediaColumn`, or call it directly
     * when rendering custom view logic.
     */
    public function getVideoPlaceholderUrl(): string
    {
        // point at a generic asset in the public directory; adjust the path
        // as needed for your application.
        return asset('images/video-placeholder.png');
    }
}
