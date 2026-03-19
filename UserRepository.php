<?php

class UserRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db; // ← агрегация
    }

    // ============================================
    // СУЩЕСТВУЮЩИЕ МЕТОДЫ (для входа)
    // ============================================

    public function findByLogin(string $login): ?User
    {
        $row = $this->db->fetch("SELECT id, email, name, login, role, password FROM user WHERE login = ?", [$login]);
        if (!$row) return null;

        return $this->createUserFromRow($row);
    }

    public function getPasswordHash(string $login): ?string
    {
        $row = $this->db->fetch("SELECT password FROM user WHERE login = ?", [$login]);
        return $row['password'] ?? null;
    }

    // ============================================
    // НОВЫЕ МЕТОДЫ (для регистрации)
    // ============================================

    /**
     * Проверка: существует ли пользователь с таким email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch("SELECT id FROM user WHERE email = ?", [$email]);
    }

    /**
     * Создание нового пользователя
     * @return int|false ID нового пользователя или false при ошибке
     */
    public function create(array $data): int|false
    {
        // Хэшируем пароль
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Вставляем данные
        $result = $this->db->execute(
            "INSERT INTO user (email, name, login, password, role) VALUES (?, ?, ?, ?, ?)",
            [
                $data['email'],
                $data['name'],
                $data['login'],
                $hashedPassword,
                $data['role'] ?? 'client' // По умолчанию клиент
            ]
        );
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Получение полных данных пользователя по ID (нужно после create)
     */
    public function findById(int $id): ?User
    {
        $row = $this->db->fetch("SELECT id, email, name, login, role FROM user WHERE id = ?", [$id]);
        if (!$row) return null;

        return $this->createUserFromRow($row);
    }

    // ============================================
    // ВСПОМОГАТЕЛЬНЫЙ МЕТОД
    // ============================================

    private function createUserFromRow(array $row): User
    {
        // Если пароль есть в массиве — убираем его, чтобы не передавать в конструктор User
        unset($row['password']);
        
        return match ($row['role']) {
            'admin' => new AdminUser($row),
            'support_specialist' => new SupportUser($row),
            'client' => new ClientUser($row),
            default => new ClientUser($row)
        };
    }
}