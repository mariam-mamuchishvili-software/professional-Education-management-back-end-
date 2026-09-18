<?php

namespace App\Models\Concerns;

use App\Services\Cloudinary\CloudinaryUploader;

/**
 * When the model's Cloudinary-backed image attribute changes (replaced or
 * cleared), deletes the previous Cloudinary asset before the new value is
 * saved. Applies uniformly to Filament edit forms and API updates, since both
 * ultimately go through Eloquent's `updating` event.
 */
trait ReplacesCloudinaryImageOnUpdate
{
    protected static function bootReplacesCloudinaryImageOnUpdate(): void
    {
        static::updating(function (self $model) {
            $attribute = $model->cloudinaryImageAttribute();

            if ($model->isDirty($attribute) && filled($model->getOriginal($attribute))) {
                app(CloudinaryUploader::class)->delete($model->getOriginal($attribute));
            }
        });
    }

    /**
     * The name of the column that holds this model's Cloudinary secure URL.
     */
    abstract protected function cloudinaryImageAttribute(): string;
}
