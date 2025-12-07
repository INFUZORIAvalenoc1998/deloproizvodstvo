<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['login']) || !isset($_SESSION['role'])) {
    echo "<script>
    alert('Вам нужно войти в систему.');
    window.location.href = '../../index.php'; 
  </script>";
    exit();
}


$userRole = $_SESSION['role'];

$current_page = basename($_SERVER['PHP_SELF']);

$access_control = [
    'Employer/MainPage/EmployerMainPage.php' => 'employer',
    'Employee/MainPage/EmpMainPage.php' => 'employee',
    'Client/MainPage/ClientMainPage.php' => 'client',
    'Admin/MainPage/AdminMainPage.php' => 'admin',
];

if (array_key_exists($current_page, $access_control)) {
    if ($access_control[$current_page] !== $userRole) {
        echo "<script>
        alert('У вас нет доступа к этой странице.');
        window.location.href = 'index.php'; 
      </script>";
        exit();
    }
}
?>