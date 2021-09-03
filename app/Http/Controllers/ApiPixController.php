<?php

namespace App\Http\Controllers;

use App\enum\Enums;
use App\Http\Controllers\banco\BancoBradescoControlle;
use App\Http\Controllers\banco\BancoDoBrasilControlle;
use App\pix\Payload;
use App\Procedures\HelperProcedures;
use App\VwBanco;
use Illuminate\Http\Request;

use function GuzzleHttp\json_decode;

class ApiPixController extends Controller
{
    private $procedures;
    private $banco;
    private $bradesco;
    private $bancoBrasil;

    function __construct()
    {
        $this->procedures = new HelperProcedures;
        $this->banco = new VwBanco;
        $this->bradesco = new BancoBradescoControlle;
        $this->bancoBrasil = new BancoDoBrasilControlle;
    }

    public function sendCobrancaPix(Request $request)
    {
        try {

            $dadosDaCobranca = current($request->all());
            $dadosDaCobranca = (object) $dadosDaCobranca;

            $chavePix = $this->procedures->pr_get_psp_cobranca($dadosDaCobranca->valor);
            $idBanco = $this->banco->getIdBanco($chavePix);

            if ($idBanco->id_banco == Enums::BANCO_DO_BRASIL) {
                $cobrancaBancoBrasil = $this->bancoBrasil->gerarCobranca($dadosDaCobranca, $chavePix);
                return self::payload($cobrancaBancoBrasil->rescURL,  $cobrancaBancoBrasil);
            }

            $cobrancaBradesco = $this->bradesco->gerarCobranca($dadosDaCobranca, $chavePix);
            return self::payload($cobrancaBradesco->rescURL,  $cobrancaBradesco);
        } catch (\Throwable $th) {
            return response()->json(['data' => ['ERRO' => $th, 'mensagem' => 'ocorreu um erro na geração do qr code']]);
        }
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
}
