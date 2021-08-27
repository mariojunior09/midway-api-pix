<?php

namespace App\Http\Controllers;

use App\PixModel as pixmodel;
use App\Procedures\HelperProcedures;
use Illuminate\Support\Facades\Log;


class HelperBradescoController extends Controller
{

    public static function putWebHookUrl($urlWebHook)
    {
        $endpt_cria_cob =  pixmodel::vw_banco();
        $urlbase = $endpt_cria_cob->endpt_cria_cob;
        $certificate = public_path("/files/$endpt_cria_cob->cert_pem");
        $certificateSslKey = public_path("/files/$endpt_cria_cob->cert_key");
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

        $endpt_token = pixmodel::vw_banco();
        $baseUrl = $endpt_token->endpt_token;
        $certificate = public_path("/files/$endpt_token->cert_pem");
        $certificateSslKey = public_path("/files/$endpt_token->cert_key");


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
                "Authorization: basic $endpt_token->authorization"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return  $response;
    }


    public static function criarCobrancaBradesco($dadosCobranca, $token, $origemCobranca, $idCobOrigem)
    {
        $endpt_cria_cob =  pixmodel::vw_banco();
        $urlbase = $endpt_cria_cob->endpt_cria_cob;
        $certificate = public_path("/files/$endpt_cria_cob->cert_pem");
        $certificateSslKey = public_path("/files/$endpt_cria_cob->cert_key");
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
                'dataResProcedure' => $dataRes,
                'txId' => $txId
            );
            return $arrayRes;
            curl_close($curl);
        } catch (\Exception $e) {
            Log::info($e);
        }
    }



    public static function getCobrancaBradescoByTxId($txId)
    {
        $endpt_consulta_cob = pixmodel::vw_banco();
        $urlbase = $endpt_consulta_cob->endpt_consulta_cob;
        $certificate = public_path("/files/$endpt_consulta_cob->cert_pem");
        $certificateSslKey = public_path("/files/$endpt_consulta_cob->cert_key");
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
