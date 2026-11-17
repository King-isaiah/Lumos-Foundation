<?php
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/../config.php';

class Database {
    protected PDO $db;

    const ERROR_USER_FACING = 'user_facing';
    const ERROR_DEVELOPER   = 'developer';

    public function __construct() {
        $this->db = $this->getConnection();
    }

    protected function getConnection(): PDO {
        $c = Config::database();
        $dsn = "mysql:host={$c['host']};dbname={$c['name']};charset=utf8mb4";
        try {
            return new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ]);
        } catch (PDOException $e) {
            Logger::error('DB connection failed', [
                'message' => $e->getMessage(),
                'host'    => $c['host'],
                'db'      => $c['name'],
            ]);
            throw new Exception('Unable to connect to the database.', 500);
        }
    }

    protected function executeQuery(string $sql, array $params = []): PDOStatement {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt; 
        } catch (PDOException $e) {
            $this->handleDbError($e, $sql, $params);
            throw new Exception('Database error'); // Fallback for PHP compiler
        }
    }

    protected function fetchAll(string $sql, array $params = []): array {
        return $this->executeQuery($sql, $params)->fetchAll();
    }

    protected function fetchOne(string $sql, array $params = []) {
        $row = $this->executeQuery($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    protected function fetchValue(string $sql, array $params = []) {
        $row = $this->executeQuery($sql, $params)->fetch(PDO::FETCH_NUM);
        return $row === false ? null : $row[0];
    }

    protected function insert(string $table, array $data): int {
        $cols = implode(', ', array_keys($data));
        $ph   = implode(', ', array_fill(0, count($data), '?'));
        $this->executeQuery("INSERT INTO {$table} ({$cols}) VALUES ({$ph})", array_values($data));
        return (int)$this->db->lastInsertId();
    }

    protected function update(string $table, array $data, string $where, array $whereParams): int {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $stmt = $this->executeQuery(
            "UPDATE {$table} SET {$set} WHERE {$where}",
            array_merge(array_values($data), $whereParams)
        );
        return $stmt->rowCount();
    }

    protected function delete(string $table, string $where, array $params): int {
        return $this->executeQuery("DELETE FROM {$table} WHERE {$where}", $params)->rowCount();
    }

    protected function recordExists(string $table, array $conditions, $excludeId = null): bool {
        $w = []; $p = [];
        foreach ($conditions as $col => $val) { $w[] = "{$col} = ?"; $p[] = $val; }
        $sql = "SELECT COUNT(*) FROM {$table} WHERE " . implode(' AND ', $w);
        if ($excludeId) { $sql .= " AND id != ?"; $p[] = $excludeId; }
        return (int)$this->fetchValue($sql, $p) > 0;
    }

    protected function beginTransaction(): bool { return $this->db->beginTransaction(); }
    protected function commit(): bool           { return $this->db->commit(); }
    protected function rollBack(): bool         { return $this->db->rollBack(); }

    protected function rateLimit(string $bucketKey, int $maxHits, int $windowSeconds): bool {
        $now = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', time() + $windowSeconds);

        $this->executeQuery("DELETE FROM rate_limits WHERE expires_at < ?", [$now]);

        $row = $this->fetchOne("SELECT count FROM rate_limits WHERE bucket_key = ?", [$bucketKey]);

        if (!$row) {
            $this->insert('rate_limits', [
                'bucket_key' => $bucketKey,
                'count'      => 1,
                'expires_at' => $expires,
            ]);
            return true;
        }

        if ((int)$row['count'] >= $maxHits) return false;

        $this->executeQuery(
            "UPDATE rate_limits SET count = count + 1 WHERE bucket_key = ?",
            [$bucketKey]
        );
        return true;
    }

    private function handleDbError(PDOException $e, string $sql, array $params): void {
        $c = $this->classifyError($e);
        Logger::db('Query failed', [
            'type'    => $c['type'],
            'code'    => $e->getCode(),
            'sqlstate'=> $e->errorInfo[0] ?? null,
            'driver'  => $e->errorInfo[1] ?? null,
            'message' => $e->getMessage(),
            'sql'     => $sql,
            'params'  => $params,
        ]);
        throw new Exception($c['message'], $c['http_code']);
    }

    private function classifyError(PDOException $e): array {
        $code  = (int)($e->errorInfo[1] ?? 0);
        $state = $e->errorInfo[0] ?? '';
        $msg   = $e->getMessage();

        if ($code === 1062 || ($state === '23000' && stripos($msg, 'Duplicate') !== false)) {
            return ['type' => self::ERROR_USER_FACING, 'message' => $this->extractDup($msg), 'http_code' => 409];
        }
        if ($code === 1451 || $code === 1452) {
            return ['type' => self::ERROR_USER_FACING,
                    'message' => $code === 1452 ? 'Referenced record does not exist.' : 'This record is linked to other data.',
                    'http_code' => 409];
        }
        if ($code === 1406 || stripos($msg, 'Data too long') !== false) {
            return ['type' => self::ERROR_USER_FACING, 'message' => 'One of the fields is too long.', 'http_code' => 400];
        }
        if ($code === 1364 || stripos($msg, "doesn't have a default value") !== false) {
            return ['type' => self::ERROR_USER_FACING, 'message' => 'A required field is missing.', 'http_code' => 400];
        }

        return ['type' => self::ERROR_DEVELOPER, 'message' => 'An unexpected database error occurred.', 'http_code' => 500];
    }

    private function extractDup(string $msg): string {
        if (preg_match("/Duplicate entry '([^']*)' for key '([^']*)'/", $msg, $m)) {
            $field = preg_replace(['/^[^\.]*\./', '/_(unique|key|index|uk|idx|pk)$/i'], '', $m[2]);
            $human = strtolower(ucwords(str_replace('_', ' ', $field)));
            return "The {$human} \"{$m[1]}\" already exists.";
        }
        return 'This value already exists.';
    }
}