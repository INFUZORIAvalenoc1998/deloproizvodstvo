<?php
  session_start();
  if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    echo "<script>
    alert('У вас нет прав доступа к этой странице.');
    window.location.href = '../../index.php'; 
    </script>";
    exit();
  }

$pageTitle = "Личный кабинет";
include '../../header.php';
include '../../db_connect.php';


$employeeId = $_SESSION['employee_id'];

$stmt = $conn->prepare("SELECT avatar FROM emp_avatars WHERE emp_id = ?");
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$stmt->bind_result($avatarData);
$stmt->fetch();
$stmt->close();
$conn->close();

if ($avatarData) {
    // Кодируем изображение в base64 для отображения
    $avatarBase64 = base64_encode($avatarData);
    $avatarSrc = "data:image/jpeg;base64," . $avatarBase64;
} else {
    // Если аватар не найден, используем изображение по умолчанию
    $avatarSrc = "path/to/default-avatar.jpg"; // Укажите путь к изображению по умолчанию
}
?>




<div class="container">
    <h1 class="title main-title"><?php echo $pageTitle; ?></h1>
    <div class="emp-profile">
       <div class="emp-profile__inner">
        <div class="emp-profile--left">
        <h2>Выберите аватар</h2>
        <form action="SetEmpAvatar.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="empAvatar" id="empAvatar" accept="image/*" required>
            <button type="submit" class="btn">Сохранить</button>
        </form>
        </div>
        <div class="emp-profile--right">
          <h2>Ваш аватар</h2>
          <img src="<?php echo $avatarSrc; ?>" alt="Аватар работника" class="emp-avatar" />
        </div>
        </div>

    </div>
</div>

<?php include '../../footer.php'; ?>