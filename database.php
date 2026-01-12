<?php
/**
 * config/database.php - ULTRA SIMPLE VERSION
 * PASTI BEKERJA UNTUK SEMUA FILE
 */

// Database configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "taskflow_db";

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("❌ Database Connection Failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// ============ HELPER FUNCTIONS ============

/**
 * Execute SQL query
 */
function executeQuery($sql) {
    global $conn;
    return $conn->query($sql);
}

/**
 * Sanitize input
 */
function sanitizeInput($data) {
    global $conn;
    if (empty($data)) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $conn->real_escape_string($data);
}

/**
 * Get all results as array
 */
function fetchAll($sql) {
    $result = executeQuery($sql);
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

/**
 * Get single row
 */
function fetchOne($sql) {
    $result = executeQuery($sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * Get connection (for special cases)
 */
function getConnection() {
    global $conn;
    return $conn;
}

// Test connection (uncomment untuk test)
/*
echo "✅ Database loaded successfully!<br>";
echo "Connected to: " . $db_name;
*/
?>