<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DropLegacyTablesCommand extends Command
{
    protected $signature = 'db:drop-legacy-tables
                            {--force : Jalankan tanpa konfirmasi}';

    protected $description = 'Hapus tabel customers & settings (+ kolom customer_id di orders) via migration';

    public function handle(): int
    {
        $migration = '2026_05_19_000001_drop_customers_and_settings_tables';

        $this->info('Migration: ' . $migration);
        $this->line('  - Drop foreign key customer_id di tabel orders');
        $this->line('  - Drop kolom orders.customer_id');
        $this->line('  - Drop tabel customers');
        $this->line('  - Drop tabel settings');
        $this->newLine();

        if (! $this->option('force') && ! $this->confirm('Lanjutkan? Pastikan .env mengarah ke database yang benar.', true)) {
            $this->warn('Dibatalkan.');

            return self::SUCCESS;
        }

        $exit = Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_05_19_000001_drop_customers_and_settings_tables.php',
            '--force' => true,
        ]);

        $this->output->write(Artisan::output());

        if ($exit !== 0) {
            $this->error('Migration gagal. Cek koneksi database di .env');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Selesai. Tabel customers & settings sudah dihapus.');

        return self::SUCCESS;
    }
}
