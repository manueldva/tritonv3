<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','lastname','document','status','empresa_id'
    ];


    // Relación con la Empresa (opcional, pero buena práctica)
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
