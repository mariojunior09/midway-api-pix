<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VwBanco extends Model
{
    protected $table = "VW_BANCO_CONTA";


    public static function getIdBanco($chavePix)
    {
        
       return DB::table('VW_BANCO_CONTA')
            ->select('id_banco')
            ->where('chave_pix', '=', $chavePix)
            ->first();
    }
}
