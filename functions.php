<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

define('DATA_DIR', __DIR__ . '/data');
define('USERS_FILE', DATA_DIR . '/users.json');
define('NOTES_FILE', DATA_DIR . '/notes.json');

function getUsers() {
    if (!file_exists(USERS_FILE)) return [];
    $json = file_get_contents(USERS_FILE);
    return json_decode($json, true) ?? [];
}

function addUser($username, $password, $name) {
    $users = getUsers();
    
    // Check if username exists
    foreach ($users as $user) {
        if ($user['username'] === $username) return false;
    }
    
    // Generate new ID (simple max + 1)
    $maxId = 0;
    foreach ($users as $user) {
        if ($user['id'] > $maxId) $maxId = $user['id'];
    }
    
    $newUser = [
        'id' => $maxId + 1,
        'username' => $username,
        'password' => $password, // In real app, hash this!
        'name' => $name
    ];
    
    $users[] = $newUser;
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
    return true;
}

function updateUserPassword($id, $newPassword) {
    $users = getUsers();
    $found = false;
    foreach ($users as &$user) {
        if ($user['id'] == $id) {
            $user['password'] = $newPassword;
            $found = true;
            break;
        }
    }
    
    if ($found) {
        file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
        return true;
    }
    return false;
}

function deleteUser($id) {
    // Prevent deleting admin (id 1)
    if ($id == 1) return false;
    
    $users = getUsers();
    $filteredUsers = array_filter($users, function($user) use ($id) {
        return $user['id'] != $id;
    });
    
    if (count($users) === count($filteredUsers)) return false;
    
    $users = array_values($filteredUsers);
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
    return true;
}

function getNotes($userId = null) {
    if (!file_exists(NOTES_FILE)) return [];
    $json = file_get_contents(NOTES_FILE);
    $notes = json_decode($json, true) ?? [];
    
    if ($userId !== null) {
        $notes = array_filter($notes, function($note) use ($userId) {
            return isset($note['user_id']) && $note['user_id'] == $userId;
        });
    }

    // Sort by timestamp desc
    usort($notes, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
    return $notes;
}

function saveNote($userId, $text) {
    $notes = getNotes();
    $newNote = [
        'id' => uniqid(),
        'user_id' => $userId,
        'text' => htmlspecialchars($text),
        'timestamp' => time(),
        'date' => date('Y-m-d H:i:s')
    ];
    
    // Prepend to keep newest first
    array_unshift($notes, $newNote);
    
    file_put_contents(NOTES_FILE, json_encode($notes, JSON_PRETTY_PRINT));
    return $newNote;
}

function updateNote($id, $text, $userId) {
    $notes = getNotes(); // Get all notes to save back
    $found = false;
    foreach ($notes as &$note) {
        if ($note['id'] === $id) {
            // Check ownership
            if ($note['user_id'] != $userId) return false;
            
            $note['text'] = htmlspecialchars($text);
            $note['timestamp'] = time(); // Optional: update timestamp
            $note['date'] = date('Y-m-d H:i:s');
            $found = true;
            break;
        }
    }
    
    if ($found) {
        file_put_contents(NOTES_FILE, json_encode($notes, JSON_PRETTY_PRINT));
        return true;
    }
    return false;
}

function deleteNote($id, $userId) {
    $notes = getNotes(); // Get all notes
    $filteredNotes = array_filter($notes, function($note) use ($id, $userId) {
        // Keep note if ID doesn't match OR (ID matches but User ID doesn't match - unauthorized delete attempt)
        // Actually simpler: Remove if ID matches AND User ID matches
        if ($note['id'] === $id && $note['user_id'] == $userId) {
            return false; // Remove
        }
        return true; // Keep
    });
    
    if (count($notes) === count($filteredNotes)) {
        return false;
    }
    
    // Re-index array
    $notes = array_values($filteredNotes);
    file_put_contents(NOTES_FILE, json_encode($notes, JSON_PRETTY_PRINT));
    return true;
}

function checkLogin($username, $password) {
    $users = getUsers();
    foreach ($users as $user) {
        if ($user['username'] === $username && $user['password'] === $password) {
            return $user;
        }
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
?>
