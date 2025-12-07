<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    echo "<script>
    alert('У вас нет прав доступа к этой странице.');
    window.location.href = '../../index.php'; 
    </script>";
    exit();
}

error_reporting(E_ALL); 
ini_set('display_errors', 1);

$pageTitle = "Предложить дело";
include '../../header.php';

require '../../db_connect.php';

$query = "SELECT id, name FROM area";
$result = mysqli_query($conn, $query);
$areas = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $areas[] = $row;
    }
}
?>

<div class="container">
    <h1 class="title main-title"><?php echo htmlspecialchars($pageTitle); ?></h1>

    <form action="SubmitRequest.php" method="POST" class="form task-form" onsubmit="return validateForm()" enctype="multipart/form-data">
        <input class="input task-input" type="text" id="task_name" name="task_name" maxlength="30" placeholder="Введите название дела" required>
        <textarea class="textarea" id="task_description" name="task_description" placeholder="Введите описание дела" rows="4" required></textarea> 

        <select class="area-select" id="task_area" name="task_area" required>
          <option value="">Выберите направление</option>
          <?php foreach ($areas as $area): ?>
              <option value="<?php echo htmlspecialchars($area['id']); ?>">
                  <?php echo htmlspecialchars($area['name']); ?>
              </option>
          <?php endforeach; ?>
      </select>

      <input class="input--photo"  type="file" name="task-photo" id="task-photo" accept="image/*">


        <button type="submit" class="btn btn-task-submit">Отправить на рассмотрение</button>
    </form>
</div>


<script>
window.addEventListener('DOMContentLoaded', (event) => {
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    if (message) {
        showAlert(decodeURIComponent(message));
        clearURLParams();
    }
});

// function validateForm() {
//     var taskName = document.getElementById('task_name').value.trim();
//     var taskDescription = document.getElementById('task_description').value.trim();
//     var taskArea = document.getElementById('task_area').value;
//
//     if (taskName === "" || taskDescription === "" || taskArea === "") {
//         alert("Ошибка: Название, описание дела и направление не могут быть пустыми.");
//         return false;
//     }
//
//     return true;
// }
</script>

<?php include '../../footer.php'; ?>
