<?php
// app/Services/Supabase.php
// Servicio PHP para interactuar con Supabase (solo lo necesario para crear días y evaluaciones)

namespace App\Services;

class Supabase
{
    private $baseUrl;
    private $apiKey;

    public function __construct()
    {
        // Configura tu URL y API KEY de Supabase aquí
        $this->baseUrl = 'https://fqlmqnyshzknkogbsznl.supabase.co';
        $this->apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZxbG1xbnlzaHprbmtvZ2Jzem5sIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc0Nzc1NTMxOCwiZXhwIjoyMDYzMzMxMzE4fQ.IEbeR0lo1mTAz5JVIS7e-jjFPeQ9Bn7kTEumw-euz_8';
    
    }

    /**
     * Realiza una petición HTTP a la API de Supabase
     * 
     * @param string $method Método HTTP (GET, POST, PATCH, DELETE, etc.)
     * @param string $endpoint Endpoint de la API (sin la URL base)
     * @param mixed $data Datos a enviar en el cuerpo de la petición
     * @return object Objeto con los datos de respuesta y metadatos
     */
    private function request($method, $endpoint, $data = null)
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
        
        // Configuración común de headers
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $this->apiKey,
            'Authorization: Bearer ' . $this->apiKey,
            'Prefer: return=representation',
            'Accept: application/json',
        ];
        
        // Configuración específica para PATCH
        if ($method === 'PATCH') {
            $headers = array_filter($headers, function($header) {
                return strpos($header, 'Prefer:') === false;
            });
            $headers[] = 'Prefer: return=minimal';
        }
        
        $opts = [
            'http' => [
                'method' => strtoupper($method),
                'header' => implode("\r\n", $headers),
                'ignore_errors' => true,
                'timeout' => 30 // Añadir timeout de 30 segundos
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];
        
        // Solo agregar contenido si hay datos y no es una petición GET o HEAD
        if ($data !== null && $method !== 'GET' && $method !== 'HEAD') {
            // Asegurarse de que los booleanos se conviertan correctamente
            $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $opts['http']['content'] = $jsonData;
            
            // Asegurarse de que el Content-Length esté configurado correctamente
            $opts['http']['header'] = implode("\r\n", $headers) . "\r\nContent-Length: " . strlen($jsonData);
        }
        
        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);
        
        // Obtener el código de estado HTTP
        $statusLine = $http_response_header[0] ?? '';
        preg_match('{HTTP\/\S*\s(\d{3})}', $statusLine, $match);
        $status = $match[1] ?? '000';
        
        // Crear objeto de respuesta estandarizado
        $response = new class {
            public $data = null;
            public $error = null;
            public $status = 0;
            public $headers = [];
            
            public function getData() {
                return $this->data;
            }
            
            public function hasError() {
                return $this->error !== null || $this->status >= 400;
            }
            
            public function getError() {
                return $this->error;
            }
        };
        
        $response->status = (int)$status;
        $response->headers = $http_response_header;
        
        // Registrar la respuesta (eliminado logging en producción)
        
        // Si hay un error en la petición HTTP
        if ($result === false) {
            $error = error_get_last();
            $errorMessage = $error['message'] ?? 'Error desconocido al realizar la petición';
            
            $response->error = [
                'message' => $errorMessage,
                'code' => 'REQUEST_ERROR',
                'details' => [
                    'url' => $url,
                    'method' => $method,
                    'status' => $status,
                    'headers' => $http_response_header
                ]
            ];
            
            // Logging eliminado
            
            return $response;
        }
        
        // Decodificar la respuesta JSON si existe
        if (!empty($result)) {
            $decoded = json_decode($result, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $response->data = $decoded;
            } else {
                $response->data = $result;
            }
        }
        
        // Si el estado HTTP indica un error
        if ($status >= 400) {
            $errorInfo = [
                'http_status' => $status,
                'url' => $url,
                'method' => $method,
                'request_data' => $data ?? null,
                'response' => $response->data,
                'response_headers' => $http_response_header
            ];
            
            $response->error = [
                'message' => 'Error en la respuesta del servidor',
                'code' => 'HTTP_' . $status,
                'details' => $errorInfo
            ];
            
            // Logging eliminado
            $errorData = json_decode($result, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Logging eliminado
            }
            
            return false;
        }
        
        // Para operaciones PATCH exitosas, Supabase puede devolver un array vacío o un objeto con los datos actualizados
        if ($method === 'PATCH' && $status >= 200 && $status < 300) {
            // Si hay contenido en la respuesta, intentar decodificarlo
            if (!empty($result)) {
                $decoded = json_decode($result, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }
            // Si no hay contenido o no se pudo decodificar, asumir éxito
            return true;
        }
        
        // Para otras respuestas exitosas, devolver el resultado decodificado si es JSON
        if (!empty($result)) {
            $decoded = json_decode($result, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        
        // Si llegamos aquí, hubo un error
        // Logging eliminado
        return false;
    }

    public function createDay($manager_id, $day_date)
    {
        $data = [
            'manager_id' => $manager_id,
            'day_date' => $day_date,
        ];
        return $this->request('POST', 'rest/v1/days', $data);
    }

    public function createEvaluations($day_id, $employees)
    {
        // Ruta al archivo de evaluaciones
        $evaluationsPath = __DIR__ . '/../../json/evaluations.json';
        
        // Verificar si el archivo existe
        if (!file_exists($evaluationsPath)) {
            return false;
        }
        
        // Leer el contenido del archivo
        $evaluationsJson = file_get_contents($evaluationsPath);
        if ($evaluationsJson === false) {
            return false;
        }
        
        // Decodificar el JSON
        $evaluationsData = json_decode($evaluationsJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        
        if (!is_array($evaluationsData) || empty($evaluationsData)) {
            return false;
        }

        $allEvaluations = [];
        
        // Validar que $employees sea un array
        if (!is_array($employees)) {
            return false;
        }
        
        // Para cada empleado
        foreach ($employees as $emp) {
            if (!is_array($emp)) {
                continue;
            }
            
            $employeeId = (int)($emp['id'] ?? 0);
            
            if (empty($employeeId)) {
                continue;
            }
            
            // Para cada categoría de evaluación
            foreach ($evaluationsData as $category => $items) {
                if (!is_array($items) || empty($items)) {
                    continue;
                }
                
                // Tomar la primera opción (PERFECTO) como valor por defecto
                $defaultItem = $items[0] ?? 'PERFECTO';
                
                $allEvaluations[] = [
                    'day_id' => (int)$day_id,
                    'employee_id' => $employeeId,
                    'category' => $category,
                    'checked' => true,
                    'item' => $defaultItem
                ];
            }
        }
        
        if (empty($allEvaluations)) {
            return false;
        }
        
        // Insertar todas las evaluaciones en una sola petición
        $result = $this->request('POST', 'rest/v1/evaluations', $allEvaluations);
        
        return $result !== false;
    }

    public function getDays()
    {
        return $this->request('GET', 'rest/v1/days?select=*');
    }
    
    /**
     * Obtiene los días de un manager específico
     * @param string $manager_id ID del manager
     * @return array|false Array de días o false en caso de error
     */
    public function getDaysByManager($manager_id)
    {
        if (empty($manager_id)) {
            return false;
        }
        
        $endpoint = sprintf('rest/v1/days?select=*&manager_id=eq.%s&order=day_date.desc', urlencode($manager_id));
        $result = $this->request('GET', $endpoint);
        
        if ($result === false) {
            return false;
        }
        
        return $result;
    }

    public function getEvaluationsByDay($day_id)
    {
        if (empty($day_id)) {
            return false;
        }
        
        // Especificar explícitamente las columnas que necesitamos, incluyendo evaluation_id
        $endpoint = 'rest/v1/evaluations?select=evaluation_id,day_id,employee_id,category,checked,item,created_at,updated_at&day_id=eq.' . urlencode((string)$day_id);
        
        
        $result = $this->request('GET', $endpoint);
        
        if ($result === false) {
            return false;
        }
        
        // Decodificar el JSON si es necesario
        if (is_string($result)) {
            $result = json_decode($result, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }
        }
        
        // Asegurarse de que cada evaluación tenga un evaluation_id
        if (is_array($result)) {
            foreach ($result as &$evaluation) {
                if (!isset($evaluation['evaluation_id']) && isset($evaluation['id'])) {
                    $evaluation['evaluation_id'] = $evaluation['id'];
                }
            }
            unset($evaluation); // Romper la referencia
            
            
        }
        
        return is_array($result) ? $result : [];
    }

    /**
     * Obtiene evaluaciones por empleado
     * @param string|int $employee_id
     * @return array Lista de evaluaciones o []
     */
    public function getEvaluationsByEmployee($employee_id)
    {
        if (empty($employee_id)) {
            return [];
        }

        $endpoint = 'rest/v1/evaluations'
            . '?select=day_id,employee_id,category,checked,item,created_at,updated_at'
            . '&employee_id=eq.' . urlencode((string)$employee_id)
            . '&order=created_at.desc';

        
        $result = $this->request('GET', $endpoint);

        if ($result === false) {
            return [];
        }

        // Decodificar si viene como string
        if (is_string($result)) {
            $result = json_decode($result, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [];
            }
        }

        return is_array($result) ? $result : [];
    }
    
    /**
     * Obtiene un día por su ID
     * @param string $day_id ID del día
     * @return array|false Datos del día o false si hay un error
     */
    public function getDayById($day_id)
    {
        if (empty($day_id)) {
            return false;
        }
        
        $endpoint = 'rest/v1/days?select=*&day_id=eq.' . urlencode((string)$day_id);
        $result = $this->request('GET', $endpoint);
        
        if ($result === false) {
            return false;
        }
        
        // Decodificar el JSON si es necesario
        if (is_string($result)) {
            $result = json_decode($result, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }
        }
        
        // Si es un array y tiene elementos, devolver el primero
        if (is_array($result) && !empty($result[0])) {
            return $result[0];
        }
        
        // Si llegamos aquí, no se encontró el día
        
        return false;
    }

    /**
     * Crea una nueva evaluación en Supabase
     * @param array $evaluationData Datos de la evaluación
     * @return int|false ID de la evaluación creada o false en caso de error
     */
    public function createEvaluation($evaluationData)
    {
        $result = $this->request('POST', 'rest/v1/evaluations', $evaluationData);
        
        if ($result && isset($result[0]['id'])) {
            return $result[0]['id'];
        }
        
        
        return false;
    }

    /**
     * Actualiza una evaluación existente en Supabase
     * @param int $evaluationId ID de la evaluación a actualizar
     * @param array $updateData Datos a actualizar
     * @return bool true si la actualización fue exitosa, false en caso contrario
     */
    public function updateEvaluation($evaluationId, $updateData) {
        // Validar que el ID de evaluación no esté vacío
        if (empty($evaluationId)) {
            return false;
        }
        
        // Validar que los datos de actualización no estén vacíos
        if (empty($updateData) || !is_array($updateData)) {
            return false;
        }
        
        // Validar que al menos uno de los campos requeridos esté presente
        if (!isset($updateData['item']) && !isset($updateData['checked'])) {
            return false;
        }
        
        // Preparar los datos para Supabase
        $supabaseData = [
            'updated_at' => date('Y-m-d H:i:s') // Siempre actualizar la marca de tiempo
        ];
        
        // Agregar el ítem si está presente
        if (isset($updateData['item'])) {
            $item = trim($updateData['item']);
            $supabaseData['item'] = empty($item) ? 'Sin especificar' : $item;
        }
        
        // Agregar el estado del checkbox si está presente
        if (isset($updateData['checked'])) {
            $supabaseData['checked'] = (bool)$updateData['checked'];
        }
        
        // Construir el endpoint con el ID de la evaluación
        $endpoint = 'rest/v1/evaluations';
        
        // Agregar el ID como parámetro de consulta
        $endpoint .= '?evaluation_id=eq.' . urlencode($evaluationId);
        
        
        
        // Usar el método request existente que ya maneja la autenticación
        $result = $this->request('PATCH', $endpoint, $supabaseData);
        
        // Si la respuesta es false, hubo un error
        if ($result === false) {
            return false;
        }
        
        // Si llegamos hasta aquí, asumir que la operación fue exitosa
        return true;
    }


}
