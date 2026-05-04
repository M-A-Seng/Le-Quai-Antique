<?php

namespace App\Core;

use App\Enums\Role;
use App\Exceptions\DataProcessingException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\RequireLoginException;
use App\Services\SessionService;

/**
 * Auth gère l'authentification utilisateur.
 */
class Auth
{
    private SessionService $session;
    
    /**
     * __construct
     *
     * @param  SessionService $session
     * @return void
     */
    public function __construct(SessionService $session)
    {
        $this->session = $session;
    }
        
    /**
     * login enregistre les données de session de l'utilisateur.
     *
     * - login()
     * - check()
     * - requireLogin()
     * - requireRole()
     * - logout()
     * 
     * @param  array $userData
     * @param  bool $newUser
     * @return void
     */
    public function login(array $userData, bool $newUser = false): void
    {
        if (empty($userData['id']) || empty($userData['role'])) {
            throw new DataProcessingException(__METHOD__ . ": Argument manquant: L'id et le rôle doivent obligatoirement être fournis.");
        }
        session_regenerate_id(true);
        $this->session->set('id', $userData['id']);
        $this->session->set('role', Role::from($userData['role']));
        $this->session->set('new_user', $newUser);
    }

    /**
     * check vérifie si la clé spécifiée est définit dans la session.
     *
     * @return bool
     */
    public function check(): bool 
    {
        return $this->session->has('id');
    }
    
    /**
     * requireLogin vérifie si l'utilisateur est connecté.
     *
     * @return void
     */
    public function requireLogin(): void
    {
        if (!$this->check()) {
            throw new RequireLoginException(__METHOD__ . ": Utilisateur non identifié.");
        }
    }
    
    /**
     * requireRole vérifie que l'utilisateur possède le rôle spécifié.
     *
     * @param  Role $role
     * @return void
     */
    public function requireRole(Role $role): void
    {
        $this->requireLogin();
        $userRole = $this->session->get('role');
        if ($role !== $userRole) {
            throw new ForbiddenException(__METHOD__ . ": Impossible d'accéder à cette page.");
        };
    }
    
    /**
     * logout déconnecte l'utilisateur.
     *
     * @return void
     */
    public function logout(): void
    {
        $this->session->destroy();
    }
}