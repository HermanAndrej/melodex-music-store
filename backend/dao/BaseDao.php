<?php
require_once __DIR__ . '/../config.php';

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
            error_log("Database query failed: " . $e->getMessage());
            throw new Exception("Database query failed: " . $e->getMessage());
        }
    }

    public function getAll() {
        $sql = "SELECT * FROM " . $this->table;
        $result = $this->executeQuery($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $result ?: [];
    }

    public function getById($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE " . $this->primaryKey . " = :id";
        return $this->executeQuery($sql, [':id' => $id])->fetch(PDO::FETCH_ASSOC);
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
        $params = $data;
        $params['id'] = $id;
        $this->executeQuery($sql, $params);
        return true;
    }

    public function delete($id) {
        $sql = "DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = :id";
        $this->executeQuery($sql, [':id' => $id]);
        return true;
    }

    public function query_unique($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

}
?>
