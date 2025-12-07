<?php
include '../../db_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sql = "
    SELECT 
        t.id AS task_id,
        t.name AS task_name,
        t.status AS task_status,
        t.description AS task_description,
        t.date AS task_date,
        a.name AS area_name,
        u.login AS employee_login
    FROM task t
    LEFT JOIN user u ON t.employee_id = u.id
    LEFT JOIN area a ON t.area_id = a.id
    ORDER BY t.id DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $TaskTitle = $row['task_name']; 
        $TaskId = $row['task_id']; 
        $TaskDesc = $row['task_description'];
        $TaskArea = $row['area_name']; 
        $TaskStatus = $row['task_status'];
        $TaskEmployee = $row['employee_login'] ?? '-'; 
        $TaskDate = $row['task_date']; 
        
        ?>
        <div class="task">
            <h2 class="task-title"><?php echo $TaskTitle; ?></h2>
            <p class="task-desc"><?php echo $TaskDesc; ?></p>
            <span class="task-id">#<?php echo $TaskId; ?></span>
            <button class="task-details-btn">Подробнее...</button>

            <div class="task-bottom">
                <p class="task-status">Статус: <?php echo $TaskStatus; ?></p>
                <p class="task-employee">Исполнитель: <?php echo $TaskEmployee; ?></p>
            </div>

            <div class="details__overlay">
                <div class="details__pop-up">
                    <span class="task-id">#<?php echo $TaskId; ?></span>
                   <button class="pop-up__close-btn">×</button>
                   <h3 class="pop-up__title">Подробная информация по делу</h3>
                    <div class="pop-up__inner">
                        <p class="pop-up__row"><strong>Название:</strong> <?php echo $TaskTitle; ?></p>
                        <p class="pop-up__row"><strong>Описание:</strong> <?php echo $TaskDesc; ?></p>
                        <p class="pop-up__row"><strong>Направление:</strong> <?php echo $TaskArea; ?></p>
                        <p class="pop-up__row"><strong>Статус:</strong> <?php echo $TaskStatus; ?></p>
                        <p class="pop-up__row"><strong>Исполнитель:</strong> <?php echo $TaskEmployee; ?></p>
                        <p class="pop-up__row"><strong>Дата одобрения:</strong> <?php echo $TaskDate; ?></p>
                   </div>
                   <h3 class="pop-up__title">История изменений</h3>
                   <div class="pop-up__inner">
                        <p class="pop-up__row">Заявка одобрена. Дело открыто.<span class="pop-up__date"><?php echo $TaskDate; ?></span></p>

                        <?php
                            $sqlHistory = "SELECT action, status, date, employee_id FROM task_history WHERE task_id = ? ORDER BY date DESC";
                            $stmtHistory = $conn->prepare($sqlHistory);
                            $stmtHistory->bind_param("i", $TaskId);
                            $stmtHistory->execute();
                            $resultHistory = $stmtHistory->get_result();

                            if ($resultHistory->num_rows > 0) {
                                while ($historyRow = $resultHistory->fetch_assoc()) {
                                    $action = $historyRow['action'];
                                    $status = $historyRow['status'];
                                    $date = $historyRow['date'];
                                    $employeeId = $historyRow['employee_id'];
                                    $employeeLogin = '';

                                    if ($employeeId) {
                                        $empSql = "SELECT login FROM user WHERE id = ?";
                                        $empStmt = $conn->prepare($empSql);
                                        $empStmt->bind_param("i", $employeeId);
                                        $empStmt->execute();
                                        $empResult = $empStmt->get_result();
                                        if ($empResult->num_rows > 0) {
                                            $employee = $empResult->fetch_assoc();
                                            $employeeLogin = htmlspecialchars($employee['login']);
                                        }
                                        $empStmt->close();
                                    }

                                    if ($action == 'status') {
                                        echo '<p class="pop-up__row">Исполнитель ' . htmlspecialchars($employeeLogin) . ' изменил статус дела на "' . htmlspecialchars($status) . '" <span class="pop-up__date">' . htmlspecialchars($date) . '</span></p>';
                                    } elseif ($action == 'employee') {
                                        echo '<p class="pop-up__row">Исполнитель изменен на ' . htmlspecialchars($employeeLogin) . ' <span class="pop-up__date">' . htmlspecialchars($date) . '</span></p>';
                                    }
                                }
                            } 
                            $stmtHistory->close();
                        ?>

                </div>
               </div>
            </div>
        </div>



        <?php
    }
} else {
    echo "<p>Дел нет.</p>";
}

$conn->close();
?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const detailButtons = document.querySelectorAll('.task-details-btn');

    detailButtons.forEach(button => {
        button.addEventListener('click', function () {
            const overlay = this.closest('.task').querySelector('.details__overlay');
            overlay.style.display = 'flex';
            document.body.classList.add('no-scroll');
        });
    });

    document.querySelectorAll('.pop-up__close-btn').forEach(closeButton => {
        closeButton.addEventListener('click', function () {
            closeOverlay(this.closest('.details__overlay'));
        });
    });

    document.querySelectorAll('.details__overlay').forEach(overlay => {
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) {
                closeOverlay(overlay);
            }
        });
    });

    // Обработчик нажатия клавиши Escape
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.details__overlay').forEach(overlay => {
                if (overlay.style.display === 'flex') {
                    closeOverlay(overlay);
                }
            });
        }
    });

    function closeOverlay(overlay) {
        overlay.style.display = 'none'; 
        document.body.classList.remove('no-scroll');
    }
});
</script>
