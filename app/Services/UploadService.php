<?php

namespace App\Services;

use App\Exceptions\InvalidFileException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServerException;
use App\Services\Api\CloudinaryService;
use Exception;
use finfo;

class UploadService
{
    private const ALLOWED_FILES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];
    private const MAX_SIZE = 10 * 1024 * 1024; // 10 MB
    private const MAX_WIDTH = 4000;
    private const MAX_HEIGHT = 4000;
    private const SIZES = [
        'thumb'  => 300,
        'medium' => 800,
        'large'  => 1920,
    ];

    public function __construct(private CloudinaryService $cloudinary) {}

    public function uploadImage(array $imageFile, int $restaurantId, ?string $publicId = null): array
    {
        $this->validateImage($imageFile);
        try {
            if ($publicId === null) {
                $result = $this->cloudinary->uploadRestaurantImage($imageFile['tmp_name'], $restaurantId);
            } else {
                $result = $this->cloudinary->changeRestaurantImage($imageFile['tmp_name'], $restaurantId, $publicId);
            }
        } catch (Exception $e) {
            throw new ServerException(__METHOD__ . ": Échec Cloudinary API: " . $e->getMessage());
        }
        return [
            "public_id" => $result["public_id"],
            "width" => $result["width"],
            "height" => $result["height"],
            "format" => $result["format"],
            "secure_url" => $result["secure_url"],
        ];
    }

    private function validateImage(array $file): void
    {
        # erreur fichier
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidFileException(message:__METHOD__ . ': Erreur upload.', UImessage:"Impossible de récupérer votre fichier. Veuillez réssayer.");
        }
        # poids fichier
        if ($file['size'] > self::MAX_SIZE) {
            throw new InvalidFileException(UImessage:'Image trop lourde: Veuillez fournir une image inférieure à 10MB.');
        }
        # mime type (media type)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!isset(self::ALLOWED_FILES[$mime])) {
            throw new InvalidFileException(UImessage:'Format invalide: Veuillez fournir une image .jpg, .png ou .webp');
        }
        # image infos/dimensions
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            throw new InvalidFileException(UImessage:'Image invalide.');
        }
        [$width, $height] = $imageInfo;
        if ($width > self::MAX_WIDTH || $height > self::MAX_HEIGHT) {
            throw new InvalidFileException(UImessage:'Image trop grande: Veuillez fournir une image de maximum 4000px.');
        }
    }

    public function deleteImage(string $publicId): bool
    {
        try {
            $result = $this->cloudinary->destroyRestaurantImage($publicId);
        } catch (Exception $e) {
            throw new ServerException(__METHOD__ . ": Échec Cloudinary API: " . $e->getMessage());
        }
        if ($result['result'] === "ok") {
            return true;
        }
        if ($result['result'] === "not found") {
            throw new NotFoundException(message:__METHOD__ . ": Cloudinary API: Image non trouvée.", UIMessage:"Échec de suppression, veuillez réessayer.");
        }
        return false;
    }
}