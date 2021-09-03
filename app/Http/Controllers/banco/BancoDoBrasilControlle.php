<?php

namespace App\Http\Controllers\banco;

use App\helpers\HelperPix;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HelperBancoDoBrasil;
use App\PixModel;

class BancoDoBrasilControlle extends Controller
{
    private $expiracao;
    private $bancoBrasil;
    private $helperPix;
    function __construct()
    {
        $this->expiracao = new PixModel();
        $this->bancoBrasil = new HelperBancoDoBrasil();
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

        $cobranca = $this->bancoBrasil->criarCobrancaBancoBrasil(
            $dadosEnviados,
            $token,
            $dadosDaCobranca->origem_cobranca,
            $dadosDaCobranca->id_cob_origem
        );

        return $cobranca;
    }
}
