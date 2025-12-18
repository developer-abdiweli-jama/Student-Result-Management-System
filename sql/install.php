-- sql/install.php
<?php
require_once '../config/database.php';

function installDatabase() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully<br>";
    } else {
        die("Error creating database: " . $conn->error);
    }
    
    $conn->select_db(DB_NAME);
    
    // Read and execute SQL file
    $sql_file = __DIR__ . '/database.sql';
    if (!file_exists($sql_file)) {
        die("SQL file not found: $sql_file");
    }
    
    $sql_content = file_get_contents($sql_file);
    $queries = array_filter(array_map('trim', explode(';', $sql_content)));
    
    foreach ($queries as $query) {
        if (!empty($query)) {
            if ($conn->query($query) === FALSE) {
                echo "Error executing query: " . $conn->error . "<br>";
                echo "Query: " . substr($query, 0, 100) . "...<br>";
            }
        }
    }
    
    echo "Database installation completed successfully!<br>";
    echo "<a href='../login.php'>Go to Login Page</a>";
    
    $conn->close();
}

// Check if already installed
$check_conn = getDBConnection();
$result = $check_conn->query("SHOW TABLES LIKE 'admins'");
if ($result && $result->num_rows > 0) {
    die("System is already installed. <a href='../login.php'>Go to Login</a>");
}
$check_conn->close();

// Run installation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    installDatabase();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install SRMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Install SRMS</h1>
            <p class="text-gray-600">Student Result Management System</p>
        </div>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-blue-800 mb-2">Before You Begin:</h3>
            <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
                <li>Create a MySQL database</li>
                <li>Update config/database.php with your credentials</li>
                <li>Ensure PHP has MySQLi extension enabled</li>
                <li>Web server should have write access to temp/ and logs/ directories</li>
            </ul>
        </div>
        
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-yellow-800 mb-2">Default Login Credentials:</h3>
            <p class="text-sm text-yellow-700">
                <strong>Admin:</strong> admin / admin123<br>
                <strong>Student:</strong> SRM001 / admin123
            </p>
        </div>
        
        <form method="POST">
            <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition duration-200">
                Install Database
            </button>
        </form>
        
        <div class="mt-4 text-center text-sm text-gray-500">
            <p>After installation, delete the sql/install.php file for security.</p>
        </div>
    </div>
</body>
</html>