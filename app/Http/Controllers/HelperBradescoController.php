<?php

namespace App\Http\Controllers;

use App\Procedures\HelperProcedures;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class HelperBradescoController extends Controller
{
    public static function putWebHookUrl($urlWebHook)
    {
        $urlbase = 'https://qrpix-h.bradesco.com.br/v1/spi/cob/';
        $certificate = public_path('/files/mandacaru.crt.pem');
        $certificateSslKey = public_path('/files/ww8_libercard_com_br.key');
        $token = self::getAccessToken();
        $access_token = json_decode($token);


        try {
            //HEADERS
            $headers = [
                'Cache-Control: no-cache',
                'Content-type: application/json',
                'Authorization: Bearer ' . $access_token->access_token
            ];


            //CONFIGURAÇÃO DO CURL
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL =>  $urlbase,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_SSLCERT        => $certificate,
                CURLOPT_SSLKEY         => $certificateSslKey,
                CURLOPT_POSTFIELDS => $$urlWebHook,
                CURLOPT_HTTPHEADER => $headers

            ));

            $response = curl_exec($curl);
            return $response;
            curl_close($curl);
        } catch (\Exception $e) {
            Log::info($e);
        }
    }


    public static function getAccessToken()
    {

        $baseUrl = 'https://qrpix-h.bradesco.com.br/auth/server/oauth/token';
        $certificate = public_path('/files/mandacaru.crt.pem');
        $certificateSslKey = public_path('/files/ww8_libercard_com_br.key');

        //ENDPOINT COMPLETO
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $baseUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSLCERT       => $certificate,
            CURLOPT_SSLKEY        => $certificateSslKey,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: basic ZjQ1YzQyNzgtZDkyOS00YTViLWFlMjYtOGIwM2I5ZWYzYzAyOmE1MzQ3MTRkLWMyZDgtNDNkNS1iOThiLWM3ZTRiNDUyMDkwNg=='
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return  $response;
    }


    public static function createCobBradesco($dadosCobranca, $token, $origemCobranca, $idCobOrigem)
    {
        $urlbase = 'https://qrpix-h.bradesco.com.br/v1/spi/cob/';
        $certificate = public_path('/files/mandacaru.crt.pem');
        $certificateSslKey = public_path('/files/ww8_libercard_com_br.key');
        $txId = md5(date('d/m/Y H:i:s') . rand());

        try {
            //HEADERS
            $headers = [
                'Cache-Control: no-cache',
                'Content-type: application/json',
                'Authorization: Bearer ' . $token
            ];


            //CONFIGURAÇÃO DO CURL
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL =>  $urlbase . $txId,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_SSLCERT        => $certificate,
                CURLOPT_SSLKEY         => $certificateSslKey,
                CURLOPT_POSTFIELDS => $dadosCobranca,
                CURLOPT_HTTPHEADER => $headers

            ));

            $response = curl_exec($curl);
            $dataRes =  self::saveLogs($dadosCobranca, $response, $urlbase . $txId, $txId, $origemCobranca, $idCobOrigem);
            $arrayRes = array(
                'rescURL' => $response,
                'dataResProcedure' => $dataRes
            );
            return $arrayRes;
            curl_close($curl);
        } catch (\Exception $e) {
            Log::info($e);
        }
    }



    public static function getCobrancaBradescoByTxId($txId)
    {
        $urlbase = 'https://qrpix-h.bradesco.com.br/v1/spi/cob/';
        $certificate = public_path('/files/mandacaru.crt.pem');
        $certificateSslKey = public_path('/files/ww8_libercard_com_br.key');
        $token = self::getAccessToken();

        $access_token = json_decode($token);
        //HEADERS
        $headers = [
            'Cache-Control: no-cache',
            'Content-type: application/json',
            'Authorization: Bearer ' . $access_token->access_token
        ];

        //CONFIGURAÇÃO DO CURL
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL =>  $urlbase . $txId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSLCERT        => $certificate,
            CURLOPT_SSLKEY         => $certificateSslKey,
            CURLOPT_HTTPHEADER => $headers
        ));
        $response = curl_exec($curl);
        return $response;
        curl_close($curl);
    }


    public static function saveLogs($p_dados_enviados, $p_dados_recebidos, $p_endpoint, $p_id_cobranc = null, $origemCobranca, $idCobOrigem = null)
    {
        $result = json_decode(stripslashes($p_dados_recebidos));
        if (isset($result->codigoErro)) {
            HelperProcedures::pr_log_insere($p_dados_enviados, $p_dados_recebidos, $p_endpoint);
        } else {
            HelperProcedures::pr_log_insere($p_dados_enviados, $p_dados_recebidos, $p_endpoint);
            $dataRes =  HelperProcedures::pr_cobranca_insere($p_dados_enviados, $p_dados_recebidos, $p_id_cobranc, $origemCobranca, $idCobOrigem);
            return  $dataRes;
        }
    }
}
