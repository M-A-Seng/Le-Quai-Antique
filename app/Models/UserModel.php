<?php

namespace App\Models;

use App\Core\Abstract\AbstractModel;
use App\Exceptions\NotFoundException;

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
    # Les constantes sont utilisées dans AbstractModel.
    protected const ALLOWED_TABLES = ["users"];
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
    protected const TABLE = "users";

    private $readOnlyColumns = [
        "id",
        "user_id",
    ];
        
    /**
     * createUser créer un nouvel utilisateur dans la base de données.
     *
     * @param  mixed $data
     * @return void
     */
    public function createUser(array $data)
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        return $this->insert($data);
    }
    
    /**
     * getUserByEmail cherche l'existance d'un email dans la base de données.
     *
     * @param  mixed $email
     * @return void
     */
    public function getUserByEmail(string $email)
    {
        $result = $this->findBy("email", $email);
        return $result[0] ?? NULL;
    }
    
    /**
     * getUserById cherche l'existance de l'id de utilisateur.
     * 
     * Lance une exception si non trouvé.
     *
     * @param  mixed $id
     * @return void
     */
    public function getUserById(int $id)
    {
        $result = $this->findBy("id", $id);
        if (empty($result)) {
            throw new NotFoundException(message: "Utilisateur non trouvé en db.");
        }
        return $result[0];
    }
    
    /**
     * updateUser met à jour l'enregistrement d'un utilisateur.
     *
     * @param  int $id
     * @param  array $data
     * @return void
     */
    public function updateUser(int $id, array $data)
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        $this->getUserById($id);

        return $this->update($id, $data);
    }
    
    /**
     * deleteUser supprime un utilisateur de la base de données.
     *
     * @param  int $id
     * @return void
     */
    public function deleteUser(int $id)
    {
        $this->getUserById($id);
        return $this->delete(["id" => $id]);
    }
}