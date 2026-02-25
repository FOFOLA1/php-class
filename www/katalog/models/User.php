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
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public static function hashPassword($password)
    {
        // Use Argon2id if available (slower, memory-hard), otherwise use Bcrypt with higher cost
        // password_hash() automatically generates a secure random salt for each password
        $options = [
            'memory_cost' => 65536, // 64MB
            'time_cost' => 4,
            'threads' => 2,
        ];
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;

        // If Argon2 isn't available and we fall back to Bcrypt, increase the cost
        if ($algo === PASSWORD_DEFAULT) {
            $options = ['cost' => 12];
        }

        return password_hash($password, $algo, $options);
    }

    public function register($username, $password)
    {
        $hash = self::hashPassword($password);

        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            return $stmt->execute([':username' => $username, ':password' => $hash]);
        } catch (PDOException $e) {
            // Handle unique constraint violation or multiple registration
            return false;
        }
    }
}
