<?php
// models/TodoModel.php
require_once __DIR__ . '/../config.php';

class TodoModel
{
    private $db;

    public function __construct()
    {
        $host = DB_HOST;
        $port = DB_PORT;
        $dbname = DB_NAME;
        $user = DB_USER;
        $password = DB_PASSWORD;

        try {
            $this->db = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }

    public function getTodos($filter = 'all', $q = '')
    {
        try {
            $sql = "SELECT * FROM db_todo";
            $where = [];
            $params = [];

            if ($filter === 'finished') {
                $where[] = "is_finished = TRUE";
            } elseif ($filter === 'unfinished') {
                $where[] = "is_finished = FALSE";
            }

            if ($q !== '') {
                $where[] = "(LOWER(title) LIKE :q OR LOWER(description) LIKE :q)";
                $params[':q'] = '%' . strtolower($q) . '%';
            }

            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }

            $sql .= " ORDER BY position ASC, created_at DESC";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error saat mengambil data: " . $e->getMessage());
        }
    }

    public function getTodoById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM db_todo WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function titleExists($title, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) FROM db_todo WHERE LOWER(title) = LOWER(:title)";
        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':title', $title);
        if ($excludeId !== null) {
            $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

   public function createTodo($title, $description)
{
    try {
        $stmt = $this->db->query("SELECT COALESCE(MAX(position), 0) + 1 AS next_pos FROM db_todo");
        $nextPos = (int) $stmt->fetchColumn();

        $query = 'INSERT INTO db_todo (title, description, is_finished, position, created_at, updated_at)
                  VALUES (:title, :description, FALSE, :position, NOW(), NOW())';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':position', $nextPos, PDO::PARAM_INT);
        $stmt->execute();

        echo "✅ Data berhasil ditambah!"; // tambahkan ini sementara untuk tes
    } catch (PDOException $e) {
        die("❌ Error saat menambah data: " . $e->getMessage());
    }
}

    public function updateTodo($id, $title, $description, $is_finished)
    {
        try {
            $query = 'UPDATE db_todo
                      SET title = :title,
                          description = :description,
                          is_finished = :is_finished,
                          updated_at = NOW()
                      WHERE id = :id';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':is_finished', $is_finished, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error saat mengupdate data: " . $e->getMessage());
        }
    }

    public function deleteTodo($id)
    {
        try {
            $query = 'DELETE FROM db_todo WHERE id = :id';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error saat menghapus data: " . $e->getMessage());
        }
    }

    public function reorderPositions(array $orderMap)
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("UPDATE db_todo SET position = :position WHERE id = :id");
            foreach ($orderMap as $id => $position) {
                $stmt->bindValue(':position', (int)$position, PDO::PARAM_INT);
                $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
                $stmt->execute();
            }
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
?>
