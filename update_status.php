<?php
/**
 * update_status.php - SIMPLE VERSION
 */

require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (!isset($_POST['task_id']) || !isset($_POST['completed'])) {
        header('Location: index.php');
        exit;
    }
    
    $task_id = intval($_POST['task_id']);
    $completed = $_POST['completed'] === '1' ? 1 : 0;
    
    // Update task status
    $sql = "UPDATE tasks SET completed = $completed WHERE id = $task_id";
    
    executeQuery($sql);
    
    header('Location: index.php?success=updated');
    exit;
} else {
    header('Location: index.php');
    exit;
}
?>