<?php

class Auth
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function attempt(string $login, string $password): ?User
    {
        $login = trim($login);
        if (empty($login) || empty($password)) {
            return null;
        }

        $hash = $this->userRepository->getPasswordHash($login);
        if (!$hash || !password_verify($password, $hash)) {
            return null;
        }

        return $this->userRepository->findByLogin($login);
    }

    public function login(User $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user->id;
        $_SESSION['role'] = $user->role;
        $_SESSION['name'] = $user->name;
        $_SESSION['login'] = $user->login;
    }

    public function logout(): void
    {
        session_start();
        session_destroy();
    }

    public function getCurrentUser(): User
    {
        session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === 0) {
            return new GuestUser();
        }

        $user = $this->userRepository->findByLogin($_SESSION['login']);
        return $user ?? new GuestUser();
    }

    public function redirectByRole(string $role): void
    {
        $user = match ($role) {
            'admin' => new AdminUser(['role' => 'admin']),
            'support_specialist' => new SupportUser(['role' => 'support_specialist']),
            'client' => new ClientUser(['role' => 'client']),
            default => new GuestUser()
        };

        header("Location: " . $user->getDashboardUrl());
        exit;
    }

    // ========== ДОБАВИТЬ ЭТИ МЕТОДЫ ==========

    /**
     * Создание токена для восстановления пароля
     */
    public function createPasswordResetToken(string $email): ?string
    {
        $user = $this->userRepository->getUserByEmail($email);
        if (!$user) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $this->userRepository->setResetToken($user['id'], $token, $expires);
        
        return $token;
    }

    /**
     * Проверка токена восстановления
     */
    public function validateResetToken(string $token): ?array
    {
        return $this->userRepository->findByResetToken($token);
    }

    /**
     * Сброс пароля по токену
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        $user = $this->userRepository->findByResetToken($token);
        if (!$user) {
            return false;
        }
        
        return $this->userRepository->updatePassword($user['id'], $newPassword);
    }
}