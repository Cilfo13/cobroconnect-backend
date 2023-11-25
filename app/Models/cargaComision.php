<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cargaComision extends Model
{
    use HasFactory;
    public function cliente()
    {
        return $this->belongsTo(clienteComision::class);
    }
}