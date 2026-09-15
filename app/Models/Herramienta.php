<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Herramienta extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'herramientas';
    public $timestamps = false;
    
    protected $fillable = [
        'almacen_id', 'categoria_id', 'codigo', 'nombre', 'descripcion', 'estado', 
        'ubicacion', 'almacen', 'seccion', 'tamano', 'uso',
        'stock_total', 'stock_disponible', 'stock_minimo', 'creado_en'
    ];

    /**
     * Auto-asignar creado_en al crear una herramienta nueva.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->creado_en)) {
                $model->creado_en = now();
            }
        });
    }

    protected $casts = [
        'metadata' => 'array',
    ];

    public function modelAlmacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}
