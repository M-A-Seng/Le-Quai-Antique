<?php

namespace App\Models;

use App\Core\Abstract\AbstractModel;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServerException;

/**
 * UserModel implémente un CRUD utilisateur.
 * 
 * - createUser()
 * - getUserByEmail()
 * - getUserById()
 * - updateUser()
 * - deleteUser()
 */
class UserModel extends AbstractModel
{
    # Constantes utilisées dans AbstractModel.
    protected const TABLE = "users";
    protected const ALLOWED_COLUMNS = [
        "id",
        "user_id",
        "first_name",
        "last_name",
        "email",
        "tel",
        "password",
        "allergy",
        "default_guest_count", 
    ];

    private array $readOnlyColumns = [
        "id",
        "user_id",
    ];
        
    /**
     * createUser créer un nouvel utilisateur dans la base de données.
     *
     * @param  array $data
     * @return array
     */
    public function createUser(array $data): array
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        return $this->insert($data);
    }
    
    /**
     * getUserByEmail cherche l'existance d'un email dans la base de données.
     *
     * @param  string $email
     * @return array|null
     */
    public function getUserByEmail(string $email): array|null
    {
        $result = $this->findBy(["email" => $email]);
        return $result[0] ?? NULL;
    }
    
    /**
     * getUserById cherche l'existance de l'id de utilisateur.
     *
     * @param  int $id
     * @return ?array
     */
    public function getUserById(int $id): ?array
    {
        $result = $this->findBy(["id" => $id]);
        return !empty($result[0]) ? $result[0] : null;
    }
    
    /**
     * updateUser met à jour l'enregistrement d'un utilisateur.
     *
     * @param  int $id
     * @param  array $data
     * @return array
     */
    public function updateUser(int $id, array $data): array
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        $this->getUserById($id);

        return $this->update($id, $data);
    }
    
    /**
     * deleteUser supprime un utilisateur de la base de données.
     *
     * @param  int $id
     * @param string $email
     * @return int
     */
    public function deleteUser(int $id, string $email): int
    {
        $userById = $this->getUserById($id);
        $userByEmail = $this->getUserByEmail($email);
        if ($userById["email"] !== $email || $userByEmail["id"] !== $id) {
            throw new ServerException(__METHOD__ . ": Echec de suppression utilisateur. L'email '$email' ne correspond pas à l'utilisateur $id.");
        }
        return $this->delete(["id" => $id, "email" => $email]);
    }
}