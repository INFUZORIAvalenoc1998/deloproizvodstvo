<?php
  session_start();
  if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>
    alert('У вас нет прав доступа к этой странице.');
    window.location.href = '../../index.php'; 
    </script>";
    exit();
  }

$pageTitle = "Управление ролями";
include '../../auth_check.php';

include '../../header.php';
include '../../db_connect.php';

$query = "SELECT id, login, role FROM user WHERE role != 'admin'";
$result = $conn->query($query);
?>

<div class="container">
    <h1 class="title main-title"><?php echo $pageTitle ?></h1>

    <div class="admin-table admin-table--roles">
        <div class="table-row table-row--roles">
            <span class="row-item">Пользователь</span>
            <span class="row-item">Роль</span>
            <span class="row-item">Изменить роль</span>
        </div>

        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="table-row table-row--roles">';
                echo '<span class="row-item">' . htmlspecialchars($row['login']) . '</span>';
                echo '<span class="row-item">' . htmlspecialchars($row['role']) . '</span>';
                
                echo '<span class="row-item">';
                echo '<form class="update-role-form" method="POST" action="UpdateRole.php">'; 
                echo '<input type="hidden"  name="user_id" value="' . $row['id'] . '">';
                echo '<input type="hidden"  name="current_role" value="' . htmlspecialchars($row['role']) . '">';
                echo '<select class="employee-select" name="role">';

                $roles = ['employee', 'client', 'employer'];
                foreach ($roles as $role) {
                    $selected = ($role == $row['role']) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($role) . '" ' . $selected . '>' . htmlspecialchars($role) . '</option>';
                }

                echo '</select>';
                echo '<button class="logout-btn" type="submit">Сохранить</button>';
                echo '</form>';
                echo '</span>';
                echo '</div>';
            }
        } else {
            echo '<div class="table-row"><span class="row-item">Нет пользователей</span></div>';
        }
        ?>
    </div>
</div>

<?php include '../../footer.php'; ?>