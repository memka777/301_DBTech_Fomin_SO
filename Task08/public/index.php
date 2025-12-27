<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Подключение к базе данных
$db = getDB();

// Получение списка мастеров, отсортированного по фамилии
$stmt = $db->query("
    SELECT * FROM employees 
    ORDER BY last_name, first_name
");
$employees = $stmt->fetchAll();

// Обработка удаления мастера
if (isPost() && isset($_POST['delete_employee'])) {
    $id = post('id');
    $stmt = $db->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape(SITE_NAME) ?> - Список мастеров</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .actions-column { white-space: nowrap; }
        .btn-group-sm { gap: 5px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4"><?= escape(SITE_NAME) ?></h1>
        <h2 class="mb-3">Список мастеров</h2>
        
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Фамилия</th>
                    <th>Имя</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Дата приема</th>
                    <th>Процент</th>
                    <th>Статус</th>
                    <th class="actions-column">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($employees)): ?>
                <tr>
                    <td colspan="9" class="text-center">Нет данных</td>
                </tr>
                <?php else: ?>
                <?php foreach ($employees as $employee): ?>
                <tr>
                    <td><?= escape($employee['id']) ?></td>
                    <td><?= escape($employee['last_name']) ?></td>
                    <td><?= escape($employee['first_name']) ?></td>
                    <td><?= escape($employee['phone']) ?></td>
                    <td><?= escape($employee['email'] ?? '—') ?></td>
                    <td><?= formatDate($employee['hire_date']) ?></td>
                    <td><?= escape($employee['salary_percent']) ?>%</td>
                    <td>
                        <?php if ($employee['is_active']): ?>
                            <span class="badge bg-success">Работает</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Уволен</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="employee_edit.php?id=<?= $employee['id'] ?>" class="btn btn-warning btn-sm">Редактировать</a>
                            
                            <button type="button" class="btn btn-danger btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteModal<?= $employee['id'] ?>">
                                Удалить
                            </button>
                            
                            <a href="schedule.php?employee_id=<?= $employee['id'] ?>" class="btn btn-primary btn-sm">График</a>
                            
                            <a href="completed_works.php?employee_id=<?= $employee['id'] ?>" class="btn btn-info btn-sm">Работы</a>
                        </div>
                        
                        <!-- Модальное окно подтверждения удаления -->
                        <div class="modal fade" id="deleteModal<?= $employee['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Подтверждение удаления</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        Вы действительно хотите удалить мастера 
                                        <strong><?= escape($employee['first_name'] . ' ' . $employee['last_name']) ?></strong>?
                                    </div>
                                    <div class="modal-footer">
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="id" value="<?= $employee['id'] ?>">
                                            <button type="submit" name="delete_employee" class="btn btn-danger">Удалить</button>
                                        </form>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="mt-3">
            <a href="employee_create.php" class="btn btn-success">Добавить нового мастера</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>