<?php

namespace App\Services;

use App\Core\Database;
use App\Helpers\Auth;

/**
 * BackupService
 * Handles database backup creation and restoration.
 */
class BackupService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ─── Create backup ───────────────────────────────────────
    public function createBackup(string $type = 'manual'): array
    {
        $filename  = 'backup_' . DB_NAME . '_' . date('Y-m-d_His') . '.sql';
        $filepath  = BACKUPS_PATH . '/' . $filename;

        // Log start
        $this->db->execute(
            "INSERT INTO backup_logs (backup_type, file_name, file_path, status, triggered_by)
             VALUES (?,?,?,?,?)",
            [$type, $filename, $filepath, 'in_progress', Auth::id()]
        );
        $logId = (int)$this->db->lastInsertId();

        try {
            $sql = $this->dumpDatabase();
            file_put_contents($filepath, $sql);
            $size = filesize($filepath);

            $this->db->execute(
                "UPDATE backup_logs SET status='success', file_size=? WHERE id=?",
                [$size, $logId]
            );

            // Clean old backups
            $this->cleanOldBackups();

            return [
                'success'  => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'size'     => $size,
                'log_id'   => $logId,
            ];
        } catch (\Throwable $e) {
            $this->db->execute(
                "UPDATE backup_logs SET status='failed', notes=? WHERE id=?",
                [$e->getMessage(), $logId]
            );
            throw $e;
        }
    }

    // ─── Full schema + data dump ─────────────────────────────
    private function dumpDatabase(): string
    {
        $sql  = "-- Enterprise Incubator System Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: " . DB_NAME . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        $tables = $this->db->fetchAll("SHOW TABLES");

        foreach ($tables as $row) {
            $table = reset($row);

            // Table structure
            $create = $this->db->fetchOne("SHOW CREATE TABLE `{$table}`");
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $create['Create Table'] . ";\n\n";

            // Table data
            $rows = $this->db->fetchAll("SELECT * FROM `{$table}`");
            if (!empty($rows)) {
                $cols    = array_keys($rows[0]);
                $colList = implode('`, `', $cols);
                $sql    .= "INSERT INTO `{$table}` (`{$colList}`) VALUES\n";

                $values = [];
                foreach ($rows as $row) {
                    $escaped = array_map(function ($v) {
                        if ($v === null) return 'NULL';
                        return "'" . addslashes((string)$v) . "'";
                    }, array_values($row));
                    $values[] = '(' . implode(', ', $escaped) . ')';
                }

                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        return $sql;
    }

    // ─── Clean backups older than retention period ────────────
    private function cleanOldBackups(): void
    {
        $retentionDays = (int)($this->db->fetchColumn(
            "SELECT setting_value FROM system_settings WHERE setting_key = 'backup_retention'"
        ) ?: 30);

        $cutoff = time() - ($retentionDays * 86400);
        $files  = glob(BACKUPS_PATH . '/backup_*.sql');

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }

    // ─── List available backups ──────────────────────────────
    public function listBackups(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM backup_logs ORDER BY created_at DESC LIMIT 50"
        );
    }

    // ─── Get backup log ──────────────────────────────────────
    public function getLog(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM backup_logs WHERE id=?", [$id]) ?: null;
    }
}
