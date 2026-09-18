<?php

namespace App\Services\Cloudinary;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Talks to Cloudinary's signed Upload API directly over Laravel's HTTP
 * client. No Cloudinary SDK is used: the official cloudinary/cloudinary_php
 * package pins guzzlehttp/guzzle to ^7.4.5, which conflicts with the
 * guzzlehttp/guzzle 8.x this project already depends on, and
 * cloudinary-labs/cloudinary-laravel doesn't support Laravel 13 yet.
 */
class CloudinaryUploader
{
    private readonly ?string $cloudName;

    private readonly ?string $apiKey;

    private readonly ?string $apiSecret;

    public function __construct()
    {
        $this->cloudName = config('cloudinary.cloud_name');
        $this->apiKey = config('cloudinary.api_key');
        $this->apiSecret = config('cloudinary.api_secret');
    }

    /**
     * Upload an image to Cloudinary under the given folder and return its secure URL.
     */
    public function upload(UploadedFile $file, string $folder): string
    {
        $this->ensureConfigured();

        $params = [
            'folder' => $folder,
            'timestamp' => now()->timestamp,
        ];

        $response = Http::attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post($this->endpoint('image/upload'), [
                ...$params,
                'api_key' => $this->apiKey,
                'signature' => $this->sign($params),
            ]);

        if ($response->failed()) {
            throw new RuntimeException("Cloudinary upload failed: {$response->body()}");
        }

        $secureUrl = $response->json('secure_url');

        if (! is_string($secureUrl) || $secureUrl === '') {
            throw new RuntimeException('Cloudinary upload response did not include a secure_url.');
        }

        return $secureUrl;
    }

    /**
     * Safely delete a previously uploaded image from Cloudinary, but only if the
     * given URL is demonstrably one we uploaded ourselves (matching host, cloud
     * name, and our own folder namespace). Never guesses at arbitrary/foreign
     * URLs, and never throws — failures are logged and otherwise ignored so a
     * Cloudinary hiccup never blocks a save.
     */
    public function delete(?string $secureUrl): void
    {
        if (blank($secureUrl)) {
            return;
        }

        $publicId = $this->extractOwnedPublicId($secureUrl);

        if ($publicId === null) {
            return;
        }

        try {
            $this->ensureConfigured();

            $params = [
                'public_id' => $publicId,
                'timestamp' => now()->timestamp,
            ];

            $response = Http::asForm()->post($this->endpoint('image/destroy'), [
                ...$params,
                'api_key' => $this->apiKey,
                'signature' => $this->sign($params),
            ]);

            if ($response->failed()) {
                Log::warning('Cloudinary destroy request failed.', [
                    'url' => $secureUrl,
                    'public_id' => $publicId,
                    'response' => $response->body(),
                ]);
            }
        } catch (Throwable $exception) {
            Log::warning('Cloudinary destroy request threw an exception.', [
                'url' => $secureUrl,
                'public_id' => $publicId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Derive the Cloudinary public_id from a secure URL, but only when the URL's
     * host, cloud name, and folder namespace all match this app's own uploads.
     * Returns null for anything else, so delete() never touches a foreign URL.
     */
    private function extractOwnedPublicId(string $url): ?string
    {
        $parsed = parse_url($url);

        if (! $parsed || ($parsed['host'] ?? null) !== 'res.cloudinary.com') {
            return null;
        }

        if (blank($this->cloudName) || ! str_contains($parsed['path'] ?? '', "/{$this->cloudName}/")) {
            return null;
        }

        if (! preg_match('#/upload/(?:v\d+/)?(?<public_id>eduhub/[^.]+)\.[a-zA-Z0-9]+$#', $url, $matches)) {
            return null;
        }

        return $matches['public_id'];
    }

    /**
     * Cloudinary's signing algorithm: sort params by key, join as "key=value"
     * pairs with "&", append the raw API secret, and hash the whole thing.
     */
    private function sign(array $params): string
    {
        ksort($params);

        $pairs = [];
        foreach ($params as $key => $value) {
            $pairs[] = "{$key}={$value}";
        }

        return sha1(implode('&', $pairs).$this->apiSecret);
    }

    private function endpoint(string $action): string
    {
        return "https://api.cloudinary.com/v1_1/{$this->cloudName}/{$action}";
    }

    private function ensureConfigured(): void
    {
        if (blank($this->cloudName) || blank($this->apiKey) || blank($this->apiSecret)) {
            throw new RuntimeException(
                'Cloudinary credentials are not configured. Set CLOUDINARY_CLOUD_NAME, '.
                'CLOUDINARY_API_KEY, and CLOUDINARY_API_SECRET in your .env file.'
            );
        }
    }
}
