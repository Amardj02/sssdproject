<?php
require_once "BaseDao.php";


class UserDao extends BaseDao
{

  public function __construct()
  {
    parent::__construct("users");
  }

  public function checkExistenceForEmail($username)
  {
    try {
      $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email");
      $stmt->bindParam(':email', $username);
      $stmt->execute();
      $user = $stmt->fetch(PDO::FETCH_ASSOC);
      return $user;
    } catch (PDOException $e) {
      error_log($e->getMessage());
      return null;
    }
  }

  public function checkExistenceForUsername($username)
  {
    try {
      $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :username");
      $stmt->bindParam(':username', $username);
      $stmt->execute();
      $user = $stmt->fetch(PDO::FETCH_ASSOC);
      return $user;
    } catch (PDOException $e) {
      error_log($e->getMessage());
      return null;
    }
  }
  public function getUserByEmail($username)
  {
    try {
      $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email");
      $stmt->bindParam(':email', $username);
      $stmt->execute();
      $user = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($user) {
        return $user['password'];
      }
    } catch (PDOException $e) {
      error_log($e->getMessage());
      return null;
    }
  }

  public function getUserByUsername($username)
  {
    try {
      $stmt = $this->conn->prepare("SELECT password FROM users WHERE username = :username");
      $stmt->bindParam(':username', $username);
      $stmt->execute();
      $user = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($user) {
        return $user['password'];
      }
    } catch (PDOException $e) {
      error_log($e->getMessage());
      return null;
    }
  }

  public function updateLoginCount($email)
  {
    try {
      $stmt = $this->conn->prepare("UPDATE users SET login_count = login_count + 1 WHERE email = :email");
      $stmt->bindParam(':email', $email);
      $stmt->execute();
      $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log($e->getMessage());
      return null;
    }
  }
  public function updateSecretValue($secret, $username)
  {
    try {
      $stmt = $this->conn->prepare("UPDATE users SET secret = :secret WHERE username = :username");
      $stmt->bindParam(':secret', $secret);
      $stmt->bindParam(':username', $username);
      $stmt->execute();
      $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
  public function trackFailedAttempts($ipAddress)
  {
    try {
      $stmt = $this->conn->prepare("SELECT * FROM logs WHERE ip_address = :ip_address");
      $stmt->bindParam(':ip_address', $ipAddress);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($result) {
        $failedAttempts = $result['failed_login_count'] + 1;
        if ($failedAttempts >= 3) {
          $captchaRequired = 1;
        } else {
          $captchaRequired = 0;
        }
        $stmt = $this->conn->prepare("UPDATE logs SET failed_login_count = :failed_login_count, captcha_required = :captcha_required WHERE ip_address = :ip_address");
        $stmt->bindParam(':failed_login_count', $failedAttempts);
        $stmt->bindParam(':captcha_required', $captchaRequired);
        $stmt->bindParam(':ip_address', $ipAddress);
        $stmt->execute();
      } else {
        $stmt = $this->conn->prepare("INSERT INTO logs (ip_address, failed_login_count, captcha_required) VALUES (:ip_address, 1, 0)");
        $stmt->bindParam(':ip_address', $ipAddress);
        $stmt->execute();
      }
    } catch (PDOException $e) {
      error_log($e->getMessage());
    }
  }
  public function resetFailedAttempts($ipAddress)
  {
    try {
      $stmt = $this->conn->prepare("UPDATE logs SET failed_login_count = 0, captcha_required = 0 WHERE ip_address = :ip_address");
      $stmt->bindParam(':ip_address', $ipAddress);
      $stmt->execute();
    } catch (PDOException $e) {
      error_log($e->getMessage());
    }
  }

  public function numberOfFailedAttempts($ipAddress)
  {
    try {
      $stmt = $this->conn->prepare("SELECT failed_login_count FROM logs WHERE ip_address = :ip_address");
      $stmt->bindParam(':ip_address', $ipAddress);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result && $result['failed_login_count'] >= 3;
    } catch (PDOException $e) {
      error_log($e->getMessage());
    }
  }

}