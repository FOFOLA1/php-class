<?php
class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function login($username, $password)
    {
        $stmt = $this->pdo->prepare("SELECT id, username, password, role FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public static function hashPassword($password)
    {
        $options = [
            'memory_cost' => 65536,
            'time_cost' => 12,
            'threads' => 1,
        ];
        $algo =  PASSWORD_ARGON2ID;

        return password_hash($password, $algo, $options);
    }

    public function register($username, $password)
    {
        $hash = self::hashPassword($password);

        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            return $stmt->execute([':username' => $username, ':password' => $hash]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
