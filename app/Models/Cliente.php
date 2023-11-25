<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    public function artefactos()
    {
        return $this->belongsToMany(Artefacto::class, 'cliente_artefacto')
            ->withPivot('comision')
            ->withTimestamps();
    }
    public function zona()
    {
        return $this->belongsTo(Zona::class);
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