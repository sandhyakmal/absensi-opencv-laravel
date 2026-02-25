<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi'; // <-- ini yang penting

    protected $fillable = [
        'nis', 'nama', 'kelas', 'percent'
    ];
}
