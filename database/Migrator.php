<?php

require_once __DIR__ . '/../config/database.php';

class Migrator
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = db();
        $this->initMigrationsTable();
    }

    private function initMigrationsTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS riwayat_migrasi (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nama_migrasi VARCHAR(255) NOT NULL UNIQUE,
                dieksekusi_pada DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function up(): void
    {
        $files = glob(__DIR__ . '/migrations/*.php');
        sort($files);

        $executed = $this->pdo->query("SELECT nama_migrasi FROM riwayat_migrasi")->fetchAll(PDO::FETCH_COLUMN);

        $count = 0;
        foreach ($files as $file) {
            $migration = require $file;
            $name = $migration['name'];

            if (in_array($name, $executed, true)) {
                echo "[SKIP] Migrasi '{$name}' sudah dieksekusi sebelumnya.\n";
                continue;
            }

            echo "[MIGRATE] Menjalankan: {$name} ... ";
            $this->pdo->beginTransaction();
            try {
                $migration['up']($this->pdo);
                $stmt = $this->pdo->prepare("INSERT INTO riwayat_migrasi (nama_migrasi, dieksekusi_pada) VALUES (?, NOW())");
                $stmt->execute([$name]);
                if ($this->pdo->inTransaction()) {
                    $this->pdo->commit();
                }
                echo "BERHASIL!\n";
                $count++;
            } catch (Exception $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                echo "GAGAL!\nError: " . $e->getMessage() . "\n";
                exit(1);
            }
        }

        echo "Total migrasi baru yang dijalankan: {$count}\n";
    }

    public function down(int $steps = 1): void
    {
        $executed = $this->pdo->query("SELECT nama_migrasi FROM riwayat_migrasi ORDER BY id DESC LIMIT {$steps}")->fetchAll(PDO::FETCH_COLUMN);

        if (empty($executed)) {
            echo "Tidak ada migrasi yang bisa di-rollback.\n";
            return;
        }

        foreach ($executed as $name) {
            $file = __DIR__ . "/migrations/{$name}.php";
            if (!file_exists($file)) {
                echo "[WARN] File migrasi {$file} tidak ditemukan, skip rollback.\n";
                continue;
            }

            $migration = require $file;
            echo "[ROLLBACK] Membatalkan migrasi: {$name} ... ";
            $this->pdo->beginTransaction();
            try {
                $migration['down']($this->pdo);
                $stmt = $this->pdo->prepare("DELETE FROM riwayat_migrasi WHERE nama_migrasi = ?");
                $stmt->execute([$name]);
                if ($this->pdo->inTransaction()) {
                    $this->pdo->commit();
                }
                echo "BERHASIL DI-ROLLBACK!\n";
            } catch (Exception $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                echo "GAGAL ROLLBACK!\nError: " . $e->getMessage() . "\n";
                exit(1);
            }
        }
    }
}
