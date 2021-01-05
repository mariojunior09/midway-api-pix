<?php

namespace App\Procedures;

use Illuminate\Support\Facades\DB;

use function PHPSTORM_META\type;

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

    public static function pr_log_insere(
        $p_dados_enviados,
        $p_dados_recebidos,
        $p_endpoint,
        $p_id_cobranca = null
    ) {
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


    public static function pr_cobranca_insere($p_dados_enviados, $p_dados_recebidos, $p_id_cobranca)
    {


        $dadodos_enviados = json_decode($p_dados_enviados);
        $dados_recebidos =  json_decode($p_dados_recebidos);

        $data_expiracao =  gmdate("H", $dados_recebidos->calendario->expiracao);
        $hora_hoje = date("H:i:s");
        $expiracao = date('d/m/y H:i:s', strtotime("+$data_expiracao hour", strtotime($hora_hoje)));

        $p_data_criacao  =   date('d/m/y H:i:s', strtotime($dados_recebidos->calendario->criacao));
        $p_data_expiracao = $expiracao;
        $p_devedor_nome     =       $dados_recebidos->devedor->nome;
        $p_devedor_cpf_cnpj =       $dados_recebidos->devedor->cpf;
        $p_valor            =       $dados_recebidos->valor->original;
        $p_solicitacao_pag  =        $dadodos_enviados->solicitacaoPagador;
        $p_info_adicional   =       'dados que vem do app';
        $p_location         =        $dados_recebidos->location;
        $p_chave_pix        =       $dados_recebidos->chave;

        $id_retorno = &$p_id_retorno;
        $msg_retorno = &$p_msg_retorno;

        $db = DB::connection()->getPdo();

        $stmt = $db->prepare("begin pk_pix.pr_cobranca_insere(
            :p_id_cobranca,
            :p_data_criacao,
            :p_data_expiracao,  
            :p_devedor_nome,    
            :p_devedor_cpf_cnpj,
            :p_valor,           
            :p_solicitacao_pag, 
            :p_info_adicional,  
            :p_location,        
            :p_chave_pix, 

            :p_id_retorno,
            :p_msg_retorn); end;");

        $stmt->bindParam(':p_id_cobranca', $p_id_cobranca, \PDO::PARAM_STR, 40);
        $stmt->bindParam(':p_data_criacao', $p_data_criacao, \PDO::PARAM_STR, 30);
        $stmt->bindParam(':p_data_expiracao', $p_data_expiracao, \PDO::PARAM_STR, 50);
        $stmt->bindParam(':p_devedor_nome', $p_devedor_nome, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_devedor_cpf_cnpj', $p_devedor_cpf_cnpj, \PDO::PARAM_STR, 15);
        $stmt->bindParam(':p_valor', $p_valor, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_solicitacao_pag', $p_solicitacao_pag, \PDO::PARAM_STR, 200);
        $stmt->bindParam(':p_info_adicional', $p_info_adicional, \PDO::PARAM_STR, 300);
        $stmt->bindParam(':p_location', $p_location, \PDO::PARAM_STR, 500);
        $stmt->bindParam(':p_chave_pix', $p_chave_pix, \PDO::PARAM_STR, 500);

        $stmt->bindParam(':p_id_retorno', $id_retorno, $type = \PDO::PARAM_INPUT_OUTPUT, 10);
        $stmt->bindParam(':p_msg_retorn', $msg_retorno, $type = \PDO::PARAM_INPUT_OUTPUT, 10);
        $stmt->execute();

        return $p_msg_retorno;
    }
}
