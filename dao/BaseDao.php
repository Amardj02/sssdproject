<?php
require_once __DIR__ . '/../config_default.php';
class BaseDao
{
    protected $conn;
    protected $table_name;

    public function __construct($table_name)
    {
        try {

            $this->table_name = $table_name;

            $host = DB_HOST;
            $username = DB_USERNAME;
            $password = DB_PASSWORD;
            $schema = DB_NAME;
            $port = DB_PORT;

            $this->conn = new PDO("mysql:host=$host;port=$port;dbname=$schema", $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo "Connection failed.";
        }
    }

    public function get_all()
    {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_by_id($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id = :id");
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            echo "Deletion was successful.";
        } else {
            echo "Deletion failed.";
        }
    }

    public function add($entity)
    {
        try {
            $query = "INSERT INTO " . $this->table_name . " (";
            foreach ($entity as $column => $value) {
                $query .= $column . ', '; 
            }

            $query = substr($query, 0, -2);
            $query .= ") VALUES (";
            foreach ($entity as $column => $value) {
                $query .= ":" . $column . ', '; 
            }

            $query = substr($query, 0, -2);
            $query .= ")";

            $stmt = $this->conn->prepare($query);
            $stmt->execute($entity);
            $entity['id'] = $this->conn->lastInsertId(); 
            return array("status" => 200, "message" => "Registration Successfull");
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return array("status" => 500, "message" => "Username or email are already registered");
        }

    }

    public function update($entity, $id, $id_column = "id")
    {
        try{
        $query = "UPDATE " . $this->table_name . " SET ";
        foreach ($entity as $column => $value) {
            $query .= $column . "= :" . $column . ", ";
        }
        $query = substr($query, 0, -2);
        $query .= " WHERE $id_column = :id";
        $stmt = $this->conn->prepare($query);
        $entity['id'] = $id;
        $stmt->execute($entity);
        return $entity;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return array("status" => 500, "message" => "Backend Error");
    }

}
}

