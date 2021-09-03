<?php

namespace App\helpers;

use App\enum\Enums;
use App\Http\Controllers\HelperBancoDoBrasil;
use App\Http\Controllers\HelperBancoDoBrasilController;
use App\Http\Controllers\HelperBradesco;
use App\Http\Controllers\HelperBradescoController;
use App\Procedures\HelperProcedures;
use App\VwBanco;

class HelperPix
{
    public static function verificaToken($chavePix)
    {
        $token = HelperProcedures::getToken($chavePix);

        if ($token['id_retorno'] == Enums::ID_ERRO_PROCEDURE) {

            if (VwBanco::getIdBanco($chavePix) == Enums::BANCO_DO_BRASIL) {

                $token =  json_decode(HelperBancoDoBrasil::getAccessToken());
                HelperProcedures::updateToken($chavePix, $token->access_token, $token->expires_in);
                return $token->access_token;
            }

            $token =  json_decode(HelperBradesco::getAccessToken());
            HelperProcedures::updateToken($chavePix, $token->access_token, $token->expires_in);
            return $token->access_token;
        }
        return $token['p_token'];
    }
}
