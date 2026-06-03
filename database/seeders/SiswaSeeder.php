<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Siswa;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = fake('id_ID'); // faker Indonesia

        $kelasList = [
            'X', 'XI', 'XII'
        ];

        $jurusanList = [
            'IPA I', 'IPA II', 'IPS I', 'IPS II', 'Bahasa I', 'Bahasa II'
        ];

        for ($i = 1; $i <= 20; $i++) {
            Siswa::create([
                // NIS unique, random tapi aman
                'nis'    => 'NIS' . now()->format('Y') . str_pad((string) $i, 4, '0', STR_PAD_LEFT),

                'nama'   => $faker->name(),
                'kelas'  => $faker->randomElement($kelasList),
                'no_hp'  => $faker->numerify('08##########'),
                'alamat' => $faker->address(),
                'nama_ortu' => $faker->name(),
                'no_hp_ortu' => $faker->numerify('08##########'),
            ]);
        }
    }
}
