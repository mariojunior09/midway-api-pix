<?php

namespace App\Http\Controllers;

use App\Procedures\HelperProcedures;

class HelperBancoDoBrasil extends Controller
{
    public static function getAccessToken()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://oauth.hm.bb.com.br/oauth/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials&scope=cob.read%20cob.write%20pix.read%20pix.write',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ZXlKcFpDSTZJakl6WmpZM016RXRaakZtTlMwME9EWXhMVGxpTVRJaUxDSmpiMlJwWjI5UWRXSnNhV05oWkc5eUlqb3dMQ0pqYjJScFoyOVRiMlowZDJGeVpTSTZNVGswTnpnc0luTmxjWFZsYm1OcFlXeEpibk4wWVd4aFkyRnZJam94ZlE6ZXlKcFpDSTZJakpoWVRZeU1UWXRNV1ptTVMwME9UY3lMV0prWTJZdE16ZzBZemxqTWpnd1pqZGxOVEU1SWl3aVkyOWthV2R2VUhWaWJHbGpZV1J2Y2lJNk1Dd2lZMjlrYVdkdlUyOW1kSGRoY21VaU9qRTVORGM0TENKelpYRjFaVzVqYVdGc1NXNXpkR0ZzWVdOaGJ5STZNU3dpYzJWeGRXVnVZMmxoYkVOeVpXUmxibU5wWVd3aU9qRXNJbUZ0WW1sbGJuUmxJam9pYUc5dGIyeHZaMkZqWVc4aUxDSnBZWFFpT2pFMk1qY3hOREV5T1RBeU1EaDk=',
                'Content-Type: application/x-www-form-urlencoded',
                'Cookie: JSESSIONID=1jKrya77VInHp71PmcJnAV7QSDl2AvT--4bibPleFOgbkDYGNqmF!1443802'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function criarCobrancaBancoBrasil(
        $dadosCobranca,
        $token,
        $origemCobranca,
        $idCobOrigem
    ) {

        $txId = md5(date('d/m/Y H:i:s') . rand());;
        $urlbase = "https://api.hm.bb.com.br/pix/v1/cob/$txId?gw-dev-app-key=d27bc77900ffab801365e17d40050956b981a5bc";
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlbase,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $dadosCobranca,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token",
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $dataRes =  self::saveLogs($dadosCobranca, $response, $urlbase, $txId, $origemCobranca, $idCobOrigem);
        $arrayRes = [
            'rescURL' => $response,
            'dataResProcedure' => $dataRes,
            'txId' => $txId
        ];
        $arrayRes = (object) $arrayRes;
        return $arrayRes;

        curl_close($curl);
    }
    public static function saveLogs($p_dados_enviados, $p_dados_recebidos, $p_endpoint, $p_id_cobranc = null, $origemCobranca, $idCobOrigem = null)
    {

        $result = json_decode(stripslashes($p_dados_recebidos));

        if (isset($result->codigoErro)) {
            HelperProcedures::pr_log_insere($p_dados_enviados, $p_dados_recebidos, $p_endpoint);
        } else {
            HelperProcedures::pr_log_insere($p_dados_enviados, $p_dados_recebidos, $p_endpoint);
            $dataRes =  HelperProcedures::pr_cobranca_insere(
                $p_dados_enviados,
                $p_dados_recebidos,
                $p_id_cobranc,
                $origemCobranca,
                $idCobOrigem
            );
            return  $dataRes;
        }
    }
}
