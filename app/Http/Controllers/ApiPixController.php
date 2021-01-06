<?php

namespace App\Http\Controllers;

use App\helpers\HelperCreateToken;
use App\pix\Payload;
use App\Procedures\HelperProcedures;
use Illuminate\Http\Request;

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
            'chave' => '4578b0ea-3bcb-4c5d-ab8f-b7624ad84d69',
            'solicitacaoPagador' => 'Cobranca dos servicos prestados.'
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
        //dd($token);
        if ($token['id_retorno'] == '99') {

            $token = HelperBradescoController::getAccessToken();
            dd($token->access_token);
            HelperProcedures::updateToken($chavePix, $token->access_token, $token->expires_in);
            return $token->access_token;
        } else {
            return $token['p_token'];
        };
    }
    public static function payload($dados)
    {

        $obPayload = (new Payload)->setMerchantName('Liberarde')
            ->setMerchantCity('Fortaleza')
            ->setAmount($dados->valor->original)
            ->setTxId("***")
            ->setUrl($dados->location)
            ->setUniquePayment(true);

        $payLoadQrCode = $obPayload->getPayload();
        return $payLoadQrCode;
    }
}
