<?php

namespace App\Http\Controllers\banco;

use App\helpers\HelperPix;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HelperBradesco;
use App\PixModel;
use App\Procedures\HelperProcedures;
use Illuminate\Http\Request;

class BancoBradescoControlle extends Controller
{
    private $expiracao;
    private $bradesco;
    private $helperPix;
    function __construct()
    {
        $this->expiracao = new PixModel();
        $this->bradesco = new HelperBradesco();
        $this->helperPix = new HelperPix;
    }
    public  function gerarCobranca($dadosDaCobranca, $chavePix)
    {

        $dadosEnviados = [
            'calendario' => [
                'expiracao' => $this->expiracao->vw_config()->valor
            ],
            'devedor' => [
                'cpf' => $dadosDaCobranca->cpf,
                'nome' => $dadosDaCobranca->nome
            ],
            'valor' => [
                'original' => $dadosDaCobranca->valor
            ],
            'chave' => $chavePix,
            'solicitacaoPagador' => $dadosDaCobranca->solicitacaoPagador
        ];

        $token = $this->helperPix->verificaToken($chavePix);
        $dadosEnviados = json_encode($dadosEnviados);

        $cobranca = $this->bradesco->criarCobrancaBradesco(
            $dadosEnviados,
            $token,
            $dadosDaCobranca->origem_cobranca,
            $dadosDaCobranca->id_cob_origem
        );

        return $cobranca;
    }

    public  function getCobrancaBradescoByTxId($txId)
    {
        return $this->bradesco->getCobrancaBradescoByTxId($txId);
    }

    public  function getCobByWebHook(Request $request)
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

    public  function putWebHookUrl($urlWebHook)
    {
        return $this->bradesco->putWebHookUrl($urlWebHook);
    }
}
