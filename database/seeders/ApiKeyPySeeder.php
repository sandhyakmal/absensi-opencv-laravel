<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApiKeyPySeeder extends Seeder
{
   public function run(): void
    {
        $hash = hash('sha256', 'super:secret'); // Ganti dengan kunci asli yang ingin Anda gunakan

        DB::table('api_keys')->updateOrInsert(
            ['name' => 'api-key-py'],
            [
                'key_hash' => $hash,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
