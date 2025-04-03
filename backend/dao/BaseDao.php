<?php
require_once '../config.php';

class BaseDao {
    protected $table;
    protected $primaryKey;
    protected $connection;

    public function __construct($table, $primaryKey = 'id') {
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->connection = Database::connect();
    }

    protected function executeQuery($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Database query failed: " . $e->getMessage());
        }
    }

    public function getAll() {
        $sql = "SELECT * FROM " . $this->table;
        return $this->executeQuery($sql)->fetchAll();
    }

    public function getById($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE " . $this->primaryKey . " = :id";
        return $this->executeQuery($sql, [':id' => $id])->fetch();
    }

    public function insert($data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO " . $this->table . " ($columns) VALUES ($placeholders)";
        $this->executeQuery($sql, $data);
        return $this->connection->lastInsertId();
    }

    public function update($id, $data) {
        $setClauses = array_map(fn($key) => "$key = :$key", array_keys($data));
        $setString = implode(", ", $setClauses);
        $sql = "UPDATE " . $this->table . " SET $setString WHERE " . $this->primaryKey . " = :id";
        $data['id'] = $id;
        $this->executeQuery($sql, $data);
        return true;
    }

    public function delete($id) {
        $sql = "DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = :id";
        $this->executeQuery($sql, [':id' => $id]);
        return true;
    }
}
?>
