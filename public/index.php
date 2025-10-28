<?php
// public/index.php
// Front controller sederhana

// Tampilkan error (development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pastikan path ke controller benar
require_once __DIR__ . '/../controllers/TodoController.php';

$controller = new TodoController();

// Ambil action dari query string, default 'index'
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'update':
        $controller->update();
        break;
    case 'delete':
        $controller->delete();
        break;
    case 'detail':
        $controller->detail();
        break;
    case 'reorder':
        $controller->reorder();
        break;
    case 'index':
    default:
        $controller->index();
        break;
}
