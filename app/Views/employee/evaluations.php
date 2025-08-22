<?php
// Cargar categorías desde json/evaluations.json para mantener una única fuente de verdad
$categorias = [];
try {
    $evalJsonPath = __DIR__ . '/../../../json/evaluations.json';
    if (is_readable($evalJsonPath)) {
        $evaluationsData = json_decode(file_get_contents($evalJsonPath), true);
        if (is_array($evaluationsData)) {
            $categorias = array_keys($evaluationsData); // mantiene el orden del JSON
        }
    }
} catch (\Throwable $e) {
    // Ignorar y usar fallback
}
// Fallback si no se pudo cargar el JSON
if (empty($categorias)) {
    $categorias = ['PUNTUALIDAD','PRESENTACION','ORDEN','COMUNICACION','EQUIPO','CONDUCTA','ACTITUD','PRODUCTIVIDAD','COLABORACION','NORMAS','RESPONSABILIDAD','ATENCION_AL_CLIENTE'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 15px; }
        .container { width: 100%; overflow-x: auto; }
        .back-link { display:inline-block; padding:6px 10px; margin-bottom:10px; background:#6b7280; color:#fff; text-decoration:none; border-radius:4px; }
        .evaluations-table { width: 100%; border-collapse: collapse; }
        .evaluations-table th, .evaluations-table td { border: 1px solid #ddd; padding: 6px; text-align: center; }
        .evaluations-table th { background: #3498db; color: #fff; }
        .category-header { background:#2c3e50; color:#fff; }
        .item-cell { text-align:left; min-width:150px; }
        .no-evaluations { padding: 20px; text-align:center; color:#666; }
        .date-cell { font-weight:bold; white-space:nowrap; }
    </style>
    </head>
<body>
    <div class="container">
        <a href="<?= BASE_URL ?>/dashboard" class="back-link">← Volver al Dashboard</a>
        <h1><?= htmlspecialchars($title) ?></h1>

        <?php if (isset($_SESSION['error'])): ?>
            <p style="color:red;"><?= htmlspecialchars($_SESSION['error']) ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (empty($evaluations)): ?>
            <div class="no-evaluations">No hay evaluaciones disponibles para este empleado.</div>
        <?php else: ?>
            <table class="evaluations-table">
                <thead>
                    <tr>
                        <th rowspan="2">Fecha</th>
                        <?php foreach ($categorias as $cat): ?>
                            <th colspan="2" class="category-header"><?= htmlspecialchars($cat) ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <?php foreach ($categorias as $cat): ?>
                            <th>Item</th>
                            <th>Check</th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($evaluations as $evaluation): ?>
                        <tr>
                            <td class="date-cell"><?= htmlspecialchars(date('d/m/Y', strtotime($evaluation['date']))) ?></td>
                            <?php foreach ($categorias as $cat): 
                                $data = $evaluation['data'][$cat] ?? null;
                                $item = $data['item'] ?? '';
                                $checked = isset($data['checked']) ? (bool)$data['checked'] : false;
                            ?>
                                <td class="item-cell"><?= htmlspecialchars($item) ?></td>
                                <td><?= $checked ? '✓' : '✗' ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
