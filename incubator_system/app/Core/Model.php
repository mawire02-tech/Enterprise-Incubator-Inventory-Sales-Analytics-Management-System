<?php

namespace App\Core;

/**
 * Base Model
 */
abstract class Model
{
    protected Database $db;
    protected string $table   = '';
    protected string $primary = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ─── Basic CRUD ─────────────────────────────────────────

    public function find(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE {$this->primary} = ? LIMIT 1",
            [$id]
        );
    }

    public function findOrFail(int $id): array
    {
        $row = $this->find($id);
        if (!$row) {
            throw new \RuntimeException("Record not found in {$this->table} (id={$id}).");
        }
        return $row;
    }

    public function findBy(string $column, mixed $value): array|false
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE `{$column}` = ? LIMIT 1",
            [$value]
        );
    }

    public function all(string $orderBy = '', string $direction = 'ASC'): array
    {
        $order = $orderBy ? "ORDER BY `{$orderBy}` {$direction}" : '';
        return $this->db->fetchAll("SELECT * FROM {$this->table} {$order}");
    }

    public function insert(array $data): int
    {
        $columns = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $this->db->execute(
            "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})",
            array_values($data)
        );
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): int
    {
        $set = implode(', ', array_map(fn($c) => "`{$c}` = ?", array_keys($data)));
        return $this->db->execute(
            "UPDATE {$this->table} SET {$set} WHERE {$this->primary} = ?",
            [...array_values($data), $id]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            "DELETE FROM {$this->table} WHERE {$this->primary} = ?",
            [$id]
        );
    }

    public function softDelete(int $id): int
    {
        return $this->db->execute(
            "UPDATE {$this->table} SET deleted_at = NOW() WHERE {$this->primary} = ?",
            [$id]
        );
    }

    public function count(string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        return (int)$this->db->fetchColumn($sql, $params);
    }

    public function exists(string $column, mixed $value, ?int $excludeId = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM {$this->table} WHERE `{$column}` = ?";
        $params = [$value];
        if ($excludeId !== null) {
            $sql    .= " AND {$this->primary} != ?";
            $params[] = $excludeId;
        }
        return (int)$this->db->fetchColumn($sql, $params) > 0;
    }

    // ─── Pagination ─────────────────────────────────────────

    public function paginate(
        string $where  = '',
        array  $params = [],
        int    $page   = 1,
        int    $perPage = PER_PAGE_DEFAULT,
        string $orderBy = '',
        string $direction = 'DESC'
    ): array {
        $page    = max(1, $page);
        $perPage = min(max(1, $perPage), PER_PAGE_MAX);
        $offset  = ($page - 1) * $perPage;

        $whereSql = $where ? "WHERE {$where}" : '';
        $orderSql = $orderBy ? "ORDER BY `{$orderBy}` {$direction}" : '';

        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} {$whereSql}",
            $params
        );

        $rows = $this->db->fetchAll(
            "SELECT * FROM {$this->table} {$whereSql} {$orderSql} LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return [
            'data'     => $rows,
            'total'    => $total,
            'per_page' => $perPage,
            'page'     => $page,
            'pages'    => (int)ceil($total / $perPage),
            'offset'   => $offset,
        ];
    }
}
