<?php

namespace App\Procedures;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HelperProcedures
{
    public static function getToken($p_chave_pix)
    {

        $p_token = &$p_token;
        $id_retorno = &$p_id_retorno;
        $msg_retorno = &$p_msg_retorno;

        $db = DB::connection()->getPdo();
        $stmt = $db->prepare("begin pk_pix.pr_token_consulta(
            :p_chave_pix,
            :p_token,
            :p_id_retorno,
            :p_msg_retorn); end;");
        $stmt->bindParam(':p_chave_pix', $p_chave_pix, \PDO::PARAM_STR, 500);

        $stmt->bindParam(':p_token', $p_token, $type = \PDO::PARAM_INPUT_OUTPUT, 5000);
        $stmt->bindParam(':p_id_retorno', $id_retorno, $type = \PDO::PARAM_INPUT_OUTPUT, 500);
        $stmt->bindParam(':p_msg_retorn', $msg_retorno, $type = \PDO::PARAM_INPUT_OUTPUT, 500);
        $stmt->execute();
        $array = array(
            "p_token" => $p_token,
            "id_retorno" => $id_retorno,
            "msg_retorno" => $msg_retorno
        );
        return $array;
    }

    public static function updateToken($p_chave_pix, $p_token, $t)
    {

        $p_seg_expira = '3600';
        $id_retorno = &$p_id_retorno;
        $msg_retorno = &$p_msg_retorno;
        $db = DB::connection()->getPdo();


        $stmt = $db->prepare("begin pk_pix.pr_token_atualiza(
            :p_chave_pix,
            :p_token,
            :p_seg_expira,
            :p_id_retorno,
            :p_msg_retorn); end;");

        $stmt->bindParam(':p_chave_pix', $p_chave_pix, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_token', $p_token, \PDO::PARAM_STR, 10000);
        $stmt->bindParam(':p_seg_expira', $p_seg_expira, \PDO::PARAM_STR, 500);

        $stmt->bindParam(':p_id_retorno', $id_retorno, $type = \PDO::PARAM_INPUT_OUTPUT, 500);
        $stmt->bindParam(':p_msg_retorn', $msg_retorno, $type = \PDO::PARAM_INPUT_OUTPUT, 500);
        $stmt->execute();
        return $p_msg_retorno;
    }

    public static function pr_cobranca_insere(
        $p_dados_enviados,
        $p_dados_recebidos,
        $p_tx_id,
        $origemCobranca,
        $idCobOrigem,
        $emv = null
    ) {

        $dadodos_enviados = json_decode($p_dados_enviados);


        $dados_recebidos =  json_decode($p_dados_recebidos);
        $data_expiracao =  gmdate("H", $dados_recebidos->calendario->expiracao);
        $hora_hoje = date("H:i:s");
        $expiracao = date('d/m/Y H:i:s', strtotime("+$data_expiracao hour", strtotime($hora_hoje)));
        $p_data_criacao  =   date('d/m/Y H:i:s', strtotime($dados_recebidos->calendario->criacao));
        $p_data_expiracao = $expiracao;
        $p_devedor_nome     =       $dados_recebidos->devedor->nome;
        $p_devedor_cpf_cnpj =       $dados_recebidos->devedor->cpf;
        $p_valor            =       $dados_recebidos->valor->original;
        $p_solicitacao_pag  =        $dadodos_enviados->solicitacaoPagador;
        $p_info_adicional   =       'dados app';
        $p_location         =        $dados_recebidos->location;
        $p_chave_pix        =       $dados_recebidos->chave;
        $p_id_origem        =       $origemCobranca;
        $p_id_trans_origem  =       $idCobOrigem;

        $id_retorno     = &$p_id_retorno;
        $msg_retorno    = &$p_msg_retorno;
        $id_cobranca    = &$p_id_cobranca;
        $id_trans_rede  =  &$p_id_trans_rede;

        $db = DB::connection()->getPdo();
        $stmt = $db->prepare("begin pk_pix.pr_cobranca_insere(
            :p_tx_id,
            :p_data_criacao,
            :p_data_expiracao,  
            :p_devedor_nome,    
            :p_devedor_cpf_cnpj,
            :p_valor,           
            :p_solicitacao_pag, 
            :p_info_adicional,  
            :p_location,        
            :p_chave_pix, 
            :p_id_origem,
            :p_id_trans_origem,
            :p_emv,
            :p_id_trans_rede,
            :p_id_cobranca,
            :p_id_retorno,
            :p_msg_retorno); end;");

        $stmt->bindParam(':p_tx_id', $p_tx_id, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_data_criacao', $p_data_criacao, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_data_expiracao', $p_data_expiracao, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_devedor_nome', $p_devedor_nome, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_devedor_cpf_cnpj', $p_devedor_cpf_cnpj, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_valor', $p_valor, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_solicitacao_pag', $p_solicitacao_pag, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_info_adicional', $p_info_adicional, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_location', $p_location, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_chave_pix', $p_chave_pix, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_id_origem', $p_id_origem, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_id_trans_origem', $p_id_trans_origem, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_emv', $p_id_trans_origem, \PDO::PARAM_STR, 500);

        $stmt->bindParam(':p_id_trans_rede', $id_trans_rede, $type = \PDO::PARAM_INPUT_OUTPUT, 500);
        $stmt->bindParam(':p_id_cobranca', $id_cobranca, $type = \PDO::PARAM_INPUT_OUTPUT, 500);
        $stmt->bindParam(':p_id_retorno', $id_retorno, $type = \PDO::PARAM_INPUT_OUTPUT, 500);
        $stmt->bindParam(':p_msg_retorno', $emv, $type = \PDO::PARAM_INPUT_OUTPUT, 500);



        $stmt->execute();
        if ($id_retorno == 99) {
            Log::info($p_msg_retorno);
        }
        $arrayRes = array(
            'p_msg_retorno' => $p_msg_retorno,
            'id_retorno'   =>  $id_retorno,
            'id_cobranca' =>  $id_cobranca,
            'id_trans_rede'  => $id_trans_rede
        );
        return  $arrayRes;
    }

    public static function pr_cobranca_atualiza_wh(
        $p_id_cobranca,
        $p_id_status,
        $p_e2edid,
        $p_data_pagamento,
        $p_pagador_cpf_cnpj,
        $p_pagadpor_nome,
        $p_info_pagador,
        $p_valor
    ) {

        $id_retorno = &$p_id_retorno;
        $msg_retorno = &$p_msg_retorno;
        $db = DB::connection()->getPdo();

        $stmt = $db->prepare("begin pk_pix.pr_cobranca_atualiza_wh(
            :p_id_cobranca,
            :p_id_status,
            :p_e2edid,
            :p_data_pagamento,
            :p_pagador_cpf_cnpj,
            :p_pagadpor_nome,
            :p_info_pagador,
            :p_valor,
            :p_id_retorno,
            :p_msg_retorn); end;");

        $stmt->bindParam(':p_id_cobranca', $p_id_cobranca, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_id_status', $p_id_status, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_e2edid', $p_e2edid, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_data_pagamento', $p_data_pagamento, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_pagador_cpf_cnpj', $p_pagador_cpf_cnpj, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_pagadpor_nome', $p_pagadpor_nome, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_info_pagador', $p_info_pagador, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_valor', $p_valor, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_id_retorno', $id_retorno, $type = \PDO::PARAM_INPUT_OUTPUT, 500);
        $stmt->bindParam(':p_msg_retorn', $msg_retorno, $type = \PDO::PARAM_INPUT_OUTPUT, 500);
        $stmt->execute();
        return $p_msg_retorno;
    }

    public static function pr_log_insere($p_dados_enviados, $p_dados_recebidos, $p_endpoint, $p_id_cobranca = null)
    {
        $id_retorno = &$p_id_retorno;
        $msg_retorno = &$p_msg_retorno;
        $db = DB::connection()->getPdo();

        $stmt = $db->prepare("begin pk_pix.pr_log_insere(
            :p_dados_enviados,
            :p_dados_recebidos,
            :p_endpoint,
            :p_id_cobranca,
            :p_id_retorno,
            :p_msg_retorn); end;");

        $stmt->bindParam(':p_dados_enviados', $p_dados_enviados, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_dados_recebidos', $p_dados_recebidos, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_endpoint', $p_endpoint, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_id_cobranca', $p_id_cobranca, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_id_retorno', $id_retorno, $type = \PDO::PARAM_INPUT_OUTPUT, 500);
        $stmt->bindParam(':p_msg_retorn', $msg_retorno, $type = \PDO::PARAM_INPUT_OUTPUT, 500);
        $stmt->execute();
        return $p_msg_retorno;
    }


    public static function pr_cobranca_upd_emv($txId, $emv)
    {
        $id_retorno = &$p_id_retorno;
        $msg_retorno = &$p_msg_retorno;

        $db = DB::connection()->getPdo();
        $stmt = $db->prepare("begin pk_pix.pr_cobranca_upd_emv(
            :p_tx_id,
            :p_emv,

            :p_id_retorno,
            :p_msg_retorn); end;");
        $stmt->bindParam(':p_tx_id', $txId, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_emv', $emv, \PDO::PARAM_STR, 500);

        $stmt->bindParam(':p_id_retorno', $id_retorno, $type = \PDO::PARAM_INPUT_OUTPUT, 500);
        $stmt->bindParam(':p_msg_retorn', $msg_retorno, $type = \PDO::PARAM_INPUT_OUTPUT, 500);
        $stmt->execute();
        return $msg_retorno;
    }
}
