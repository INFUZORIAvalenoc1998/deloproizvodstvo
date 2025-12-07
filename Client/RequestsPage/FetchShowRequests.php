<?php
include '../../db_connect.php';

// Проверяем, что пользователь вошел в систему
if (!isset($_SESSION['login'])) {
    die('Вы должны войти в систему, чтобы просмотреть заявки.');
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Получаем id клиента из сессии
$client_id = $_SESSION['client_id'] ?? null;

// Если client_id не задан, выводим сообщение об ошибке
if ($client_id === null) {
    die('ID клиента не найден.'); 
}

$sql = "
    SELECT 
        r.id AS request_id,
        r.name AS request_name, 
        r.description AS request_description, 
        r.approved AS request_approved
    FROM request r
    WHERE r.client_id = ?
    ORDER BY r.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $TaskId = $row['request_id'];
        $TaskTitle = $row['request_name'];
        $TaskDesc = $row['request_description'];
        $TaskStatus = $row['request_approved'] ? 'Одобрено' : 'Не одобрено';
        ?>
        <div class="task" data-id="<?php echo $TaskId; ?>">
            <h2 class="task-title"><?php echo $TaskTitle; ?></h2>
            <p class="task-desc"><?php echo $TaskDesc; ?></p>
            
            <?php if ($TaskStatus !== 'Одобрено'): ?>
                <button class="cancel-btn delete-btn delete-request-btn">Отменить</button>
            <?php endif; ?>

            <div class="task-bottom task-bottom--request">
                <?php if ($TaskStatus === 'Одобрено'): ?>
                    <p class="task-message">Задание одобрено. Для отслеживания прогресса дела перейдите на страницу <a href="../TasksPage/ClientTasksPage.php">Ваши дела</a>.</p>
                <?php else : ?>
                     <p class="task-message">Задание в рассмотрении.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
} else {
    echo "<p>Заявок нет.</p>";
}


$stmt->close();
$conn->close();
?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.delete-request-btn').forEach(button => {
        button.addEventListener('click', function () {
            const requestElement = this.closest('.task');
            const requestTitle = requestElement.querySelector('.task-title').textContent;
            const requestId = requestElement.getAttribute('data-id');

            if (confirm(`Вы уверены, что хотите отменить заявку: ${requestTitle}?`)) {
                fetch('CancelRequest.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: requestId }) 
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        requestElement.remove();
                        alert('Заявка успешно удалена.');
                    } else {
                        alert('Не удалось удалить заявку.');
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Произошла ошибка при удалении заявки.');
                });
            }
        });
    });
});
</script>
