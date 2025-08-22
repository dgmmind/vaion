<?php require_once __DIR__ . '/../Settings/settings.php'; ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Días Registrados</title>
    <style>
        .evaluations {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            display: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .day-item {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 4px;
        }
        .ver-evaluaciones {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 5px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin-left: 15px;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        .ver-evaluaciones:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
<h1>Días Registrados</h1>

<?php if (isset($_SESSION['message'])): ?>
    <p style="color: green;"><?= htmlspecialchars($_SESSION['message']) ?></p>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <p style="color: red;"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="days-list">
    <?php if (!empty($days) && is_array($days)): ?>
        <?php foreach ($days as $day): ?>
            <div class="day-item">
                <h3>
                    Día: <?= htmlspecialchars($day['day_date'] ?? '') ?>
                    <?php 
                    $dayId = isset($day['day_id']) ? (int)$day['day_id'] : 0;
                    if ($dayId > 0): ?>
                        <a href="<?= htmlspecialchars(BASE_URL . '/manager/evaluations?day_id=' . $dayId) ?>" class="ver-evaluaciones">
                            Ver Evaluaciones
                        </a>
                    <?php else: ?>
                        <span class="error">ID de día inválido</span>
                    <?php endif; ?>
                </h3>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No hay días registrados.</p>
    <?php endif; ?>
</div>

<div style="margin-top: 20px;">
    <a href="<?= BASE_URL ?>/manager/createDayForm" style="display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">
        Crear nuevo día
    </a>
</div>


</body>
</html>
