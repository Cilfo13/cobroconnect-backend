<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carga extends Model
{
    use HasFactory;
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    public function artefacto()
    {
        return $this->belongsTo(Artefacto::class);
    }
}