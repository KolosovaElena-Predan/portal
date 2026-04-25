<?php
// Базовый абстрактный пользователь
abstract class User
{
    public int $id;
    public string $email;
    public string $name;
    public ?string $login;
    public string $role;
    public int $is_verified;
    
    public function __construct(array $data)
    {
        $this->id = (int)($data['id'] ?? 0);
        $this->email = $data['email'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->login = $data['login'] ?? null;
        $this->role = $data['role'] ?? 'guest';
        $this->is_verified = (int)($data['is_verified'] ?? 0);
    }
    
    abstract public function getDashboardUrl(): string;
    
    /**Проверка подтверждения email*/
    public function isVerified(): bool
    {
        return $this->is_verified === 1;
    }
}

// Конкретные реализации
class GuestUser extends User
{
    public function __construct()
    {
        parent::__construct(['role' => 'guest', 'name' => 'Гость']);
        $this->id = 0;
        $this->login = null;
        $this->email = '';
        $this->is_verified = 0;
    }
    
    public function getDashboardUrl(): string
    {
        return 'index.php';
    }
    
    public function isVerified(): bool
    {
        return false;
    }
}

class ClientUser extends User
{
    public function getDashboardUrl(): string
    {
        return 'mip/mip.php';
    }
    
    public function getDevices(): array
    {
        return [];
    }
}

class AdminUser extends User
{
    public function getDashboardUrl(): string
    {
        return 'admin/lk_admin.php';
    }
}

class SupportUser extends User
{
    public function getDashboardUrl(): string
    {
        return 'lk_support.php';
    }
    
    public function getAssignedClients(): array
    {
        return [];
    }
}
?>