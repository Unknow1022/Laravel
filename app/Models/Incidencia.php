<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    protected $table = 'incidencias';
    public $timestamps = false;
    
    protected $fillable = [
        'herramienta_id', 'usuario_id', 'trabajador_id', 'vale_id', 
        'fecha', 'cantidad_afectada', 'tipo', 'descripcion', 
        'monto_sancion', 'estado_sancion', 'reparado',
        // Nuevos campos del módulo disciplinario
        'tipo_falta', 'gravedad', 'accion_correctiva', 'evidencia', 'estado_disciplinario',
    ];

    public function herramienta() {
        return $this->belongsTo(Herramienta::class);
    }

    public function usuario() {
        return $this->belongsTo(Usuario::class);
    }

    public function trabajador() {
        return $this->belongsTo(Trabajador::class);
    }

    public function vale() {
        return $this->belongsTo(Vale::class);
    }
}
