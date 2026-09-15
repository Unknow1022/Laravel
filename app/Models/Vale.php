<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vale extends Model
{
    protected $table = 'vales';
    public $timestamps = false;
    
    protected $fillable = [
        'codigo_vale', 'trabajador_id', 'usuario_id', 
        'fecha_creacion', 'fecha_limite', 'estado', 'notificado_retraso'
    ];

    public function trabajador() {
        return $this->belongsTo(Trabajador::class)->withTrashed();
    }

    public function usuario() {
        return $this->belongsTo(Usuario::class);
    }

    public function detalles() {
        return $this->hasMany(ValeDetalle::class);
    }
}
