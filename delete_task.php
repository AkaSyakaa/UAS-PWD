<?php
/**
 * delete_task.php - SIMPLE VERSION
 */

require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (!isset($_POST['task_id'])) {
        header('Location: index.php');
        exit;
    }
    
    $task_id = intval($_POST['task_id']);
    
    // Delete task
    $sql = "DELETE FROM tasks WHERE id = $task_id";
    
    if (executeQuery($sql)) {
        header('Location: index.php?success=deleted');
    } else {
        header('Location: index.php?error=database');
    }
    exit;
} else {
    header('Location: index.php');
    exit;
}
?>