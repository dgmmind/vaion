<?php require_once __DIR__ . '/../Settings/settings.php'; 

// Definir las categorías en el orden deseado
$categorias = [
    'PUNTUALIDAD', 'PRESENTACION', 'ORDEN', 'COMUNICACION', 
    'EQUIPO', 'CONDUCTA', 'ACTITUD', 'PRODUCTIVIDAD', 
    'COLABORACION', 'NORMAS', 'RESPONSABILIDAD', 'ATENCION_AL_CLIENTE'
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #f5f7fa;
        }
        .container {
            max-width: 95%;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        .evaluations-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        .evaluations-table th, 
        .evaluations-table td {
            border: 1px solid #e0e0e0;
            padding: 12px 15px;
            text-align: center;
            vertical-align: middle;
        }
        .evaluations-table th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .evaluations-table th:first-child {
            position: sticky;
            left: 0;
            z-index: 11;
            background-color: #2980b9;
        }
        .evaluations-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .evaluations-table tbody tr:hover {
            background-color: #f1f8ff;
        }
        .employee-name {
            font-weight: 600;
            color: #2c3e50;
            white-space: nowrap;
        }
        .status-icon {
            font-size: 16px;
        }
        .status-perfect {
            color: #27ae60;
        }
        .status-good {
            color: #f39c12;
        }
        .status-poor {
            color: #e74c3c;
        }
        /* Base styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #f5f7fa;
        }
        
        .container {
            max-width: 95%;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        
        /* Table styles */
        .evaluations-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
            table-layout: fixed;
        }
        
        .evaluations-table th, 
        .evaluations-table td {
            border: 1px solid #e0e0e0;
            padding: 8px 10px;
            text-align: center;
            vertical-align: middle;
        }
        
        .evaluations-table th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .category-header {
            background-color: #2c3e50 !important;
            font-size: 13px;
            padding: 8px 5px;
        }
        
        .evaluations-table th:first-child {
            position: sticky;
            left: 0;
            z-index: 11;
            background-color: #2980b9;
        }
        
        .item-cell {
            text-align: left !important;
            padding: 8px !important;
            background-color: #f8f9fa;
            border-right: 1px dashed #e0e0e0 !important;
            font-size: 13px;
            word-break: break-word;
        }
        
        .check-cell {
            width: 60px;
            padding: 8px !important;
            background-color: #f8f9fa;
        }
        
        .evaluations-table tbody tr:hover .item-cell,
        .evaluations-table tbody tr:hover .check-cell {
            background-color: #f1f8ff;
        }
        
        .evaluations-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .evaluations-table tbody tr:hover {
            background-color: #f1f8ff;
        }
        
        /* Evaluation items */
        .evaluation-cell {
            padding: 5px !important;
            text-align: center;
            vertical-align: middle !important;
            min-width: 100px;
        }
        
        .evaluation-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 80px;
            padding: 8px 5px;
            border-radius: 4px;
            background-color: #f9f9f9;
            transition: background-color 0.2s;
        }
        
        .evaluation-item:hover {
            background-color: #f0f0f0;
        }
        
        .item-name {
            font-size: 11px;
            color: #555;
            margin-bottom: 8px;
            text-align: center;
            word-break: break-word;
            line-height: 1.2;
            font-weight: 500;
        }
        
        /* Checkbox styles */
        .evaluation-checkbox {
            display: inline-block;
            position: relative;
            padding-left: 25px;
            margin: 0;
            cursor: default;
        }
        
        .evaluation-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }
        
        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .evaluation-checkbox:hover .checkmark {
            background-color: #e9ecef;
        }
        
        .evaluation-checkbox input:checked ~ .checkmark {
            background-color: #28a745;
            border-color: #28a745;
        }
        
        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }
        
        .evaluation-checkbox input:checked ~ .checkmark:after {
            display: block;
        }
        
        .evaluation-checkbox .checkmark:after {
            left: 7px;
            top: 3px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        
        /* Employee name cell */
        .employee-name {
            font-weight: 600;
            color: #2c3e50;
            white-space: nowrap;
            padding: 10px 15px !important;
            background-color: #f8f9fa;
            position: sticky;
            left: 0;
            z-index: 9;
        }
        
        /* Back link */
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 15px;
            background-color: #95a5a6;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .back-link:hover {
            background-color: #7f8c8d;
            text-decoration: none;
        }
        
        .evaluation-cell {
            padding: 5px !important;
            text-align: center;
            vertical-align: middle !important;
        }
        
        .evaluation-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 80px;
            padding: 5px;
            border-radius: 4px;
            background-color: #f9f9f9;
        }
        
        .item-name {
            font-size: 11px;
            color: #555;
            margin-bottom: 8px;
            text-align: center;
            word-break: break-word;
            line-height: 1.2;
        }
        
        .evaluation-checkbox {
            display: inline-block;
            position: relative;
            padding-left: 25px;
            margin: 0;
            cursor: default;
        }
        
        .evaluation-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }
        
        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .evaluation-checkbox:hover .checkmark {
            background-color: #e9ecef;
        }
        
        .evaluation-checkbox input:checked ~ .checkmark {
            background-color: #28a745;
            border-color: #28a745;
        }
        
        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }
        
        .evaluation-checkbox input:checked ~ .checkmark:after {
            display: block;
        }
        
        .evaluation-checkbox .checkmark:after {
            left: 7px;
            top: 3px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        }
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .back-link:hover {
            background-color: #2980b9;
        }
        .status-icon {
            text-align: center;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="<?= BASE_URL ?>/manager/days" class="back-link">← Volver a la lista de días</a>
        <h1><?= htmlspecialchars($title) ?></h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <p style="color: red;"><?= htmlspecialchars($_SESSION['error']) ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (!empty($evaluaciones_por_empleado)): ?>
            <div class="table-responsive">
                <table class="evaluations-table">
                    <thead>
                        <tr>
                            <th rowspan="2">Empleado</th>
                            <?php foreach ($categorias as $categoria): ?>
                                <th colspan="2" class="category-header">
                                    <?= htmlspecialchars($categoria) ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <?php foreach ($categorias as $categoria): ?>
                                <th>Item</th>
                                <th>Check</th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($evaluaciones_por_empleado as $empleado_id => $evaluaciones): ?>
                            <tr>
                                <td class="employee-name">
                                    Empleado #<?= htmlspecialchars($empleado_id) ?>
                                </td>
                                <?php 
                                // Crear un array asociativo de evaluaciones por categoría
                                $evaluaciones_por_categoria = [];
                                foreach ($evaluaciones as $eval) {
                                    if (isset($eval['category'])) {
                                        $evaluaciones_por_categoria[$eval['category']] = $eval;
                                    }
                                }
                                
                                // Mostrar cada categoría en orden
                                foreach ($categorias as $categoria): 
                                    $evaluacion = $evaluaciones_por_categoria[$categoria] ?? null;
                                    $evaluacion_valor = $evaluacion['checked'] ?? false;
                                    $item_nombre = $evaluacion['item'] ?? $categoria; // Usar item_name si está disponible, si no, usar el nombre de la categoría
                                ?>
                                    <td class="item-cell">
                                        <select class="form-control item-select" 
                                                data-evaluation-id="<?= $evaluacion['evaluation_id'] ?? '' ?>"
                                                data-employee-id="<?= $empleado_id ?>"
                                                data-category="<?= htmlspecialchars($categoria) ?>"
                                                style="width: 100%; font-size: 13px; padding: 4px;">
                                            <?php 
                                            // Get items for this category from evaluations.json
                                            $items = $evaluationsData[$categoria] ?? [];
                                            foreach ($items as $item): 
                                                $selected = ($item === $item_nombre) ? 'selected' : '';
                                            ?>
                                                <option value="<?= htmlspecialchars($item) ?>" <?= $selected ?>><?= htmlspecialchars($item) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="check-cell">
                                        <label class="evaluation-checkbox">
                                            <input type="checkbox" 
                                                   class="evaluation-checkbox-input" 
                                                   data-evaluation-id="<?= $evaluacion['evaluation_id'] ?? '' ?>"
                                                   data-employee-id="<?= $empleado_id ?>"
                                                   data-category="<?= htmlspecialchars($categoria) ?>"
                                                   <?= $evaluacion_valor ? 'checked' : '' ?> 
                                                   <?= ($evaluacion_valor === null) ? 'disabled' : '' ?>>
                                            <span class="checkmark"></span>
                                        </label>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No hay evaluaciones para mostrar para este día.</p>
        <?php endif; ?>
        
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Handle item selection change
            document.querySelectorAll('.item-select').forEach(select => {
                // Store initial value
                select.dataset.previousValue = select.value;
                
                select.addEventListener('change', async function() {
                    const evaluationId = this.dataset.evaluationId;
                    const employeeId = this.dataset.employeeId;
                    const category = this.dataset.category;
                    const newValue = this.value;
                    const checked = this.checked;
                    
                    this.disabled = true;
                    this.classList.add('updating');
                    
                    try {
                        const formData = new FormData();
                        formData.append('evaluation_id', evaluationId);
                        formData.append('employee_id', employeeId);
                        formData.append('category', category);
                        formData.append('field', 'item');
                        formData.append('value', newValue);
                        formData.append('checked', checked); // Añadimos el checkbox como false
                        formData.append('day_id', '<?= $day_id ?? '' ?>');
                        
                        console.log('Enviando datos al servidor:', {
                            evaluation_id: evaluationId,
                            item: newValue,
                            checked: false
                        });
                        
                        const response = await fetch('<?= BASE_URL ?>/manager/update_evaluation', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.dataset.previousValue = newValue;
                            const checkbox = this.closest('tr').querySelector(`.evaluation-checkbox-input[data-category="${category}"]`);
                            if (checkbox) checkbox.disabled = false;
                        } else {
                            alert('Error: ' + (data.message || 'Error desconocido'));
                            this.value = this.dataset.previousValue;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error al conectar con el servidor');
                        this.value = this.dataset.previousValue;
                    } finally {
                        this.disabled = false;
                        this.classList.remove('updating');
                    }
                });
            });
            
            // Handle checkbox change
            document.querySelectorAll('.evaluation-checkbox-input').forEach(checkbox => {
                checkbox.addEventListener('change', async function() {
                    const evaluationId = this.dataset.evaluationId;
                    const employeeId = this.dataset.employeeId;
                    const category = this.dataset.category;
                    const isChecked = this.checked;
                    const checkmark = this.nextElementSibling;
                    
                    this.disabled = true;
                    if (checkmark) checkmark.classList.add('updating');
                    
                    try {
                        const formData = new FormData();
                        formData.append('evaluation_id', evaluationId);
                        formData.append('employee_id', employeeId);
                        formData.append('category', category);
                        formData.append('field', 'checked');
                        // Enviar como booleano en lugar de string
                        formData.append('value', isChecked ? 'true' : 'false');
                        formData.append('day_id', '<?= $day_id ?? '' ?>');
                        
                        console.log('Enviando actualización de checkbox:', {
                            evaluationId,
                            field: 'checked',
                            value: isChecked,
                            day_id: '<?= $day_id ?? '' ?>'
                        });
                        
                        const response = await fetch('<?= BASE_URL ?>/manager/update_evaluation', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (!data.success) {
                            alert('Error: ' + (data.message || 'Error desconocido'));
                            this.checked = !isChecked;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error al conectar con el servidor');
                        this.checked = !isChecked;
                    } finally {
                        this.disabled = false;
                        if (checkmark) checkmark.classList.remove('updating');
                    }
                });
            });
        });
        </script>
        
        <style>
        .updating {
            opacity: 0.7;
            position: relative;
        }
        .updating:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.7) url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%23333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg>') no-repeat center;
            background-size: 16px 16px;
        }
        .item-select {
            min-width: 150px;
        }
        </style>
        
        <div style="margin-top: 30px;">
            <a href="<?= BASE_URL ?>/manager/days" class="back-link">← Volver a la lista de días</a>
        </div>
    </div>
</body>
</html>
