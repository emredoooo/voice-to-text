<?php
require_once 'functions.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'test') {
    echo json_encode(['success' => true, 'message' => 'Connection OK']);
    exit;
}

if ($action === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = checkLogin($username, $password);

    if ($user) {
        $_SESSION['user'] = $user;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($action === 'save_note') {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $text = $data['text'] ?? '';

    // Log for debugging
    error_log("Save Note Request: UserID=" . $_SESSION['user']['id'] . ", Text=" . $text);

    if (empty($text)) {
        echo json_encode(['success' => false, 'message' => 'Empty text']);
        exit;
    }

    $note = saveNote($_SESSION['user']['id'], $text);

    if ($note) {
        echo json_encode(['success' => true, 'note' => $note]);
    } else {
        error_log("Failed to save note to file");
        echo json_encode(['success' => false, 'message' => 'Failed to write to file']);
    }
    exit;
}

if ($action === 'update_note') {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? '';
    $text = $data['text'] ?? '';

    if (empty($id) || empty($text)) {
        echo json_encode(['success' => false, 'message' => 'Missing data']);
        exit;
    }

    if (updateNote($id, $text, $_SESSION['user']['id'])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed or unauthorized']);
    }
    exit;
}

if ($action === 'delete_note') {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? '';

    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Missing ID']);
        exit;
    }

    if (deleteNote($id, $_SESSION['user']['id'])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Delete failed or unauthorized']);
    }
    exit;
}

// Admin Actions
if ($action === 'add_user') {
    if (!isLoggedIn() || $_SESSION['user']['id'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (addUser($data['username'], $data['password'], $data['name'])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
    }
    exit;
}

if ($action === 'reset_password') {
    if (!isLoggedIn() || $_SESSION['user']['id'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (updateUserPassword($data['id'], $data['new_password'])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password']);
    }
    exit;
}

if ($action === 'delete_user') {
    if (!isLoggedIn() || $_SESSION['user']['id'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (deleteUser($data['id'])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
    }
    exit;
}
?>