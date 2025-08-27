<?php
require_once "../settings/settings.php";

class Supabase {
    private $baseUrl;
    private $apiKey;

    public function __construct() {
        $this->baseUrl = SUPABASE_URL;
        $this->apiKey  = SUPABASE_API_KEY;
    }

    private function request($method, $table, $params = "", $data = null) {
        $url = $this->baseUrl . "/rest/v1/" . $table . $params;

        $headers = [
            "apikey: {$this->apiKey}",
            "Authorization: Bearer {$this->apiKey}",
            "Content-Type: application/json"
        ];

        if ($method === "POST" || $method === "PATCH") {
            $headers[] = "Prefer: return=representation";
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            "status" => $httpCode,
            "data"   => json_decode($response, true)
        ];
    }

    // SELECT
    public function select($table, $columns = "*", $filter = []) {
        $queryParams = ["select=" . urlencode($columns)];
        $conditions = [];
        
        // Add filter conditions
        foreach ($filter as $key => $value) {
            if ($key === 'order' || $key === 'limit') {
                $queryParams[] = $key . '=' . urlencode($value);
            } else if ($key === 'conditions') {
                // Handle raw conditions
                $conditions[] = $value;
            } else if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $queryParams[] = $k . '=' . urlencode($v);
                }
            } else if ($value === null || (is_string($value) && strtolower($value) === 'null')) {
                $conditions[] = $key . ".is.null";
            } else if (is_string($value) && strpos($value, 'not.') === 0) {
                // Handle NOT conditions
                $notValue = substr($value, 4);
                $conditions[] = $key . ".not.eq." . urlencode($notValue);
            } else {
                $conditions[] = $key . ".eq." . urlencode($value);
            }
        }
        
        // Add conditions to query
        if (!empty($conditions)) {
            $queryParams[] = 'and=(' . implode(',', $conditions) . ')';
        }
        
        return $this->request("GET", $table, '?' . implode('&', $queryParams));
    }

    // INSERT
    public function insert($table, $data) {
        return $this->request("POST", $table, "", $data);
    }

    // UPDATE
    public function update($table, $data, $filter) {
        // Convert filter array to query string
        $queryParams = [];
        foreach ($filter as $key => $value) {
            $queryParams[] = $key . '=eq.' . urlencode($value);
        }
        $queryString = '?' . implode('&', $queryParams);
        
        return $this->request("PATCH", $table, $queryString, $data);
    }

    // DELETE
    public function delete($table, $filter) {
        $queryParams = [];
        foreach ($filter as $key => $value) {
            $queryParams[] = $key . '=eq.' . urlencode($value);
        }
        $queryString = '?' . implode('&', $queryParams);
        
        return $this->request("DELETE", $table, $queryString);
    }
}
