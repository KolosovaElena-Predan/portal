<?php

// Базовый абстрактный пользователь
abstract class User
{
    public int $id;
    public string $email;
    public string $name;
    public ?string $login;
    public string $role;

    public function __construct(array $data)
    {
        $this->id = (int)($data['id'] ?? 0);
        $this->email = $data['email'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->login = $data['login'] ?? null;
        $this->role = $data['role'] ?? 'guest';
    }

    abstract public function getDashboardUrl(): string;
}

// === Конкретные реализации ===

class GuestUser extends User
{
    public function __construct()
    {
        parent::__construct(['role' => 'guest', 'name' => 'Гость']);
        $this->id = 0;
        $this->login = null;
        $this->email = '';
    }

    public function getDashboardUrl(): string
    {
        return 'index.php'; // или login.php
    }
}

class ClientUser extends User
{
    public function getDashboardUrl(): string
    {
        return 'index.php';
    }

    // Специфичная логика клиента
    public function getDevices(): array
    {
        // Можно интегрировать с DeviceRepository позже
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
        return []; // реализуется через SupportAssignment позже
    }
}