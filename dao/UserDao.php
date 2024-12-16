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

}