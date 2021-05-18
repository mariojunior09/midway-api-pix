<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PixModel extends Model
{
    public function vw_chave_pix()
    {
        return DB::table('VW_CHAVE_PIX')
            ->select('chave_pix')
            ->get();
    }

    public static function vw_banco()
    {
        return DB::table('VW_BANCO')->first();
    }


    public  function vw_config()
    {
        return DB::table('vw_config')
        ->where('id_config','=', '1')
        ->first();
    }
}
