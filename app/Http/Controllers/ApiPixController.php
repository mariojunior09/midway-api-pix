<?php

namespace App\Http\Controllers;

use App\helpers\HelperCreateToken;
use App\pix\Payload;
use App\Procedures\HelperProcedures;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\Environment\Console;

use function GuzzleHttp\json_decode;

class ApiPixController extends Controller
{
    public static function createCobBradesco(Request $request)
    {
        $dados = $request['data'];
        $origemCobranca = $dados['origem_cobranca'];
        $idCobOrigem = $dados['id_cob_origem'];
        $array = array(
            'calendario' => array(
                'expiracao' => '36000'
            ),
            'devedor' => array(
                'cpf' => $dados['cpf'],
                'nome' => $dados['nome']
            ),
            'valor' => array(
                'original' => $dados['valor']
            ),
            'chave' => 'e570607e-3f4d-489a-bc0f-f885b4a59cc9',
            'solicitacaoPagador' => $dados['solicitacaoPagador']
        );

        $token = self::verifyToken($array['chave']);

        $cobranca = HelperBradescoController::createCobBradesco(json_encode($array), $token, $origemCobranca, $idCobOrigem);

        $dados = json_decode($cobranca['rescURL']);

        return self::payload($dados, $cobranca['dataResProcedure']);
    }

    public static function getCobrancaBradescoByTxId($txId)
    {

        return HelperBradescoController::getCobrancaBradescoByTxId($txId);
    }

    public static function verifyToken($chavePix)
    {
        $token = HelperProcedures::getToken($chavePix);
        if ($token['id_retorno'] == '99') {

            $accessToken = stripslashes(HelperBradescoController::getAccessToken());
            $token =  json_decode($accessToken);

            HelperProcedures::updateToken($chavePix, $token->access_token, $token->expires_in);
            return $token->access_token;
        } else {
            return $token['p_token'];
        };
    }

    public static function payload($dados, $resProcedure)
    {

        try {
            $obPayload = (new Payload)->setMerchantName('Libercard')
                ->setMerchantCity('Fortaleza')
                ->setAmount($dados->valor->original)
                ->setTxId("***")
                ->setUrl($dados->location)
                ->setUniquePayment(true);

            $payLoadQrCode = $obPayload->getPayload();
            return response()->json(['data' => [
                'emv' => $payLoadQrCode,
                'menssage' => $resProcedure
            ]]);
        } catch (\Throwable $th) {
            return response()->json(['data' => ['emv' => $th, 'sucesso' => 'false', 'mensagem' => 'ocorreu um erro na geração do qr code']]);
        }
    }


    public static function getCobByWebHook(Request $request)
    {
        $dados = $request->all();
        Log::info($dados);
        foreach ($dados['pix'] as $pix) {

            $p_id_cobranca = $pix['txid'];
            $p_id_status = '2';
            $p_e2edid = $pix['endToEndId'];
            $p_data_pagamento = date('d/m/Y H:i:s', strtotime($pix['horario']));
            $p_pagador_cpf_cnpj = "";
            $p_pagadpor_nome = "";
            $p_info_pagador = "";
            $p_valor = $pix['valor'];

            HelperProcedures::pr_cobranca_atualiza_wh(
                $p_id_cobranca,
                $p_id_status,
                $p_e2edid,
                $p_data_pagamento,
                $p_pagador_cpf_cnpj,
                $p_pagadpor_nome,
                $p_info_pagador,
                $p_valor
            );
        }
    }

    public static function putWebHookUrl($urlWebHook)
    {
        return HelperBradescoController::putWebHookUrl($urlWebHook);
    }


    public function testeWebhook()
    {
        $urlbase = 'https://www5.libercard.com.br/api/get-cobranca-webhook/e570607e-3f4d-489a-bc0f-f885b4a59cc9';
        $certificate = public_path('/files/certtest2.crt');
        $certificateSslKey = public_path('/files/certtest2.key');
        try {
            //HEADERS
            $headers = [
                'Cache-Control: no-cache',
                'Content-type: application/json',
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
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_SSLCERT        => $certificate,
                CURLOPT_SSLKEY         => $certificateSslKey,
                CURLOPT_HTTPHEADER => $headers

            ));

            $response = curl_exec($curl);
            return $response;
            curl_close($curl);
        } catch (\Exception $e) {
            Log::info($e);
        }
        
    }
}
