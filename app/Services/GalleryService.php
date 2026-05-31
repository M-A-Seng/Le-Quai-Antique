<?php

namespace App\Services;

use App\Core\Abstract\AbstractService;
use App\Enums\Role;
use App\Exceptions\DataProcessingException;
use App\Exceptions\InvalidFieldException;
use App\Models\GalleryModel;
use Throwable;

class GalleryService extends AbstractService
{
    protected const NOT_NULL_COLUMNS = [
        "restaurant_id",
        "title",
        "slug",
        "public_id",
        "secure_url",
        "width",
        "height"
    ];
    private const AUTHORIZED_KEYS = [
        "id",
        "restaurant_id",
        "title",
        "slug",
        "position",
        "public_id",
        "secure_url",
        "width",
        "height"
    ];

    public function __construct(private GalleryModel $galleryModel, private UploadService $uploadService) {}

    public function newImage(int $restaurantId, array $data, array $file): array
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        if (empty($data) || array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . ": Tableau associatif attendu en deuxième paramètre.");
        }
        if (empty($file) || !is_uploaded_file($file['tmp_name'])) {
            throw new DataProcessingException(__METHOD__ . ": Fichier attendu en troisième paramètre.");
        }
        $this->validatePositiveInteger($restaurantId);
        $this->checkExpectedKeys(self::AUTHORIZED_KEYS, $data);
        
        $fileData = $this->uploadService->uploadImage($file, $restaurantId);
        $table = [
            "restaurant_id" => $restaurantId,
            "title" => $data['title'],
            "slug" => $this->slugifyUnique($data['title']),
            "public_id" => $fileData['public_id'],
            "secure_url" => $fileData["secure_url"],
            "width" => $fileData["width"],
            "height" => $fileData["height"],
            "position" => $this->getImageCount($restaurantId) + 1,
        ];
        $this->validateNotNullKeys(static::class, $table, true);
        return $this->galleryModel->addImage($table);
    }

    private function slugifyUnique(string $text): string
    {
        $slug = mb_strtolower($text, 'UTF-8'); # lowercase
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug); # retirer accents
        # remplace tout ce qui n'est pas lettre/chiffre par -
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-'); # supprime '-' début/fin
        $random = bin2hex(random_bytes(2)); // code aléatoire
        # check si unique
        if ($this->galleryModel->findSlug("{$slug}-{$random}")) {
            $this->slugifyUnique($text);
        }
        return "{$slug}-{$random}";
    }

    public function getRestaurantImages(int $restaurantId): ?array
    {
        $this->validatePositiveInteger($restaurantId);
        return $this->galleryModel->findAllImages($restaurantId);
    }

    public function getImageCount(int $restaurantId): int
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        $this->validatePositiveInteger($restaurantId);

        return $this->galleryModel->countImages($restaurantId);
    }

    public function modifyImage(array $data, ?array $file = null): array
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        if (empty($data)) {
            throw new InvalidFieldException("Veuillez renseigner les champs requis.");
        }
        $this->checkExpectedKeys(self::AUTHORIZED_KEYS, $data);
        $this->validatePositiveInteger($data['id']);
        $image = $this->galleryModel->findImageById($data['id']);

        $table = [
            "title" => $data['title'],
            "slug" => $this->slugifyUnique($data['title']),
        ];
        
        if ($file !== null) {
            if (empty($file) || !is_uploaded_file($file['tmp_name'])) {
                throw new DataProcessingException(__METHOD__ . ": Fichier attendu en deuxième paramètre.");
            }
            $fileData = $this->uploadService->uploadImage($file, $image['restaurant_id'], $image['public_id']);

            $table = array_merge($table, [
                "secure_url" => $fileData["secure_url"],
                "width" => $fileData["width"],
                "height" => $fileData["height"],
            ]);
        }
        $this->validateNotNullKeys(static::class, $table);
        return $this->galleryModel->updateImage($data['id'], $table);
    }

    public function changeImagesOrder(array $data): bool
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        if (!array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . ": Liste attendue en paramètre");
        }
        try {
            $this->galleryModel->beginTransaction();
            foreach ($data as $index => $id) {
                $this->galleryModel->updateImage($id, ['position' => $index]);
            }
            $this->galleryModel->commit();
            return true;
        } 
        catch (Throwable $e) {
            $this->galleryModel->rollBack();
            throw $e;
        }
    }

    public function deleteImage(int $imageId): int
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        $this->validatePositiveInteger($imageId);
        $image =$this->galleryModel->findImageById($imageId);

        $this->uploadService->deleteImage($image['public_id']);
        return $this->galleryModel->deleteImage($imageId);
    }
}