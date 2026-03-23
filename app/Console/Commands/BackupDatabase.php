<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    protected $signature   = 'backup:database';
    protected $description = 'Backup MySQL database ke /var/backups/gotiket/ dan hapus backup > 7 hari';

    public function handle(): int
    {
        $dir = '/var/backups/gotiket';
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }

        $filename = $dir . '/backup_' . date('Ymd_His') . '.sql.gz';

        try {
            $gz = gzopen($filename, 'wb9');
            if (!$gz) {
                throw new \RuntimeException("Tidak bisa membuka file: {$filename}");
            }

            $db       = config('database.connections.mysql.database');
            $dbName   = DB::connection()->getPdo()->quote($db);
            $tables   = DB::select('SHOW TABLES');
            $tableKey = "Tables_in_{$db}";

            gzwrite($gz, "-- GoTiket Database Backup\n");
            gzwrite($gz, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
            gzwrite($gz, "-- Database: {$db}\n\n");
            gzwrite($gz, "SET FOREIGN_KEY_CHECKS=0;\n");
            gzwrite($gz, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

            foreach ($tables as $tableRow) {
                $table = $tableRow->$tableKey;

                // CREATE TABLE
                $create = DB::select("SHOW CREATE TABLE `{$table}`");
                $createSql = $create[0]->{'Create Table'};
                gzwrite($gz, "DROP TABLE IF EXISTS `{$table}`;\n");
                gzwrite($gz, $createSql . ";\n\n");

                // INSERT data — chunked per 500 baris
                $pdo    = DB::connection()->getPdo();
                $offset = 0;
                $chunk  = 500;

                do {
                    $rows = DB::table($table)->offset($offset)->limit($chunk)->get();
                    if ($rows->isEmpty()) break;

                    gzwrite($gz, "INSERT INTO `{$table}` VALUES\n");
                    $lines = [];
                    foreach ($rows as $row) {
                        $vals = array_map(function ($v) use ($pdo) {
                            return $v === null ? 'NULL' : $pdo->quote((string) $v);
                        }, (array) $row);
                        $lines[] = '(' . implode(',', $vals) . ')';
                    }
                    gzwrite($gz, implode(",\n", $lines) . ";\n\n");

                    $offset += $chunk;
                } while ($rows->count() === $chunk);
            }

            gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
            gzclose($gz);

        } catch (\Throwable $e) {
            Log::error('backup:database failed', ['error' => $e->getMessage()]);
            $this->error('Backup gagal: ' . $e->getMessage());
            if (file_exists($filename)) unlink($filename);
            return self::FAILURE;
        }

        // Hapus backup lebih dari 7 hari
        $cutoff = time() - (7 * 86400);
        foreach (glob($dir . '/backup_*.sql.gz') ?: [] as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }

        $size = round(filesize($filename) / 1024, 1);
        $this->info("Backup berhasil: " . basename($filename) . " ({$size} KB)");
        Log::info('backup:database succeeded', ['file' => $filename, 'size_kb' => $size]);

        return self::SUCCESS;
    }
}
