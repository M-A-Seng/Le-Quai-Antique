<?php

namespace App\Services\Api;

use App\Exceptions\ServerException;
use Cloudinary\Api\ApiResponse;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

use function App\Helpers\get_valid_env;

/**
 * CloudinaryService
 * 
 * - uploadRestaurantImage()
 * - changeRestaurantImage()
 * - destroyRestaurantImage()
 */
class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => get_valid_env('CLOUDINARY_CLOUD_NAME') ?? throw new ServerException(__METHOD__ . ": Cloudname cloudinary manquant."),
                'api_key' => get_valid_env('CLOUDINARY_API_KEY') ?? throw new ServerException(__METHOD__ . ": Clé API cloudinary manquant."),
                'api_secret' => get_valid_env('CLOUDINARY_API_SECRET') ?? throw new ServerException(__METHOD__ . ": Secret API cloudinary manquant.")
            ],
            'url' => [
                'secure' => true
            ]
        ]);
    }
    
    /**
     * uploadRestaurantImage enregistrer image
     *
     * @param  string $tmpPath
     * @param  int $restaurantId
     * @return ApiResponse
     */
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
    
    /**
     * changeRestaurantImage remplacer image
     *
     * @param  string $tmpPath
     * @param  int $restaurantId
     * @param  string $publicId
     * @return ApiResponse
     */
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
    
    /**
     * destroyRestaurantImage supprimer image
     *
     * @param  string $publicId
     * @return ApiResponse
     */
    public function destroyRestaurantImage(string $publicId): ApiResponse {
        return $this->cloudinary->uploadApi()
            ->destroy($publicId, 
                [
                    'invalidate' => true
                ]
            );
    }
}