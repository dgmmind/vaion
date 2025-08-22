<?php require_once __DIR__ . '/../Settings/settings.php'; 

// Cargar categorías e items desde json/evaluations.json para mantener una única fuente de verdad
$evaluationsData = [];
$categorias = [];
try {
    $evalJsonPath = __DIR__ . '/../../json/evaluations.json';
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
    $categorias = [
        'PUNTUALIDAD', 'PRESENTACION', 'ORDEN', 'COMUNICACION', 
        'EQUIPO', 'CONDUCTA', 'ACTITUD', 'PRODUCTIVIDAD', 
        'COLABORACION', 'NORMAS', 'RESPONSABILIDAD', 'ATENCION_AL_CLIENTE'
    ];
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        /* Minimal essential styles for layout and interactions */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
        }
        
        .container {
            width: 100%;
            overflow-x: auto;
        }
        
        .evaluations-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .evaluations-table th, 
        .evaluations-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
        }
        
        .evaluations-table th {
            background: #3498db;
            color: white;
        }
        
        .category-header {
            background: #2c3e50;
        }
        
        .evaluations-table th:first-child {
            /* First column styles */
        }
        
        .evaluation-checkbox {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        
        .evaluation-checkbox input {
            position: absolute;
            opacity: 0;
            height: 0;
            width: 0;
        }
        
        .checkmark {
            position: relative;
            display: inline-block;
            height: 18px;
            width: 18px;
            background: white;
            border: 1px solid #999;
            border-radius: 4px;
        }
        
        .item-cell {
            text-align: left;
            padding: 5px;
            min-width: 150px;
        }
        
        .evaluation-checkbox input:checked ~ .checkmark {
            background: #4CAF50;
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
            left: 6px;
            top: 2px;
            width: 4px;
            height: 8px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        
        .back-link {
            display: inline-block;
            padding: 5px 10px;
            margin-bottom: 10px;
            background: #95a5a6;
            color: white;
            text-decoration: none;
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
                        // Si el valor es 'PERFECTO', marcamos el checkbox como true
                        const isPerfecto = (newValue === 'PERFECTO');
                        formData.append('checked', isPerfecto);
                        formData.append('day_id', '<?= $day_id ?? '' ?>');
                        
                        console.log('Enviando datos al servidor:', {
                            evaluation_id: evaluationId,
                            item: newValue,
                            checked: isPerfecto
                        });

                        console.log(...formData);
                        
                        const response = await fetch('<?= BASE_URL ?>/manager/update_evaluation', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.dataset.previousValue = newValue;
                            const checkbox = this.closest('tr').querySelector(`.evaluation-checkbox-input[data-category="${category}"]`);
                            if (checkbox) {
                                checkbox.disabled = false;
                                // Actualizar el estado del checkbox basado en el valor seleccionado
                                checkbox.checked = isPerfecto;
                                // Actualizar visualmente el checkmark
                                const checkmark = checkbox.nextElementSibling;
                                if (checkmark) {
                                    if (isPerfecto) {
                                        checkmark.style.backgroundColor = '#4CAF50';
                                        checkmark.style.borderColor = '#4CAF50';
                                    } else {
                                        checkmark.style.backgroundColor = '';
                                        checkmark.style.borderColor = '';
                                    }
                                }
                            }
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
