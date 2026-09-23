<?php

namespace App\Models\Concerns;

use App\Services\Cloudinary\CloudinaryUploader;

/**
 * When any of the model's Cloudinary-backed image attributes changes (replaced
 * or cleared), deletes the previous Cloudinary asset before the new value is
 * saved. Applies uniformly to Filament edit forms and API updates, since both
 * ultimately go through Eloquent's `updating` event.
 */
trait ReplacesCloudinaryImageOnUpdate
{
    protected static function bootReplacesCloudinaryImageOnUpdate(): void
    {
        static::updating(function (self $model) {
            foreach ($model->cloudinaryImageAttributes() as $attribute) {
                if ($model->isDirty($attribute) && filled($model->getOriginal($attribute))) {
                    app(CloudinaryUploader::class)->delete($model->getOriginal($attribute));
                }
            }
        });
    }

    /**
     * The names of the columns that hold this model's Cloudinary secure URLs.
     *
     * @return array<int, string>
     */
    abstract protected function cloudinaryImageAttributes(): array;
}
