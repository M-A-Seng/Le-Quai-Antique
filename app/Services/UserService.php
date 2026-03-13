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
     * emailCheck vérifie la validité et l'existence de l'email dans la db.
     *
     * @param  string $email
     * @return void
     */
    public function emailCheck(string $email): void
    {
        $domain = substr(strrchr($email, "@"), 1);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !checkdnsrr($domain, "MX")) {
            throw new InvalidArgumentException("Email invalide.");
        }
        if ($this->userModel->getUserByEmail($email)) {
            throw new InvalidArgumentException("Cet email est déjà utilisé par un utilisateur.");
        }
    }
    
    /**
     * passwordCheck vérifie la solidité d'un nouveau mot de passe.
     *
     * @param  string $password
     * @return void
     */
    public function passwordCheck(string $password): void
    {
        $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/';
        if (!preg_match($regex, $password)) {
            throw new InvalidArgumentException("Votre mot de passe n'est pas assez sécurisé, veuillez suivre les instructions affichées.");
        }
    }

    /**
     * signUserUp créer un nouvel utilisateur puis le connecte.
     *
     * @param  array $data
     * @return array
     */
    public function signUserUp(array $data): void
    {
        unset($data['csrf_token'], $data['password-confirm']);
        $this->validateNotNullKeys(static::class, $data, true);
        $this->emailCheck($data['email']);
        $this->passwordCheck($data['password']);
        $data['allergy'] = implode(', ', $data['allergy']);
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $this->userModel->createUser($data);
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