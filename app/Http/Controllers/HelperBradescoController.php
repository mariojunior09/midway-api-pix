<?php

namespace App\Http\Controllers;

use App\Procedures\HelperProcedures;
use Illuminate\Support\Facades\Log;


class HelperBradescoController extends Controller
{

    public static function getAccessToken()
    {

        $baseUrl = 'https://qrpix-h.bradesco.com.br/auth/server/oauth/token';
        $clientId = 'f45c4278-d929-4a5b-ae26-8b03b9ef3c02';
        $clientSecret = 'a534714d-c2d8-43d5-b98b-c7e4b4520906';
        $certificate = public_path('\files\mandacaru.crt.pem');
        $certificateSslKey = public_path('\files\ww8_libercard_com_br.key');

        //ENDPOINT COMPLETO
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $baseUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSLCERT       => $certificate,
            CURLOPT_SSLKEY        => $certificateSslKey,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: basic ZjQ1YzQyNzgtZDkyOS00YTViLWFlMjYtOGIwM2I5ZWYzYzAyOmE1MzQ3MTRkLWMyZDgtNDNkNS1iOThiLWM3ZTRiNDUyMDkwNg=='
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return  $response;
    }


    public static function createCobBradesco($dadosCobranca, $token)
    {
        $urlbase = 'https://qrpix-h.bradesco.com.br/v1/spi/cob/';
        $certificate = public_path('\files\mandacaru.crt.pem');
        $certificateSslKey = public_path('\files\ww8_libercard_com_br.key');
        $txId = md5(date('d/m/Y H:i:s') . rand());

        try {
            //HEADERS
            $headers = [
                'Cache-Control: no-cache',
                'Content-type: application/json',
                'Authorization: Bearer ' . $token
            ];


            //CONFIGURAÇÃO DO CURL
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL =>  $urlbase . $txId,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_SSLCERT        => $certificate,
                CURLOPT_SSLKEY         => $certificateSslKey,
                CURLOPT_POSTFIELDS => $dadosCobranca,
                CURLOPT_HTTPHEADER => $headers
            ));
            dd(curl_exec($curl));
            $response = curl_exec($curl);
            self::saveLogs($dadosCobranca, $response, $urlbase . $txId, $txId);
            return $response;
            curl_close($curl);
        } catch (\Exception $e) {
            Log::info($e);
        }
    }


    public static function getCobrancaBradescoByTxId($txId)
    {
        $urlbase = 'https://qrpix-h.bradesco.com.br/v1/spi/cob/';
        $certificate = public_path('\files\mandacaru.crt.pem');
        $certificateSslKey = public_path('\files\ww8_libercard_com_br.key');
        $token = self::getAccessToken();

        $access_token = json_decode($token);

        //HEADERS
        $headers = [
            'Cache-Control: no-cache',
            'Content-type: application/json',
            'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzUxMiJ9.ew0KICAgICJ2ZXIiOiAiMi4wIiwNCiAgICAic3ViIjogImY0NWM0Mjc4LWQ5MjktNGE1Yi1hZTI2LThiMDNiOWVmM2MwMiIsDQogICAgImlzcyI6ICJodHRwOi8vMTAuMTk0LjY2LjgzOjgwODAvYXV0aC9zZXJ2ZXIvb2F1dGgvdG9rZW4iLA0KICAgICJhdWQiOiAiaHR0cHM6Ly8xMC4xOTQuNjYuODM6ODQ0MyIsDQogICAgImlhdCI6IDE2MDk4Njk0OTgsDQogICAgImV4cCI6IDE2MDk4NzMwOTgsDQogICAgInNjcCI6ICJjb2IucmVhZCBjb2Iud3JpdGUgcGl4LnJlYWQgcGl4LndyaXRlIHdlYmhvb2sud3JpdGUgd2ViaG9vay5yZWFkIiwNCiAgICAianRpIjogImdZRWc2M1hLMEFnaG8tc2FwZVI0RUE9IiwNCiAgICAiY25mIjogew0KICJ4NXQjUzI1NiI6ICJnd1ovWmVwNnZnQ0JtaXdiejhUR3d1bnV5clV2L1ZTemhyVHBoUDVJalhZIg0KfSwNCiAgICAidG9rZW5UeXBlIjogImFjY2VzcyIsDQogICAgImNsaWVudFR5cGUiOiAic2VydmVyIiwNCiAgICAiYXV0aERhdGEiOiAiZXlKMGVYQWlPaUpLVjFRaUxDSmpkSGtpT2lKS1YxUWlMQ0poYkdjaU9pSlNVMEV0VDBGRlVDSXNJbVZ1WXlJNklrRXlOVFpEUWtNdFNGTTFNVElpZlEuSVI5NzM3RlA5bjdraVVrWVBscUJJLUloR2NVVzdWNlFpbUotOHVWSkdaM1gxOV81ZDJkTnpxUHN2VUlzcDJzNi1mWS14QURZbDlDOTRYamVOWWpSem5jT3VXeEhNVURVZ0dSTXV6SDdRUmVRTEM3dW9CUDBIMndaVWs0QjZIUWpZd3NjajF4TGIyN3FfQkNBNEZpWWl4cE1QMC0zSG9PNTlPOURQbXR3VU1HbEVoLWMzeV9NMjU3N2xXNEpfeVB3a2FXb0Itb3kzUjlCMFhpQmV0TWdNVUhPamNFTzJnd25sZlJVMmZQbUNHQkFqM3h1cTQzeWVaSTR6ajJsQTNZVkZadUw2LUYyTHI4MVA5OVBUNDN6ODJJYjFiRzhMWEt0ajViX3VwZGlJMTl5NEtyRVhqaGI1djhvUWo3eV80T3hSalZLX2U0WUIyQ3I5YWVLbVNIR2FudlREZ2R2SVhHYlBKSGZGWGdnM0UtR2o1ejFjUG5SRm45bmdaenFHVlhScEQ2ZjdKWnhlX3IyZzdpeHZIVlk1cmMwTEhud3FDTkpMTERDa09iRFo0QnRNQmpONHhwQkwxNkdmRGphZHZSZDhqa3dKNWtzNTZfRVV5bkdHRGROd1VROXpuRC1HV2VJZk1CXzhrQTRYQU1GOTB4dnl5SFBIM1E5OU5IOGYzeC14U2NPYnpERV9Qc3hCbnhQSjNaT2VFZlJNLUhpaWtxcERlaWZQZFZid2JWd2ZnLUVZOVoyVkJXcWs3aVJZNi1UWTR1RXlscmgxU0NiQ1F6VHltblZEWURTNmxLQjl4NUpVQkJjcU5qRmhDdXYxRmlWYWFuX1dCdFlfNnh4WVY3QmhDc3htMUc2Z3Q2bUJ6MmVFYUNFUjg1dGhzVkJNd0czVlJOUWpvN1c3LWcuSUoycnBDOFRrRllxTG5DdGp1Z3Z0QS44NWlDT2cwbkVVOTVJWEppeUdmNWlSTDB4Nk50UUxwTllzaEt1dlN5WnljSHNyN1U0b2hPdE9SY25xQnhoMV9hUGc1V245Y0x4cFFaNDJHekJlR2lqU1hpdWprb25KTXh1UTB4NTZINUV1R0ZENVlaNmlRNEJvY1BWQ0FGZmU2S3VOVlpLbzI4aFZmX0NFNkhscE1BX29ZUlZyb3h3SVBfYlRLcnJnQTctTW5MdHROZHV4NlZsb3lpNV8zNk9MTUpCNmlPN050bWdoLU9WVHNKUlZhVTJNU0lRWllqNDVWWXNvRUFNbTg3UzQwOTZLMzV1TExjdHhJQTAwRGJ1SU5Gc1I4U0Z3MGxCdFlib3VSY1B6UDZ2WWhOY0dLT2JnZmxxdmJQaU45d2ZsOGdrb2RRdUkxa1hFeWwweHllVkRsaERRNkNZa094bmE5QXNMa2JHLVZhWUdEZk16Zll0eC1FcjU2VGVNRUdSU0hRSW1mMEttT3Nlbjd6U1Vqb2xBbWxLeDRWOXVsVWU2TExLWXk2NURuMWZrWi1pcE5mSG9nU0dnNER2eVpaRmJXT3RJWmVjOTViQW13eHVCRW9pZ2dnbE5zaHNnZ3g1NnFDYUZ2QnA5ek90TWxLYy1NdUp1dVZ0WGJ2eGRuN056Q2lDa181TjRTdE1xSGdvOW1LWlRzQ0wzMFF3Z3U3RjNQNUEzaTRTM3RLSVd1ZjBqM2M1U0x0SkswdFc4dDdUaUx2bFJRT2syb2VIMXBKbzdGM1h5bGtVU2RQR0ZDa1BYY3Jsa1BNM3o1SlozYmxqQzNORUN2Z0QtRG9mYlFobjVnWDhrdlJ3UUowVFc0UmVtNWRoMGc0ckpOS0hLb0dfeTQ5cHVaR3NtTWRvamRYRV9zNkVVc0FpOGozTDhGbkFmTFBRZDNZMVlxY192b1I0WlBYMWxXU3NkbXJ1ejBlWWpld2p5S056TEdIM1pWWlBYbHVaTURrd0N3eGh6R05NY193T3VLeG9lMmhMUjRwWFlQbl9iU1ItaS1obnZrWkZxcS1aZS0tUzdXZkZvblJkYnR6MWJZVGltZ2lXWUxNNkhtaVpoUDJRNW1zTHJfNEhaV19vS3FGSVg5ZjJSbUFwdWh6Q3dKNUZjendVMEgxZG1RVl96dkxjNjQxZVZMZ2w1UTVHUWdBS1BtT3poNi1BT1JndXhCTU9Bamp6bzcySmZ1WkhPbzhXclliMjRyblpIZ05UNjhyendZVVcxZTVrcVFOak5DaGhESEJ1V3lGZEdIVnc2T0pqRTVKSEZlU2p4MVB4aV84S1hPWklmQ2tYa0YyNWMwakFSUTUyb3BpWm40N1pIMDNOaUk0VWY4bTE0R3VLemhzUzJKMWViWkVidnIzZGRLUk1icjlqMXNrWDJCbDJlb0drQUQ0QWFJTTRCS1QzN00taTRyd01vM05YVnJsTDVfUUNRRUx0bE10QmNLV1drbWJCYy1zN2hxajBxOThERmRwYks4TkotaWtXS3NKUmM5MHZRSlliRS1JU2VHNWFsNDVmSUg5bmVlUHlxR3B0dm5vMUJXYnA3VzgzdGZ5M3NGdWpOY1liMXFobENSMXVjVGxxOGFMV1JRejR3a2VkTW5rVlYxMXpteVZFcllfU1hjN2NLUEFNSWp3TXNGSVQ0MGM3aDJFWVNmN21ZUG9KbGViNUZ2UmtWbjdtZFRZYWduM3g1YU0xWTN2Zzd6bDBscG55Y0lYeHBCNV9CcDdhZW54aV9kUHVBRDNYcnA1SzE1d2lENm5ndklkUFhIejUyZ2J5TkZheFhDOWFBdENjZFRjNXdfdUF1WFpwR3BEYV9vd3lKUXZMN0xoQlJmVWhvTVZHQ3Z6NEV6d1k4ZU1fTkNzWWlWLTd1TU41YWZfdnFZVDBqd1hGRkw1dFU3LTFHbF9RbzdzMDJMZmRna0NlUXR1Sk03REJaZzM2UHhCT2o1WERldm0zWFRtQk1keUd6ZzhYRzJfeHNNRjZkbnR3NXFTQ09ZaklLamV2bWpBWjdNLXJhSS1XcFdYeXpkSlk1eW9uYWVZSHpIWFBUclpnSDhvcUJhSmFQSWNPbWVTcmFiSFA0M0lhdUg0cE00QXlRRjFJZm5RdHBpSFJxLVhRczRlU2Z6ZUdibUp0NlNqQkhPQkpjUlZmREFhdG5HOWVWUEYwZjd6b0Y2dW1KVlNqdzd0TTctT3dfRk1yUV9ENEFSdi5wTHBfNWdTZWwxZVk2ZzN5dXZ3cXItd2dabTRnejN2bjF0MGJPSWZEbGlBIg0KfQ.COGeT0ewphLC7c_DI0RuSHxqBiCZe-Lh_CXXtl9N9lOTBGYjStcScLuOxK2YF-tAqs83_zGX4iqwnWDd_iv3FfvQAzktfcMCSrMQAQh2_UoDVfmJHyl17SrAtqcsv1TBRbwSOU-u1R-BOfjDgIrU_glrzMxEPTkZYixglhgdp9gAQDlat-5tpHSlAuv8ZWNVTlYDETpxlQYbWiDD19xR93xcCmeYJ7IONysuXATZHFld6kzT1mYUThx6Hs_GYL0NUrna_RsVvEvHrbb6nLlPLreDvdks78W7FYnpN7f5Ohkd9poJqECOGwBr785rv13Umqr4tGbmJS37E8iXGWzMa2tGPfIxljuseJDBgsxPdFWNPLncFphdCAf5s5EX7Z-gTbltMQ6djDpWIvOAG1RqdTh01_PuHo7aUjhC7EB7WJPxlKVuLe_c93L3oX4xLsKlVa-1SW0kjBYYoi9IFBozEiEm3eRqCANUeywNMgjzZ-qDwMdH9ox-vVIFY86GcG358emXo-UdcXShGRJhEqobsIgteYvHI4qDfewpyMNTcXoXXqZK-FlR8iuGvDMrLe9UjgAtkbWu_1UVB0TVfmP2jap_vJGf1D66wQt6AL7rRyt4wKlLxwZDxoGpTw6YpKw7ehAfhxMSe8F5Ii6iHH8X9fPrTdycfX6RzfDqJyo92y4' 
        ];

        //CONFIGURAÇÃO DO CURL
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL =>  $urlbase . $txId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSLCERT        => $certificate,
            CURLOPT_SSLKEY         => $certificateSslKey,
            CURLOPT_HTTPHEADER => $headers
        ));
        dd(curl_exec($curl));
        $response = curl_exec($curl);
        return $response;
        curl_close($curl);
    }



    public static function saveLogs($p_dados_enviados, $p_dados_recebidos, $p_endpoint, $p_id_cobranc = null)
    {
        $result = json_decode(stripslashes($p_dados_recebidos));
        if (isset($result->codigoErro)) {
            HelperProcedures::pr_log_insere($p_dados_enviados, $p_dados_recebidos, $p_endpoint);
        } else {
            HelperProcedures::pr_cobranca_insere($p_dados_enviados, $p_dados_recebidos, $p_id_cobranc);
        }
    }
}
