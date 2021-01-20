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

        $cobranca = stripslashes(HelperBradescoController::createCobBradesco(json_encode($array), $token));
        $dados = json_decode($cobranca);

        return self::payload($dados);
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

    public static function payload($dados)
    {

        try {
            $obPayload = (new Payload)->setMerchantName('Libercard')
                ->setMerchantCity('Fortaleza')
                ->setAmount($dados->valor->original)
                ->setTxId("***")
                ->setUrl($dados->location)
                ->setUniquePayment(true);

            $payLoadQrCode = $obPayload->getPayload();
            return response()->json(['data' => ['emv' => $payLoadQrCode, 'sucesso' => 'true', 'mensagem' => 'sucesso']]);
        } catch (\Throwable $th) {
            return response()->json(['data' => ['emv' => $th, 'sucesso' => 'false', 'mensagem' => 'ocorreu um erro na geração do qr code']]);
        }
    }


    public static function getCobByWebHook(Request $request)
    {
        $dados = $request->all();
  
        foreach ($dados['pix'] as $pix) {

            $p_id_cobranca = $pix['txid'];
            $p_id_status = '2';
            $p_e2edid = $pix['endToEndId'];
            $p_data_pagamento = date('d/m/Y H:i:s', strtotime($pix['horario'])) ;
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
            $p_valor);
        }
    }

    public static function putWebHookUrl($urlWebHook)
    {
        return HelperBradescoController::putWebHookUrl($urlWebHook);
    }
}
