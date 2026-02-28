<?php

namespace App\Services;

use App\Core\AbstractDataValidationService;
use App\Exceptions\ValidationException;
use App\Models\UserModel;
use InvalidArgumentException;

class UserService extends AbstractDataValidationService
{
    private UserModel $userModel;
    protected const NOT_NULL_COLUMNS = [
        "last_name",
        "email",
        "password",
    ];

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public function signUserUp(array $data): void
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
    }

    public function authenticateUser(array $data): string
    {
        $expectedKeys = ['email', 'password'];
        $unknownKeys = array_diff(array_keys($data), $expectedKeys);
        if ($unknownKeys) {
            throw new InvalidArgumentException("Clés invalides: " . implode(", ", $unknownKeys));
        }

        $this->validateNotNullKeys(static::class, $data, false);

        $result = $this->userModel->getUserByEmail($data['email']);

        if (empty($result) || !password_verify($data['password'], $result['password'])) {
            throw new ValidationException("Email ou mot de passe invalide.");
        }

        $userRole = $result['role'];
        return $userRole;
    }

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