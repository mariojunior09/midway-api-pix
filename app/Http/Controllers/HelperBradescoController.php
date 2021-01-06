<?php

namespace App\Http\Controllers;

use App\Procedures\HelperProcedures;
use Illuminate\Support\Facades\Log;

use function GuzzleHttp\json_decode;

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

        return  json_decode($response);
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
         
            $response = curl_exec($curl);
           // dd(curl_exec($curl));
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
            'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzUxMiJ9.ew0KICAgICJ2ZXIiOiAiMi4wIiwNCiAgICAic3ViIjogImY0NWM0Mjc4LWQ5MjktNGE1Yi1hZTI2LThiMDNiOWVmM2MwMiIsDQogICAgImlzcyI6ICJodHRwOi8vMTAuMTk0LjY2LjgzOjgwODAvYXV0aC9zZXJ2ZXIvb2F1dGgvdG9rZW4iLA0KICAgICJhdWQiOiAiaHR0cHM6Ly8xMC4xOTQuNjYuODM6ODQ0MyIsDQogICAgImlhdCI6IDE2MDk5MzIyOTIsDQogICAgImV4cCI6IDE2MDk5MzU4OTIsDQogICAgInNjcCI6ICJjb2IucmVhZCBjb2Iud3JpdGUgcGl4LnJlYWQgcGl4LndyaXRlIHdlYmhvb2sud3JpdGUgd2ViaG9vay5yZWFkIiwNCiAgICAianRpIjogIkRMTmQ5K0JmQWhpRzNSYnZ4V2U2NGc9IiwNCiAgICAiY25mIjogew0KICJ4NXQjUzI1NiI6ICJnd1ovWmVwNnZnQ0JtaXdiejhUR3d1bnV5clV2L1ZTemhyVHBoUDVJalhZIg0KfSwNCiAgICAidG9rZW5UeXBlIjogImFjY2VzcyIsDQogICAgImNsaWVudFR5cGUiOiAic2VydmVyIiwNCiAgICAiYXV0aERhdGEiOiAiZXlKMGVYQWlPaUpLVjFRaUxDSmpkSGtpT2lKS1YxUWlMQ0poYkdjaU9pSlNVMEV0VDBGRlVDSXNJbVZ1WXlJNklrRXlOVFpEUWtNdFNGTTFNVElpZlEuU3pPTGJ3MmctVk9kUS1CRjVoaWRZa1k5N1dRUHdYajdJWVhLOWlEQjFLTXp1MFJLZFdoYmQxdjQzM2VZemw3ZDhFTXlPdDBkcmZJNFhzSTEwZDRPVUZxbjRJLUpHRWpSd0I3ZHZSd3BWZVZwb2F6V1luSFdZeHVwZXZna1hETUtoQXNRVTVzQXFMVktOVU4zdDdoenNhVmtCOF8tR3liOTdFNGpBU19fSGtuX1FIcEhodFI4OGM3VE00T05DdVN3WWxFWTdCcFY1UlZBYU15c0o5blY2OFMtSjN2WnQ2Vk1nTElBaXZXc1hPc3g2eFR5XzhQTWNqbUlTVWFrM3lvYU84RlF0azlYZ0pOZFpJT3piR1dWdmJIUFYyS1p4QV9xMXJiTU9URG8waHY2MGxkc21BdXMyMWRHRGRSeGJ6c3ZqVnlSazRHSExwUUtKR3p2SXJqQ1plOEpVU3R3N0wwcG1IRGUzSXVKN2pnYm9CUF9RZVRnQ3JNVTBBckd2NEt6eDRCTktoa3BpMGstZk5SR3JkbW82VGY2c3N0Y1VwMzNRZDJRczBHenNvT29lMGM2QU1PZ05rSzUyc25ZNzlKaW1tcnBKUTNCakg4OHI1ZEgyUFFhTTdWU2pmcnY3a3lIazZwWEJMZklZaTlNQ29NSEV1WXF5UklqNGFlMXhOcWlaa2FXaFNkRnRYOTVYSDl3c09TM0QxcXJuUkR6TmdnUXZTdjQ4dzF2SlhaWGotYmJadncxeUxZVFlHZjBZam85Rk9MN0dCbjVTMEIyT19rOVhISmEtMmpvUUE4VWE1eVRuUjF4Z3A2SzVJeUQyZ05lWnE3SFA1Y2YxWmY5T0lqNVVMSWJrcWNaV2xwRFFsdFFEczNqSjFfUktRMGxmdGx2MnRsNTdJNGVQcUkuZ180R2pJcjVjRzBiN3FNOGRLV0l1dy4yVFFQOF9WdUUtLTh2MjJyY0JvdWpvdDBBb0JZdDdsZ1hKNmdVdHRmNWlTbHJhbkdWWGhqLXI5a0x2OFl2cjFhU1doUk5wVEZqTVBMaUF2dEFOT3ItUzk5OURBMG1uRG04NHNDbTRoXzFCQzg5ZkxEbzQyb3RRcVlIYzR4aGJIeEEtLTRNSGQ1aGVCNk5hTU5QSVlPbzlBVWtUVDJZeF9PS2RpbXZnRGcwNWJPUEQyYi1Wa2MxVDlrWllXd3BkMWpNRVN0dElISkp6bDI3SFVwcjlueDZyWTh2R25nTi02b2RSRWxZLUdmTkxWMmxMVTN4WVh4VGU2Y0g0QjBKWHBRY1NvU2VPb01JYU5fS1hPdjFkOXA2eEFKc3M4dllZMUtBTlJ3LXZWbG1kbUpfczlkNWtOQVNsU2x3VWtBY3RQazdhVXdsTWpYNkEwNjZFVHBaSTZzTXl4Q0phMWw3SzR4Q2dQZTlYeWxWZmN3UUJsTE9idjFlOVlHRjFVRzJxUklScXJoMXZKVExnY3cyZjRZMzZBbFNFMnA4YVR5TENtblV2MV8xZzE2WnBGcXJEeW9BYlpQQ0hfU2oyWlNORkNDYmlIS0RsX3FrVWJtYXpneEo3YTdfNDdUMUpKYUJaWG95QkQ1SE9JVGZhUHpDdzlJQzJSbTUxTC1iMXVQRDRVLVpfckJMTUg3aERmaFd1UDZaRmR6MFFNVDlvNmJqYm5vSWx2S3lBNTRwWVRvQXY0Yi1Xc0V0Z19iSU1xNkVHUHJnb0lMTExSU3NSSTVMdnZBZ1hxS3IxaEMwTHljZldTOTFEZlhLTC1oZGw1V1pGSXVvU0dtWnpXRHREQ0gwMFdfZ2RXODhUT1BIQ3BtSGlNZlQ4TGYtY0VFVW9uMGxlOTQ0QWY2S20yOU9reXFmZUlQbkpBa19zcU1iNlVFa0Q4bF9DRGNjVDVzQUMxZldBTEo5TVYtYU5vb3p5S09Bakp3aUlZOW9oX0h5YW5LcUF4R0hCTDVWVXRUMlBXQ3JNOGxrVHRvWmZuS1dKS2hQMGRMMkVvc2dTM0Y2dk9kSHE5cndHdUdOeTdtZ1dseTZUUVpORHFkckIzbmJha2tMQjdGSG90eHhIMjJYcm5FTDFKRWNIYnVTeklmbHh0QlRpNFFmekFiYzl1NXp2OVVOWmloVTlmY25xa0h2SnF0YTBVcEhDbW9JSy1FbmR5MnRMakJkb0EtN2VZdVFVYURXOWxwZ05wMWZ1dWFNNXViTXY1YXBFUmNYTWhXdlYyYk13a2phSG5jZHZXYk5ZYldaTGJDa2swTFFHaFdfZjNJbE0yaDN5VGVzVjY0LWw0QTFXckFFQUJrZm1hSzZKWXBWM0RjMXdLZWpaMXJ5VndGSUxmaU16bG1wV0tDenItc3UyaDNNS2h2b2pfTEJRY0JOTk1CY2d6U3AzYmQ5SDdXbjZ2SjBZc1BkVzVoLUxGTF9pUTNkX3dTbEx6QTk4SnR0S01TajBOay1kSVFtaEh6R3RKME1fQ0ZBODZkejdneEZtUFg0NG83WG1HQjlvNmlnaENOd2luM1M4ZmVBX2l1ZEJpNlRERTh6clB4Q3htWHdfRGJIQkRnNWcta3RzTDZqWnZxSV8tZHhsS1p0TjVZdWdmeXFpY3JTdWFnWFh4TTBJRDdBamZXWnp0WjNINWJXcUFJb21md0NvVTZvZ0d0R0kwbTBUV051MjRfUGszRnZUTU9maWZIVjNra2NKMHByVzA5SXo4aV9ORWp6X3FOZjBzZjB5Z3A1NXN6OHBSQmVWY3otMDV4V1I5MUwyUnlEd080NnVpQ004VkV3ZThybm41NldSdlFiZWRwMUJwRXg0ekU3X3N3ZG9tRkdvOWJURnh2N2ZIUS01TVZtOVR3T0NjbWl3b2J4cXBsdlJWMzFRcXo4aW9nck1xOE03QTRJZF8tZmo1ZVJkdjNVMEdsUHo1RW9uTnQ4dk5rR25aWlMwd1dlVjRYcjdUdU9NZjRyYkJVSm9QWE1SRWJiTlE1X205OEEwX19RMWhxMVVDdEpJMWxuVjVIdENRc1NHS1RUMElxbXZ3WlpLalJjVHVTUzlvaGJqVE5RdmJrTzJEQ19rUXRycFNBVGNNaDd0ZEc3QllFNUxmOThVMzc0a2o5cElQUVpKNExfVnhPOHU0Sl9ZUk1xMm16UHFBZXJFMVpUWlpPVHJiSVFrYkdESmtNbGVzQy5MNHVVRWZ1eDRKdld1SDUwbC1vdFR5V3hLc01OWExHbFhSb1dVYXY5bGl3Ig0KfQ.PtAezOzvTCve0-dV5ywTjzs_vxhvTA3bC-5GwCb9a_NHlk41XGjbqmoCxGEXLXoR466y3qvLcDjkP-3bi3MJUqN3FW_-bA7qEA_NwmxhGlZwclucWsKzIMJ5Zy93qQdW2-IvBfQGyM4PEBddWrgP7v5W5LhTwtOJMG_H5KjLW90-Ws09MKXefyKtql38BDzZ3L5AwcvbXToR40nmUQwdHPNpsSpdA-YJsUxMhFo8sktnoSF8-MtfoMdNh9QuQ7ck7YDO9gIgkjnOxFU-H4d1h_avhkSrrC4yfwn4RdFwfxJ2eX9X-euIQgkv2pkxP8YZmwM3cdT1CGtKVakaQAbn2XemCSsRzv4h17wfycrqCFYKaNptUI1_JRQwxXy1qeOuUbMfTVNtM1JmPcUUysB89R_Mvp8xYDP7_vr14sjJXycOd_2eoO7CaZxv4u_0up-Urop_w-Uu51mJxddwPUw1-xuleoY44hTuYfKV0AklaxQGpdg-7WNBsckymgq8Bh9__5cKpr6tftpnHVudT4OFaKS1AwIF1VxniQ6KoC7HE1kOWtjE_GMO6vdGGn8xGGMS3djW6hs-qKpqDJLBimjErQbrokDJ85MlRdipnQ0xug5bYeDnAMFPnRkfR0R_RymYeJvwoFCMdBwnxLmcSwVHTCoyivNqAfUi_5Czi_AeAPA' 
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
