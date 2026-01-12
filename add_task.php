<?php
/**
 * add_task.php - SIMPLE VERSION
 * Tidak pakai class Database
 */

require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi field wajib
    if (empty($_POST['title']) || empty($_POST['deadline'])) {
        header('Location: index.php?error=required');
        exit;
    }
    
    // Get form data
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? 'lainnya');
    $deadline = sanitizeInput($_POST['deadline'] ?? '');
    $priority = sanitizeInput($_POST['priority'] ?? 'medium');
    
    // Validate category
    $valid_categories = ['kerja', 'kuliah', 'pribadi', 'belanja', 'lainnya'];
    if (!in_array($category, $valid_categories)) {
        $category = 'lainnya';
    }
    
    // Validate priority
    $valid_priorities = ['low', 'medium', 'high'];
    if (!in_array($priority, $valid_priorities)) {
        $priority = 'medium';
    }
    
    // Insert into database
    $sql = "INSERT INTO tasks (title, description, category, deadline, priority) 
            VALUES ('$title', '$description', '$category', '$deadline', '$priority')";
    
    if (executeQuery($sql)) {
        header('Location: index.php?success=added');
    } else {
        header('Location: index.php?error=database');
    }
    exit;
} else {
    // Jika bukan POST request, redirect ke index
    header('Location: index.php');
    exit;
}
?>