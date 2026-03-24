<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `obetzera`");
    echo "Database created successfully.\n";
    $pdo->exec("USE `obetzera`");
    echo "Executing SQL file...\n";
    
    // Read and parse SQL - handling large files can be tricky with single exec(), 
    // but try standard PDO since it's only 1.7MB
    $sql = file_get_contents('BANCO-MYSQL.sql');
    
    // Disable foreign key checks for import
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
    $pdo->exec($sql);
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
    
    echo "Imported successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
