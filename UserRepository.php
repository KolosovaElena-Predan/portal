<?php
class UserRepository
{
    private Database $db;
    
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
    
    public function findByLogin(string $login): ?User
    {
        $row = $this->db->fetch("SELECT id, email, name, login, role, password, is_verified FROM user WHERE login = ?", [$login]);
        if (!$row) return null;
        return $this->createUserFromRow($row);
    }
    
    public function getPasswordHash(string $login): ?string
    {
        $row = $this->db->fetch("SELECT password FROM user WHERE login = ?", [$login]);
        return $row['password'] ?? null;
    }
    
    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch("SELECT id FROM user WHERE email = ?", [$email]);
    }
    
    /**Создание нового пользователя*/
    public function create(array $data): int|false
    {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $result = $this->db->execute(
            "INSERT INTO user (email, name, login, password, role, is_verified) VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['email'],
                $data['name'],
                $data['login'],
                $hashedPassword,
                $data['role'] ?? 'client',
                $data['is_verified'] ?? 0
            ]
        );
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    /**Получение полных данных пользователя по ID*/
    public function findById(int $id): ?User
    {
        $row = $this->db->fetch("SELECT id, email, name, login, role, is_verified FROM user WHERE id = ?", [$id]);
        if (!$row) return null;
        return $this->createUserFromRow($row);
    }
    
   
    /**Не работает пока*/
public function setVerificationToken(int $userId, string $token, string $expires): bool
{
    $this->db->execute(
        "UPDATE user SET verification_token = ?, token_expires_at = ? WHERE id = ?",
        [$token, $expires, $userId]
    );
    return true;
}
    
    /**
     * Найти пользователя по токену подтверждения
     */
    public function findByVerificationToken(string $token): ?User
{
    $row = $this->db->fetch(
        "SELECT id, email, name, login, role, is_verified 
         FROM user 
         WHERE verification_token = ? AND token_expires_at > NOW()",
        [$token]
    );
    if (!$row) return null;
    return $this->createUserFromRow($row);
}
    
    /**
     * Подтвердить email пользователя
     */
    public function verifyUser(int $userId): bool
{
    $this->db->execute(
        "UPDATE user SET is_verified = 1, verification_token = NULL, token_expires_at = NULL WHERE id = ?",
        [$userId]
    );
    return true;
}
    
    
    private function createUserFromRow(array $row): User
    {
        unset($row['password']);
        return match ($row['role']) {
            'admin' => new AdminUser($row),
            'support_specialist' => new SupportUser($row),
            'client' => new ClientUser($row),
            default => new ClientUser($row)
        };
    }
}
?>