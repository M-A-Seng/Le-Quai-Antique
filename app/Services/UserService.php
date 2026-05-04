<?php

namespace App\Services;

use App\Core\Abstract\AbstractService;
use App\Enums\Role;
use App\Exceptions\DataProcessingException;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\InvalidFieldException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServerException;
use App\Models\UserModel;

/**
 * UserService implémenter les opérations utilisateur.
 * 
 * - emailCheck()
 * - passwordCheck()
 * - signUserUp()
 * - authenticateUser()
 * - updateUserProfile()
 * - getUserParameters()
 * - deleteUserAccount()
 */
class UserService extends AbstractService
{
    # Constante utilisée par AbstractService
    protected const NOT_NULL_COLUMNS = [
        "last_name",
        "email",
        "password",
    ];
    private array $signupExpectedInputs = [
        "first_name",
        "last_name",
        "email",
        "tel",
        "password",
        "password-confirm",
        "default_guest_count",
        "allergy"
    ];
    private array $loginExpectedInputs = [
        "email",
        "password",
    ];
    
    public function __construct(private UserModel $userModel) {}

    /**
     * emailCheck vérifie la validité et l'existence de l'email dans la db.
     *
     * @param  string $email
     * @param  bool $forNewUser || True = signup / False = login
     * @return bool
     */
    public function emailCheck(string $email, bool $forNewUser): bool
    {
        $email = strtolower($email);
        $domain = substr(strrchr($email, "@"), 1);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !checkdnsrr($domain, "MX")) {
            throw new InvalidFieldException("Email invalide.");
        }
        if ($forNewUser && !empty($this->userModel->getUserByEmail($email))) {
            throw new InvalidFieldException("Cet email est déjà utilisé par un utilisateur.");
        }
        return true;
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
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/', $password)) {
            throw new InvalidFieldException("Votre mot de passe n'est pas assez sécurisé, veuillez suivre les instructions affichées.");
        }
        if ($password !== $passwordConfirm) {
            throw new InvalidFieldException("Les mots de passe ne correspondent pas.");
        }
    }
    
    /**
     * signUserUp créer un nouvel utilisateur puis le connecte.
     *
     * @param  array $data
     * @return void
     */
    public function signUserUp(array $data): void
    {
        if (empty($data) || array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . ": Un tableau associatif est attendu en paramètre.");
        }
        $this->checkExpectedKeys($this->signupExpectedInputs, $data);
        $data = $this->trimStringValuesInArray($data);

        # champs obligatoires
        $this->validateNotNullKeys(static::class, $data, true);
        $this->emailCheck($data['email'], true);
        $this->passwordCheck($data['password'], $data['password-confirm']);

        # champs facultatifs
        if (isset($data['tel']) && !empty($data['tel'])) {
            $phoneNumber = $this->phoneNumberCheckAndSanitize($data['tel']);
            $data['tel'] = $phoneNumber;
        }
        if (isset($data['allergy']) && !empty($data['allergy']) && is_array($data['allergy'])) {
            $data['allergy'] = implode(', ', $data['allergy']);
        }

        # ---
        unset($data['password-confirm']);
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
        if (empty($data) || array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . ": Tableau associatif attendu en paramètre.");
        }
        $this->checkExpectedKeys($this->loginExpectedInputs, $data, true);
        $this->validateNotNullKeys(static::class, $data, false);

        if ($this->emailCheck($data['email'], false)) {
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
     * @return array
     */
    public function updateUserProfile(array $data): array
    {
        $this->checkUserLegitimacy($data['user_id'], [Role::CLIENT]);

        if (empty($data) || array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . ": Un tableau associatif est attendu en paramètre.");
        }
        $data = $this->trimStringValuesInArray($data);
        $this->checkExpectedKeys($this->signupExpectedInputs, $data);
        $this->validateNotNullKeys(static::class, $data, false);
        $this->userModel->getUserById($_SESSION['id']) ?? throw new NotFoundException(UIMessage:"Impossible de mettre à jour le profil utilisateur. Veuillez réessayer.");

        if (isset($data['password']) && !empty($data['password'])) {
            $this->passwordCheck($data['password'], $data['password-confirm']);
        }
        if (isset($data['email']) && !empty($data['email'])) {
            $this->emailCheck($data['email'], false);
        }
        if (isset($data['tel']) && !empty($data['tel'])) {
            $phoneNumber = $this->phoneNumberCheckAndSanitize($data['tel']);
            $data['tel'] = $phoneNumber;
        }
        if (isset($data['allergy']) && !empty($data['allergy'])) {
            if (is_array($data['allergy'])) {
                $data['allergy'] = implode(', ', $data['allergy']);
            }
            elseif (!is_string($data['allergy'])) {
                throw new InvalidFieldException("Allergies invalides.");
            }
        }

        unset($data['id'], $data['user_id']);
        return $this->userModel->updateUser($_SESSION['id'], $data);
    }
    
    /**
     * getUserParameters retourne les données de l'utilisateur demandé.
     *
     * @param  int $userId
     * @return ?array
     */
    public function getUserParameters(int $userId): ?array
    {
        $this->validatePositiveInteger($userId);
        $this->checkUserLegitimacy($userId);
        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            return null;
        }
        $allergy = [];
        if (!empty($result['allergy'])) {
            $allergy = explode(', ', $result['allergy']);
        }
        return [
            'guest_count' => $user['default_guest_count'],
            'client_name' => $user['last_name'],
            'client_tel' => $user['tel'] ?? null,
            'allergy_array' => $allergy,
        ];
    }
        
    /**
     * deleteUserAccount supprime le compte utilisateur.
     *
     * @param  int $id
     * @return void
     */
    public function deleteUserAccount(int $id): void
    {
        $this->validatePositiveInteger($id);
        $this->checkUserLegitimacy($id, [Role::CLIENT]);
        
        $user = $this->userModel->getUserById($_SESSION['id']) ?? throw new ServerException(__METHOD__ . ": Tentative de supprimer un user inexistant.");
        if ($id === (int)$_SESSION['id']) {
            $this->userModel->deleteUser($id, $user['email']);
        }
    }
}