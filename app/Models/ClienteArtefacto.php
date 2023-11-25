<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteArtefacto extends Model
{
    use HasFactory;
    public function cargas()
    {
        return $this->hasMany(cargaComision::class);
    }
}