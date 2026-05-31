<?php

namespace App\Services\Api;

use Cloudinary\Api\ApiResponse;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'],
                'api_key' => $_ENV['CLOUDINARY_API_KEY'],
                'api_secret' => $_ENV['CLOUDINARY_API_SECRET'],
            ],
            'url' => [
                'secure' => true
            ]
        ]);
    }

    public function uploadRestaurantImage(string $tmpPath, int $restaurantId): ApiResponse {
        return $this->cloudinary->uploadApi()
            ->upload($tmpPath,
                [
                    'folder' => 'restaurant/' . $restaurantId,
                    'public_id' => bin2hex(random_bytes(16)),
                    'overwrite' => false,
                    'resource_type' => 'image',
                    'max_bytes' => 10 * 1024 * 1024, // 10 MB
                    'quality' => 'auto',
                    'fetch_format' => 'auto',
                    'allowed_formats' => ['jpg', 'png', 'webp'],
                    'invalidate' => true,
                ]
            );
    }

    public function changeRestaurantImage(string $tmpPath, int $restaurantId, string $publicId): ApiResponse {
        return $this->cloudinary->uploadApi()
            ->upload($tmpPath,
                [
                    'public_id' => $publicId,
                    'overwrite' => true,
                    'resource_type' => 'image',
                    'max_bytes' => 10 * 1024 * 1024, // 10 MB
                    'quality' => 'auto',
                    'fetch_format' => 'auto',
                    'allowed_formats' => ['jpg', 'png', 'webp'],
                    'invalidate' => true,
                ]
            );
    }

    public function destroyRestaurantImage(string $publicId): ApiResponse {
        return $this->cloudinary->uploadApi()
            ->destroy($publicId, 
                [
                    'invalidate' => true
                ]
            );
    }
}