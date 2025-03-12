<?php
declare(strict_types=1);
namespace Framework;
use PDO;
use App\Database;
abstract class Model
{
    protected $table;
    protected array $errors = [];


    public function __construct(protected Database $database)
    {
    }

    protected function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getTable(): string
    {
        if($this->table !== null){
            return $this->table;
        }
        $parts = explode("\\", $this::class);
        return strtolower(array_pop($parts));
    }

    public function getInsertId(): string
    {
        $conn = $this->database->getConnection();
        return $conn->lastInsertId();

    }

    public function findAll(string $table = null): array|object
    {
        $table ??= $this->getTable();
        $conn = $this->database->getConnection();
        $sql = "SELECT * FROM {$table} WHERE deleted_on IS NULL";
        $stmt = $conn->query($sql);
        return  $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getAll(string $table = null): array|object
    {
        return $this->findAll($table);
    }

    public function readAll(string $table = null): array|object
    {
        return $this->findAll($table);
    }

    public function pullAllByLimit(int $limit = 10, int $offset = 0, string $table = null): array|object
    {
        $table ??= $this->getTable();
        $conn = $this->database->getConnection();
        $sql = "SELECT * FROM {$table}  ORDER BY id ASC LIMIT {$limit} OFFSET {$offset}";
        $stmt = $conn->query($sql);
        return  $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findByQueryString($sql): object |array
    {
        $conn = $this->database->getConnection();
        $stmt = $conn->query($sql);
        return  $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getByQueryString(string $sql): object | array
    {
        return $this->findByQueryString($sql);
    }

    public function executeQueryString(string $sql): mixed
    {
        $conn = $this->database->getConnection();
        $stmt = $conn->query($sql);
        return $stmt->execute();
    }

    public function first(string $table = null): array|object
    {
        $table ??= $this->getTable();
        $conn = $this->database->getConnection();
        $sql = "SELECT * FROM {$table} WHERE deleted_on IS NULL";
        $stmt = $conn->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 
        $result = array_shift($result);
        $result = (object) $result;
        return  $result;
    }

    public function last(string $table = null): array|object
    {
        $table ??= $this->getTable();
        $conn = $this->database->getConnection();
        $sql = "SELECT * FROM {$table} WHERE deleted_on IS NULL";
        $stmt = $conn->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 
        $result = array_pop($result);
        $result = (object) $result;
        return  $result;
    }

    public function findById(int $id, string $table = null): object|bool
    {
        $table ??= $this->getTable();
        $conn = $this->database->getConnection();
        $sql = "SELECT * FROM {$table} WHERE id = :id AND deleted_on IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getById(int $id, string $table = null): object|bool
    {
        return $this->findById($id, $table);
    }

    public function pullById(int $id, string $table = null): object|bool
    {
        $table ??= $this->getTable();
        $conn = $this->database->getConnection();
        $sql = "SELECT * FROM {$table} WHERE id = :id ";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function findByField(string $field, mixed $value, string $table = null): object|bool
    {
        $table ??= $this->getTable();
        $sql = "SELECT * FROM {$table} WHERE {$field} = :field AND deleted_on IS NULL";
        $type = match(gettype($value)){
            "boolean" => PDO::PARAM_BOOL,
            "integer" => PDO::PARAM_INT,
            "NULL" => PDO::PARAM_NULL,
            default => PDO::PARAM_STR
        };
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":field", $value, $type);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getByField(string $field, mixed $value, string $table = null): object|bool
    {
        return $this->findByField($field, $value, $table);
    }

    public function findByFields(array $fields, string $table = null): object|bool
    {
        $table ??= $this->getTable();
        $sql = "SELECT * FROM {$table} WHERE ";

        $count = count($fields);
        $i = 1;
        foreach($fields as $key => $value){
            $sql .= " {$key} = :{$key}";
            if($i < $count){
                $sql .= " AND ";
            }else{
                $sql .= " AND deleted_on IS NULL";
            }
            $i++;
        }
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);

        foreach($fields as $key => $value){
            $type = match(gettype($value)){
                "boolean" => PDO::PARAM_BOOL,
                "integer" => PDO::PARAM_INT,
                "NULL" => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };
            $stmt->bindValue(":{$key}", $value, $type);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getByFields(array $fields, string $table = null): object|bool
    {
        return $this->findByFields($fields, $table);
    }

    public function pullByFields(array $fields, string $table = null): object|bool
    {
        $table ??= $this->getTable();
        $sql = "SELECT * FROM {$table} WHERE ";
        $count = count($fields);
        $i = 1;
        foreach($fields as $key => $value){
            $sql .= " {$key} = :{$key}";
            if($i < $count){
                $sql .= " AND ";
            }
            $i++;
        }
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        foreach($fields as $key => $value){
            $type = match(gettype($value)){
                "boolean" => PDO::PARAM_BOOL,
                "integer" => PDO::PARAM_INT,
                "NULL" => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };
            $stmt->bindValue(":{$key}", $value, $type);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function findAllByFields(array $fields, string $table = null): mixed
    {
        $table ??= $this->getTable();
        $sql = "SELECT * FROM {$table} WHERE ";

        $count = count($fields);
        $i = 1;
        foreach($fields as $key => $value){
            $sql .= " {$key} = :{$key}";
            if($i < $count){
                $sql .= " AND ";
            }else{
                $sql .= " AND deleted_on IS NULL";
            }
            $i++;
        }
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        foreach($fields as $key => $value){
            $type = match(gettype($value)){
                "boolean" => PDO::PARAM_BOOL,
                "integer" => PDO::PARAM_INT,
                "NULL" => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };
            $stmt->bindValue(":{$key}", $value, $type);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getAllByFields(array $fields, string $table = null): mixed
    {
        return $this->findAllByFields($fields, $table);
    }

    public function pullAllByFields(array $fields, string $table = null): mixed
    {
        $table ??= $this->getTable();
        $sql = "SELECT * FROM {$table} WHERE ";
        $count = count($fields);
        $i = 1;
        foreach($fields as $key => $value){
            $sql .= " {$key} = :{$key}";
            if($i < $count){
                $sql .= " AND ";
            }
            $i++;
        }
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        foreach($fields as $key => $value){
            $type = match(gettype($value)){
                "boolean" => PDO::PARAM_BOOL,
                "integer" => PDO::PARAM_INT,
                "NULL" => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };
            $stmt->bindValue(":{$key}", $value, $type);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findAllByField(string $field, mixed $value, string $table = null): mixed
    {
        $table ??= $this->getTable();
        $sql = "SELECT * FROM {$table} WHERE {$field} = :field AND deleted_on IS NULL";
        $type = match(gettype($value)){
            "boolean" => PDO::PARAM_BOOL,
            "integer" => PDO::PARAM_INT,
            "NULL" => PDO::PARAM_NULL,
            default => PDO::PARAM_STR
        };
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":field", $value, $type);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function pullAllByField(string $field, mixed $value, string $table = null): mixed
    {
        $table ??= $this->getTable();
        $sql = "SELECT * FROM {$table} WHERE {$field} = :field ";
        $type = match(gettype($value)){
            "boolean" => PDO::PARAM_BOOL,
            "integer" => PDO::PARAM_INT,
            "NULL" => PDO::PARAM_NULL,
            default => PDO::PARAM_STR
        };
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":field", $value, $type);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function pullByField(string $field, mixed $value, string $table = null): object|bool
    {
        $table ??= $this->getTable();
        $sql = "SELECT * FROM {$table} WHERE {$field} = :field ";
        $type = match(gettype($value)){
            "boolean" => PDO::PARAM_BOOL,
            "integer" => PDO::PARAM_INT,
            "NULL" => PDO::PARAM_NULL,
            default => PDO::PARAM_STR
        };
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":field", $value, $type);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function insert(array|object $data, string $table = null): bool
    {
        if(!empty($this->errors)){
            return false;
        }
        $data = (array) $data;
        $conn = $this->database->getConnection();
        $fields = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $table ??= $this->getTable();
        $sql = "INSERT INTO `{$table}` ($fields) VALUES ($placeholders)";
        $stmt = $conn->prepare($sql);
        $i = 1;
        foreach($data as $value){
            $type = match(gettype($value)){
                "boolean" => PDO::PARAM_BOOL,
                "integer" => PDO::PARAM_INT,
                "NULL" => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };
            $stmt->bindValue($i++, $value, $type);
        }
        return $stmt->execute();
    }

    public function create(array|object $data, string $table = null): bool
    {
        return $this->insert($data, $table);
    }

    public function updateRowById(int $id, array|object $data, string $table = null): bool
    {
        if(!empty($this->errors)){
            return false;
        }
        $table ??= $this->getTable();
        $sql = "UPDATE {$table} SET ";
        $data = (array) $data;
        unset($data['id']);
        $assignments = array_keys($data);
        array_walk($assignments, function(&$value){
            $value = " $value = ?";
        });
        $sql .= implode(',', $assignments)." WHERE id = ? ";
        $conn = $this->database->getConnection();
        $sql .= " AND deleted_on IS NULL";
        $stmt = $conn->prepare($sql);
        $i = 1;
        foreach($data as $value){
            $type = match(gettype($value)){
                "boolean" => PDO::PARAM_BOOL,
                "integer" => PDO::PARAM_INT,
                "NULL" => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };
            $stmt->bindValue($i++, $value, $type);
        }
        $stmt->bindValue($i, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function amendRowById(int $id, array|object $data, string $table = null): bool
    {
        if(!empty($this->errors)){
            return false;
        }
        $table ??= $this->getTable();
        $sql = "UPDATE {$table} SET ";
        $data = (array) $data;
        unset($data['id']);
        $assignments = array_keys($data);
        array_walk($assignments, function(&$value){
            $value = " $value = ?";
        });
        $sql .= implode(',', $assignments)." WHERE id = ? ";
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $i = 1;
        foreach($data as $value){
            $type = match(gettype($value)){
                "boolean" => PDO::PARAM_BOOL,
                "integer" => PDO::PARAM_INT,
                "NULL" => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };
            $stmt->bindValue($i++, $value, $type);
        }
        $stmt->bindValue($i, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function editRowById(int $id, array|object $data, string $table = null): bool
    {
        return $this->amendRowById($id,  $data, $table);
    }

    public function deleteRow(int $id, string $table = null): bool
    {
        $table ??= $this->getTable();
        $sql = "UPDATE {$table} SET deleted_on = NOW() WHERE id = :id";
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteByfield(string $field, mixed $value, string $table = null): bool
    {
        $table ??= $this->getTable();
        $sql = "UPDATE {$table} SET deleted_on = NOW() WHERE {$field} = :field_value";
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":field_value", $value, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function destroyRow(int $id, string $table = null): bool
    {
        $table ??= $this->getTable();
        $sql = "DELETE FROM  {$table} WHERE id = :id";
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function destroyByfield(string $field, mixed $value, string $table = null): bool
    {
        $table ??= $this->getTable();
        $sql = "DELETE FROM  {$table} WHERE {$field} = :field_value";
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":field_value", $value, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function recoverRow(int $id, string $table = null): bool
    {
        $table ??= $this->getTable();
        $sql = "UPDATE {$table} SET deleted_on = NULL WHERE id = :id";
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function rowCount(string $table = null): int
    {
        $table ??= $this->getTable();
        $sql = "SELECT COUNT(*) AS total FROM {$table} WHERE deleted_on IS NULL";
        $conn = $this->database->getConnection();
        $stmt = $conn->query($sql);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return (int) $row->total;	
    }

    public function rowTotal(string $table = null): int
    {
        $table ??= $this->getTable();
        $sql = "SELECT COUNT(*) AS total FROM {$table} ";
        $conn = $this->database->getConnection();
        $stmt = $conn->query($sql);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return (int) $row->total;	
    }

    public function fieldValueExists(string $field, mixed $value, string $table = null): bool
    {
        $table ??= $this->getTable();
        $conn = $this->database->getConnection();
        $sql = "SELECT * FROM {$table} WHERE {$field} = :val";
        $stmt = $conn->prepare($sql);
        $type = match(gettype($value)){
                "boolean" => PDO::PARAM_BOOL,
                "integer" => PDO::PARAM_INT,
                "NULL" => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
        };
        $stmt->bindValue(":val", $value, $type);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ) ? true : false;	
    }
}