<?php

namespace App\Services;

use App\Core\AbstractDataProcessingService;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\InvalidFieldException;
use App\Models\UserModel;

/**
 * UserService implémenter les opérations utilisateur.
 * 
 * - emailCheck()
 * - passwordCheck()
 * - signUserUp()
 * - authenticateUser()
 * - updateUserProfile()
 * - deleteUserAccount()
 */
class UserService extends AbstractDataProcessingService
{
    private UserModel $userModel;
    protected const NOT_NULL_COLUMNS = [
        "last_name",
        "email",
        "password",
    ];
    protected const STRING_COLUMNS = [
        "first_name",
        "last_name",
        "allergy"
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
            throw new InvalidFieldException("Email invalide.");
        }
        if ($this->userModel->getUserByEmail($email)) {
            throw new InvalidFieldException("Cet email est déjà utilisé par un utilisateur.");
        }
    }
    
    /**
     * passwordCheck vérifie REGEX du mot de passe + le champ de confirmation.
     *
     * @param  string $password
     * @param  string $passwordConfirm
     * @return void
     */
    public function passwordCheck(string $password, string $passwordConfirm): void
    {
        if (!preg_match(parent::REGEX['password'], $password)) {
            throw new InvalidFieldException("Votre mot de passe n'est pas assez sécurisé, veuillez suivre les instructions affichées.");
        }
        if ($password !== $passwordConfirm) {
            throw new InvalidFieldException("Les mots de passe ne correspondent pas.");
        }
    }
    
    /**
     * phoneNumberCheck vérifie la syntaxe du numéro de téléphone.
     *
     * @param  string $phoneNumber
     * @return void
     */
    public function phoneNumberCheck(string $phoneNumber): void
    {
        $phoneNumber = trim($phoneNumber);
        if (!empty($phoneNumber)) {
            if (!preg_match(parent::REGEX['phone'], $phoneNumber)) {
                throw new InvalidFieldException("Numéro de téléphone invalide.");
            }
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
        $data = $this->trimAllValuesInArray($data);

        # champs obligatoires
        $this->validateNotNullKeys(static::class, $data, true);
        $this->emailCheck($data['email']);
        $this->passwordCheck($data['password'], $data['password-confirm']);

        # champs facultatifs
        if (isset($data['tel']) && !empty($data['tel'])) {
            $this->phoneNumberCheck($data['tel']);
        }
        if (isset($data['allergy']) && !empty($data['allergy']) && is_array($data['allergy'])) {
            $data['allergy'] = implode(', ', $data['allergy']);
        }

        # ---
        unset($data['csrf_token'], $data['password-confirm']);
        $data = $this->sanitizeTextValuesInArray($data, self::STRING_COLUMNS);
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
            throw new InvalidFieldException("Vous ne pouvez entrer qu'un email et un mot de passe.");
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
        $this->userModel->getUserById($_SESSION['id']); # S'assurer que l'utilisateur de la session existe en db
        unset($data['id'], $data['user_id']);
        $data = $this->sanitizeTextValuesInArray($data, self::STRING_COLUMNS);

        $this->userModel->updateUser($_SESSION['id'], $data);
    }
        
    /**
     * deleteUserAccount supprime le compte utilisateur.
     *
     * @param  int $id
     * @return void
     */
    public function deleteUserAccount(int $id): void
    {
        $this->userModel->getUserById($_SESSION['id']); # S'assurer que l'utilisateur de la session existe en db
        if ($id === (int)$_SESSION['id']) {
            $this->userModel->deleteUser($id);
        }
    }
}