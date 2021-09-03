<?php

namespace App\Http\Controllers;

use App\Procedures\HelperProcedures;

class HelperBancoDoBrasil extends Controller
{
    public static function getAccessToken()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://oauth.hm.bb.com.br/oauth/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials&scope=cob.read%20cob.write%20pix.read%20pix.write',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic aNWz4sTCsUuo5PyuQDeqeALwQi9r75qp3b6zl64nRRYZ8_vGh-6EuOCsW5VRijNg3veQeEMhBJSVzwsgRrLmiQ.kibkWXQhI0gCerM85SBy0V7VAEKutWxv4oqkrP_2mj7tNww9_gIoirSAaluSoSYjo2Teq3C07IV0zh2ozcgdiOPeTHY6YIkB_syRwFZeyekIoGdRR4CoBLhrTlLyN8vGnL9j5sOd4BhBLh6GK0JR7rK59PkCH4iEGxqnG_qo_mJzm6GBwgWqi-qb_bjlr58wpcIZwDUB6ATrzcdNZNRXLcT5RB5a8kYyH3l40py7Eivq9qcqp7VOP_jqrxCLf42R8UNmOQXAF4g4fOfRZNWrkLmZnMI7Qt1I1yKhuYrQxuwxXWoY_VMXlrKsenqHJKM3UA0khnfyPZg7He9y2UJhJRYU8HEnIGDysxpDDvyCn03SKhO-f_xGPqbqfXPCmYngzE3TeRc9eM7Ck2ARAqKosfQXKfrBtfYak5HULovhMj7r_3-mVNAfqdRtQThyLlIN9efPTxijizuIUR0Um0ha2B4beWh3IBpZlhjeaae6AsOnwCrm_oAZ400Pew8jIUJGPWrh4mSbMVANaCgOg9RPiJbtDwlP7AZyYUXFUg0-0J3i0emDfDqvGR0dhHq03KZQ7zfCXO-LAOzeyCCtob9jLdsBHhsWLUk5MiCD-mSdH7iRoN55c7wYQjSWGPX_o9I6P5y5iMJoIsC5CwvVGYZEOKaT0fZxbMiBh_Pr3w3lZNzgEiCxa9lD3GALjuv1GamzriI16Z7e7_xA73RhMyDeu8jGdSN1DyQ_B2HiH2iV169TLJ0mAoHlj3pnfY-aWBDq4AarXY_bxiikKMGgkLCfw5kV7xT7Ce7ke3ZyN9djIFlIRFe2t-tsSH6Xmez3Zz0xtNc3IW0UOrITLZAA12R8gx8EZTQ_VRXbpPfo7pp-dtiNL2lZjjw8ZK6Y6PUlDi9I0tL0M2k8slZOdcmcZ89k0BnkwEvbMv0WR0XHESJdcenXLfwEbRSXLvIJAao5chr9yJ98S4zXcZaI3aOV19pKskSNOcW9DtQ10alzFtcc9bvtI2nuA3jjuMF0KgiAiqRZs73ilm99LOQ8Ttz8veN0gQ.DPcvIa6bX9c3e5nEu5IbJTFsZ60XO99qw0Hg9lxMOy-BibTs1ODQMLm95wIJ3l5dG27LuT5RiI1g_v5vzAm5Cw',
                'Content-Type: application/x-www-form-urlencoded',
                'Cookie: JSESSIONID=JMims9eC9okhAp8Q0uvRQbB41WWB1P66PXkvLNTlQfe5vTZqbT1a!1443802'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function criarCobrancaBancoBrasil(
        $dadosCobranca,
        $token,
        $origemCobranca,
        $idCobOrigem
    ) {

        $txId = md5(date('d/m/Y H:i:s') . rand());;
        $urlbase = "https://api.hm.bb.com.br/pix/v1/cob/$txId?gw-dev-app-key=d27bc77900ffab801365e17d40050956b981a5bc";
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlbase,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $dadosCobranca,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer RLjCZ7XvTosnmTJmr1_BMroU3qmXnzOmgDNv2G46rPqXAIxu-FsDnQLdc8g-UxEUB083FQUhfysZZHJo3vqyyA.UaWwpRsy3mYDVEjnGvRnjn8QfLexbi53b9jzcuCFZz0k1ZUvAd-W1QyLHTM9zBcSxqzoJ3_u3ei951r-KAIzuIV606P_iWDeXBJnZs4xQa3sVYb5L02BHnK0X47iaJF-beCRacGUn0n_gBQ2E1Uus93z7OR8qiGWlTn1e3FOe4byGVE0-yb3y6xm-kVeKnec6-gOw3x8XfyU15w3cheC7LRsZhMoHmLD6_nMtHDu-W7SKAo1rW6Q1LVajj-fLj68Ok5icYa5TaXeq1GytWmRwzCoJ7EQsd3hP31y4lZGowDlikH0ILjnG_i-YCSZl6LIPolFBklvCH_kJmgAI-90MEu4Q4nb_JWkCsvgtPqkNf9XnvTOKdcMviR3ql38i84ZcruAENc8fRyYRZdy9VYZ2lbEvM3QfoLuLTe0W5sLzc91cvpFl5kluK2JX1WTeSaAPSo77i1VlfZMk7JDdTFqm2WOs-GX0W_5TE5cX2hNRVJ4j3hJpta0ux8DrZDZT1ojNkEE0f-m1pGFG3seVTL4ondexxbSDLA6zQGYead7MKx70cHjkak57jtMHpHA7g0HcL3b4QM9SL4kxFSTPN9JdgydkXxvwwQp6P1XL-Hgm_IKeEjul0s5A0maNOM75mTEbpctqH02dCvwPUXNdxE-ouoV-CiySI4QHBseuDGBtiUoxqBEUsi7mfgjgl7i4NWKDZohSp8WSLuu_kbO_ZRhBoqt-B8aqbi8yEoJbSS-X4LyIQXUN_JFJyqGTEavoBpihwusHWVo4sk1m9y5zuR3_LmF6Kc6VLqCH2kQTm74_PjJ45xVf4yVFtir5IsV6USXBt7BJEovvm4wlVgn1Q0jkDgZM4n0eAEY4pJqKFAiLZGwvtdp6-i8506D4dQxEQ6rIyRC0ZQSeAHDJz5kyAnCKbUzlgGnq6-kuAKmu3Ns0G5dtTlTsEzNX7Vd1NXQhPpBnIPxr4bltgw0OvD38RUAF_g6P1j9LGZSg-WYoYgAkzSlxRnCACBnxc2B43aYVJG5.YRgRCO252KTtAgFHplPnnSUY2Rup0xYyF1p3ZBrHha5KjKNwvNVPEQOfK3OyIIsT5Sr-z4_083hdc6JZnG6wFg",
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $dataRes =  self::saveLogs($dadosCobranca, $response, $urlbase, $txId, $origemCobranca, $idCobOrigem);
        $arrayRes = [
            'rescURL' => $response,
            'dataResProcedure' => $dataRes,
            'txId' => $txId
        ];
        $arrayRes = (object) $arrayRes;
        return $arrayRes;

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
