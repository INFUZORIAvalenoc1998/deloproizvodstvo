document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.accept-btn').forEach((button) => {
    button.addEventListener('click', function () {
      const taskId = this.getAttribute('data-task-id');
      const row = this.closest('.task.request');
      const employeeSelect = row.querySelector('.employee-select');
      const selectedEmployee = employeeSelect.value;

      const complexitySelect = row.querySelector('.complexity-select');
      let selectedComplexity = complexitySelect.value;

      if (!selectedComplexity) {
        selectedComplexity = 1;
      }

      fetch('AcceptRequest.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${taskId}&employee_select=${encodeURIComponent(
          selectedEmployee
        )}&complexity_select=${selectedComplexity}`,
      })
        .then((response) => response.text())
        .then((data) => {
          console.log('Ответ сервера:', data);

          if (data.includes('успешно')) {
            row.remove();
            alert('Задание одобрено.');
            window.location.href = '../RequestsPage/EmployerRequestsPage.php'; // Укажите нужный URL
          } else {
            alert('Ошибка при одобрении задания: ' + data);
          }
        })
        .catch((error) => {
          console.error('Ошибка при выполнении запроса:', error);
          alert('Ошибка при выполнении запроса. Попробуйте снова.');
        });
    });
  });
});
