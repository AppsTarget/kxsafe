<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $id_maquina
 * @property int    $id_produto
 * @property int    $created_at
 * @property int    $updated_at
 * @property string $descr
 */
class GestorEstoque extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'gestor_estoque';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'descr', 'minimo', 'maximo', 'id_maquina', 'id_produto', 'created_at', 'updated_at', 'preco'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int', 'descr' => 'string', 'id_maquina' => 'int', 'id_produto' => 'int', 'created_at' => 'timestamp', 'updated_at' => 'timestamp'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;

    // Scopes...

    // Functions ...

    // Relations ...
}
