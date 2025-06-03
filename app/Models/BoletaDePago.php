<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoletaDePago extends Model
{
    protected $table = 'boleta_de_pago';
    protected $fillable = [
        'numero_orden_de_pago',
        'ci',
        'status',
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'cantidad',
        'concepto',
        'precio_unitario',
        'importe',
        'total'
    ];
    public function inscriptions()
{
    return $this->hasMany(Inscriptions::class, 'boleta_de_pago_id');
}
}
