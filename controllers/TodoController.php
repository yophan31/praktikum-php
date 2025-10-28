<?php
// controllers/TodoController.php
require_once __DIR__ . '/../models/TodoModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

class TodoController
{
    private $model;

    public function __construct()
    {
        $this->model = new TodoModel();
    }

    public function index()
    {
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $todos = $this->model->getTodos($filter, $q);
        include __DIR__ . '/../views/TodoView.php';
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($title === '') {
                $_SESSION['error'] = "Judul tidak boleh kosong.";
                header('Location: index.php');
                exit;
            }

            if ($this->model->titleExists($title)) {
                $_SESSION['error'] = "Judul sudah ada. Gunakan judul lain.";
                header('Location: index.php');
                exit;
            }

            $this->model->createTodo($title, $description);
        }
        header('Location: index.php');
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $is_finished = isset($_POST['is_finished']) ? true : false;

            if ($title === '') {
                $_SESSION['error'] = "Judul tidak boleh kosong.";
                header('Location: index.php');
                exit;
            }

            if ($this->model->titleExists($title, $id)) {
                $_SESSION['error'] = "Judul sudah ada. Gunakan judul lain.";
                header('Location: index.php');
                exit;
            }

            $this->model->updateTodo($id, $title, $description, $is_finished);
        }
        header('Location: index.php');
    }

    public function delete()
    {
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $this->model->deleteTodo($id);
        }
        header('Location: index.php');
    }

    public function detail()
    {
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'id missing']);
            return;
        }
        $id = (int)$_GET['id'];
        $todo = $this->model->getTodoById($id);
        header('Content-Type: application/json');
        echo json_encode($todo);
    }

    public function reorder()
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!isset($data['order']) || !is_array($data['order'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid payload']);
            return;
        }
        $order = $data['order'];
        $map = [];
        $pos = 1;
        foreach ($order as $id) {
            $map[(int)$id] = $pos++;
        }
        $ok = $this->model->reorderPositions($map);
        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
    }
}
?>
