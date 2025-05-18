<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoletaDePago extends Model
{
    protected $table = 'boleta_de_pago';
    protected $fillable = [
        'numero_orden_de_pago',
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'cantidad',
        'concepto',
        'precio_unitario',
        'importe',
        'total'
    ];
}
