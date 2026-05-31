<?php

namespace App\Models;

use App\Config\DbConnection;
use App\Core\Abstract\AbstractModel;
use App\Exceptions\DbFailureException;
use App\Exceptions\NotFoundException;
use PDOException;

class GalleryModel extends AbstractModel
{
    protected const TABLE = "image_gallery";
    protected const ALLOWED_COLUMNS = [
        "id",
        "restaurant_id",
        "title",
        "slug",
        "width",
        "height",
        "public_id",
        "secure_url",
        "position",
        "created_at",
        "updated_at",
    ];

    private array $readOnlyColumns = [
        "id",
        "created_at",
        "updated_at"
    ];

    public function __construct(DbConnection $connection)
    {
        parent::__construct($connection);
    }

    public function addImage(array $data): array
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        return $this->insert($data);
    }

    public function findAllImages(int $restaurantId): ?array
    {
        $result = $this->findBy(['restaurant_id' => $restaurantId], ['position' => 'ASC']);
        return empty($result) ? null : $result;
    }

    public function findImageById(int $id): array
    {
        $result = $this->findBy(['id' => $id]);
        if (empty($result)) {
            throw new NotFoundException(message:__METHOD__ . ": Image id $id introuvable.");
        }
        return $result[0];
    }

    public function findSlug(string $slug): ?array
    {
        $result = $this->findBy(['slug' => $slug]);
        return empty($result) ? null : $result[0];
    }

    public function updateImage(int $imageId, array $data): array
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        return $this->update($imageId, $data);
    }

    public function countImages(int $restaurantId): int
    {
        $sql = "SELECT COUNT(*) AS total_images
                FROM app_back.image_gallery
                WHERE restaurant_id = :restaurant_id;";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute(['restaurant_id' => $restaurantId]);
            return (int)$stmt->fetchColumn();
        } 
        catch (PDOException $e) {
            throw new DbFailureException(__METHOD__ . "Echec de l'opération: " . $e->getMessage(), 0, $e);
        }
    }

    public function deleteImage(int $imageId): int
    {
        return $this->delete(['id' => $imageId]);
    }
}