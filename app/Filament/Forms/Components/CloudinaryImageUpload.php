<?php

namespace App\Filament\Forms\Components;

use App\Services\Cloudinary\CloudinaryUploader;
use Filament\Forms\Components\FileUpload;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;

/**
 * Single-image FileUpload that stores the file on Cloudinary (under the folder set
 * via ->directory(), e.g. "eduhub/students") and keeps only the returned secure URL
 * as the field's state. An empty or removed image dehydrates to null; replacing or
 * clearing an existing image is cleaned up on Cloudinary by the model's
 * ReplacesCloudinaryImageOnUpdate trait when the record is saved.
 */
class CloudinaryImageUpload extends FileUpload
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->image()
            ->nullable()
            ->maxSize(4096)
            ->imagePreviewHeight(150)
            ->fetchFileInformation(false)
            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, CloudinaryImageUpload $component): string {
                try {
                    return app(CloudinaryUploader::class)->upload($file, $component->getDirectory() ?? 'eduhub');
                } catch (RuntimeException $exception) {
                    report($exception);

                    throw ValidationException::withMessages([
                        $component->getStatePath() => 'The image could not be uploaded to Cloudinary. Please try again.',
                    ]);
                }
            })
            ->getUploadedFileUsing(fn (string $file): array => [
                'name' => basename(parse_url($file, PHP_URL_PATH) ?: $file),
                'size' => 0,
                'type' => 'image',
                'url' => $file,
            ]);
    }
}
