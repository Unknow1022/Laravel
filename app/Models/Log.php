<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';
    public $timestamps = false;
    
    protected $fillable = [
        'usuario_id', 'accion', 'tabla', 'item_id', 
        'descripcion', 'fecha'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
