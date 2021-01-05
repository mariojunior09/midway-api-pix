<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApiPixModel extends Model
{
    protected $connection = 'oracle';

    public static function getBanco($chavePix = null)
    {
        return $query = DB::table('TB_TOKEN')
            ->get();
    }


}
