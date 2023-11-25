<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artefacto extends Model
{
    use HasFactory;
    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'cliente_artefacto')
            ->withPivot('comision')
            ->withTimestamps();
    }
    public function cargas()
    {
        return $this->hasMany(Carga::class);
    }
    public function cobros()
    {
        return $this->hasMany(Cobro::class);
    }
}