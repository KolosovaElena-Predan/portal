<?php

class UserRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db; // ← агрегация
    }

    public function findByLogin(string $login): ?User
    {
        $row = $this->db->fetch("SELECT id, email, name, login, role FROM user WHERE login = ?", [$login]);
        if (!$row) return null;

        return $this->createUserFromRow($row);
    }

    public function getPasswordHash(string $login): ?string
    {
        $row = $this->db->fetch("SELECT password FROM user WHERE login = ?", [$login]);
        return $row['password'] ?? null;
    }

    private function createUserFromRow(array $row): User
    {
        return match ($row['role']) {
            'admin' => new AdminUser($row),
            'support_specialist' => new SupportUser($row),
            'client' => new ClientUser($row),
            default => new ClientUser($row)
        };
    }
}