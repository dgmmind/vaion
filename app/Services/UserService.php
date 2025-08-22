<?php
namespace App\Services;

class UserService
{
    private string $jsonPath;

    public function __construct(?string $jsonPath = null)
    {
        $this->jsonPath = $jsonPath ?? dirname(__DIR__, 2) . '/json/users.json';
    }

    public function authenticate(string $username, string $password): ?array
    {
        $data = $this->load();
        if (!$data) return null;

        foreach ($data as $managerKey => $manager) {
            // Check manager itself
            if ($this->matchUser($manager, $username, $password)) {
                if (($manager['status'] ?? 'inactive') !== 'active') return null;
                return [
                    'id' => $manager['id'] ?? null,
                    'name' => $manager['name'] ?? '',
                    'username' => $manager['username'] ?? '',
                    'role' => $manager['role'] ?? 'admin',
                    'managerKey' => $managerKey,
                ];
            }
            // Check employees
            foreach (($manager['employees'] ?? []) as $emp) {
                if ($this->matchUser($emp, $username, $password)) {
                    if (($emp['status'] ?? 'inactive') !== 'active') return null;
                    return [
                        'id' => $emp['id'] ?? null,
                        'name' => $emp['name'] ?? '',
                        'username' => $emp['username'] ?? '',
                        'role' => $emp['role'] ?? 'user',
                        'managerKey' => $managerKey,
                        'managerName' => $manager['name'] ?? null,
                    ];
                }
            }
        }
        return null;
    }

    private function matchUser(array $u, string $username, string $password): bool
    {
        return isset($u['username'], $u['password'])
            && strcasecmp($u['username'], $username) === 0
            && $u['password'] === $password;
    }

    private function load(): ?array
    {
        if (!is_file($this->jsonPath)) {
            return null;
        }
        $json = file_get_contents($this->jsonPath);
        if ($json === false) return null;
        $data = json_decode($json, true);
        return is_array($data) ? $data : null;
    }
}
