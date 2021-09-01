<?php

namespace App\Http\Controllers;

use App\pix\Payload;
use App\PixModel;
use App\Procedures\HelperProcedures;
use Illuminate\Http\Request;

use function GuzzleHttp\json_decode;

class ApiPixController extends Controller
{
    public  function criarCobrancaBradesco(Request $request)
    {
        $pixModel  =  new PixModel();
        $bradesco = new HelperBradescoController();

        $chavePix = $pixModel->vw_chave_pix();
        $expiracao = $pixModel->vw_config();
        $dados = $request['data'];
        $origemCobranca = $dados['origem_cobranca'];
        $idCobOrigem = $dados['id_cob_origem'];

        $dadosEnviados = [
            'calendario' => [
                'expiracao' => $expiracao->valor
            ],
            'devedor' => [
                'cpf' => $dados['cpf'],
                'nome' => $dados['nome']
            ],
            'valor' => [
                'original' => $dados['valor']
            ],
            'chave' => $chavePix->chave_pix,
            'solicitacaoPagador' => $dados['solicitacaoPagador']
        ];

        $token = self::verificaToken($chavePix->chave_pix);
        $dadosEnviados = json_encode($dadosEnviados);
        $cobranca = $bradesco->criarCobrancaBradesco(
            $dadosEnviados,
            $token,
            $origemCobranca,
            $idCobOrigem
        );

        return self::payload($cobranca->rescURL, $cobranca);
    }

    public static function getCobrancaBradescoByTxId($txId)
    {
        $bradesco = new HelperBradescoController();
        return $bradesco->getCobrancaBradescoByTxId($txId);
    }

    public static function verificaToken($chavePix)
    {
        $pocedures = new HelperProcedures();
        $bradesco = new HelperBradescoController();

        $token = $pocedures->getToken($chavePix);
        if ($token['id_retorno'] == '99') {
            $accessToken = stripslashes($bradesco->getAccessToken());
            $token =  json_decode($accessToken);
            $pocedures->updateToken($chavePix, $token->access_token, $token->expires_in);
            return $token->access_token;
        }
        return $token['p_token'];
    }

    public static function payload($dados, $resProcedure)
    {


        try {
            $dados = json_decode($dados);
            $obPayload = (new Payload)->setMerchantName('Libercard')
                ->setMerchantCity('Fortaleza')
                ->setAmount($dados->valor->original)
                ->setTxId("***")
                ->setUrl($dados->location)
                ->setUniquePayment(true);

            $payLoadQrCode = $obPayload->getPayload();
            HelperProcedures::pr_cobranca_upd_emv($resProcedure->txId, $payLoadQrCode);
            return response()->json(['data' => [
                'emv' => $payLoadQrCode,
                'menssage' => $resProcedure->dataResProcedure
            ]]);
        } catch (\Throwable $th) {
            dd($th);
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
        $bradesco = new HelperBradescoController();
        return $bradesco->putWebHookUrl($urlWebHook);
    }
}
