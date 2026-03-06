<?php

namespace App\Services;

use App\Core\AbstractDataValidationService;
use App\Exceptions\InvalidCredentialsException;
use App\Models\UserModel;
use InvalidArgumentException;

/**
 * UserService
 */
class UserService extends AbstractDataValidationService
{
    private UserModel $userModel;
    protected const NOT_NULL_COLUMNS = [
        "last_name",
        "email",
        "password",
    ];
    
    /**
     * __construct
     *
     * @param  UserModel $userModel
     * @param  SessionService $session
     * @return void
     */
    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }
    
    /**
     * signUserUp créer un nouvel utilisateur puis le connecte.
     *
     * @param  array $data
     * @return array
     */
    public function signUserUp(array $data): array
    {
        $this->validateNotNullKeys(static::class, $data, true);

        if ($this->userModel->getUserByEmail($data['email'])) {
            throw new InvalidArgumentException("Cet email existe déjà pour un utilisateur.");
        }
        $domain = substr(strrchr($data['email'], "@"), 1);
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || !checkdnsrr($domain, "MX")) {
            throw new InvalidArgumentException("Email invalide.");
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $this->userModel->createUser($data);

        $authenticationData = [
            'email' => $data['email'],
            'password' => $data['password']
        ];
        return $this->authenticateUser($authenticationData);
    }
    
    /**
     * authenticateUser authentifie l'utilisateur pour le connecter.
     *
     * @param  array $data
     * @return array
     */
    public function authenticateUser(array $data): array
    {
        $expectedKeys = ['email', 'password', 'csrf_token'];
        $unknownKeys = array_diff(array_keys($data), $expectedKeys);
        if ($unknownKeys) {
            throw new InvalidArgumentException("Vous ne pouvez entrer qu'un email et un mot de passe.");
        }

        $this->validateNotNullKeys(static::class, $data, false);

        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $result = $this->userModel->getUserByEmail($data['email']);
        }
        if (empty($result) || !password_verify($data['password'], $result['password'])) {
            throw new InvalidCredentialsException("Email ou mot de passe invalide.");
        }

        $userData = [
            'id' => $result['id'],
            'role' => $result['role'],
        ];
        return $userData;
    }
    
    /**
     * updateUserProfile met à jour les données de l'utilisateur.
     *
     * @param  array $data
     * @return void
     */
    public function updateUserProfile(array $data): void
    {
        $this->validateNotNullKeys(static::class, $data, false);

        $user = $this->userModel->getUserById($data['id']);
        $userId = (int) $user['id'];

        unset($data['id'], $data['user_id']);

        $this->userModel->updateUser($userId, $data);
    }
    
    public function deleteUserAccount(int $id): void
    {
        $this->userModel->getUserById($id);
        $this->userModel->deleteUser($id);
    }
}