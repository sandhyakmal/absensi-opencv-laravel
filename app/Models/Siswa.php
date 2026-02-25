<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $fillable = [
        'nis', 'nama', 'kelas', 'no_hp', 'alamat', 'nama_ortu', 'no_hp_ortu', 'foto_path', 'chat_id'
    ];
}
