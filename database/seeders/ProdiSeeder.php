<?php

namespace Database\Seeders; // PASTIKAN INI BENAR

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
// Tambahkan use App\Models\Prodi; jika Anda akan menggunakannya

class ProdiSeeder extends Seeder // PASTIKAN NAMA CLASS BENAR
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Logika untuk men-seed data prodi di sini
        // Contoh:
        \App\Models\Prodi::create(['kode_prodi' => 'TI', 'nama_prodi' => 'Teknologi Informasi', 'fakultas' => 'Vokasi']);
        \App\Models\Prodi::create(['kode_prodi' => 'TK', 'nama_prodi' => 'Teknologi Komputer', 'fakultas' => 'Vokasi']);
        \App\Models\Prodi::create(['kode_prodi' => 'TRPL', 'nama_prodi' => 'Teknologi Rekayasa Perangkat Lunak', 'fakultas' => 'Vokasi']);
    }
}
