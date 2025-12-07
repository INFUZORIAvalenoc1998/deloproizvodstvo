<?php
$servername = "localhost";
$username = "root"; 
$password = "";    
$dbname = "mydatabase";
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); 

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Ошибка подключения: " . $conn->connect_error);
    }
} catch (Exception $e) {
    // header("Location: /deloproizvodstvo/error.php?code=" . urlencode($e->getCode()) . "&message=" . urlencode($e->getMessage()));
    // exit();
}
?>