<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SecureCustomerUploads extends Command
{
    protected $signature = 'daiku:secure-customer-uploads {--dry-run : Hanya tampilkan jumlah file}';

    protected $description = 'Memindahkan lampiran pelanggan lama dari disk publik ke penyimpanan privat';

    public function handle(): int
    {
        $moved = 0;
        $missing = 0;

        foreach ($this->paths() as $path) {
            if (Storage::disk('local')->exists($path)) {
                continue;
            }

            if (! Storage::disk('public')->exists($path)) {
                $missing++;

                continue;
            }

            if (! $this->option('dry-run')) {
                Storage::disk('local')->put($path, Storage::disk('public')->get($path));
                Storage::disk('public')->delete($path);
            }

            $moved++;
        }

        $verb = $this->option('dry-run') ? 'perlu dipindahkan' : 'dipindahkan';
        $this->info("{$moved} file {$verb} ke penyimpanan privat.");

        if ($missing > 0) {
            $this->warn("{$missing} referensi file tidak ditemukan dan dilewati.");
        }

        return self::SUCCESS;
    }

    private function paths(): array
    {
        return [];
    }
}
