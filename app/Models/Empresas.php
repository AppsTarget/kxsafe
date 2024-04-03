<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $updated_at
 * @property int    $created_at
 * @property int    $id_matriz
 * @property int    $lixeira
 * @property string $nome_fantasia
 * @property string $cnpj
 * @property string $razao_social
 */
class Empresas extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'empresas';

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
        'nome_fantasia', 'updated_at', 'created_at', 'id_matriz', 'lixeira', 'cnpj', 'razao_social'
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
        'id' => 'int', 'nome_fantasia' => 'string', 'updated_at' => 'timestamp', 'created_at' => 'timestamp', 'id_matriz' => 'int', 'lixeira' => 'int', 'cnpj' => 'string', 'razao_social' => 'string'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'updated_at', 'created_at'
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
