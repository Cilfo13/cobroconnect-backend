<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    use HasFactory;
    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'zona_user');
    }
}