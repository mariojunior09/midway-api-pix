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
         
            $response = curl_exec($curl);
            //dd(curl_exec($curl));
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
            'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzUxMiJ9.ew0KICAgICJ2ZXIiOiAiMi4wIiwNCiAgICAic3ViIjogImY0NWM0Mjc4LWQ5MjktNGE1Yi1hZTI2LThiMDNiOWVmM2MwMiIsDQogICAgImlzcyI6ICJodHRwOi8vMTAuMTk0LjY2LjgzOjgwODAvYXV0aC9zZXJ2ZXIvb2F1dGgvdG9rZW4iLA0KICAgICJhdWQiOiAiaHR0cHM6Ly8xMC4xOTQuNjYuODM6ODQ0MyIsDQogICAgImlhdCI6IDE2MDk4Nzc1ODUsDQogICAgImV4cCI6IDE2MDk4ODExODUsDQogICAgInNjcCI6ICJjb2IucmVhZCBjb2Iud3JpdGUgcGl4LnJlYWQgcGl4LndyaXRlIHdlYmhvb2sud3JpdGUgd2ViaG9vay5yZWFkIiwNCiAgICAianRpIjogInZIMkRiekdYNVpXdWpRb0tTaHd3eUE9IiwNCiAgICAiY25mIjogew0KICJ4NXQjUzI1NiI6ICJnd1ovWmVwNnZnQ0JtaXdiejhUR3d1bnV5clV2L1ZTemhyVHBoUDVJalhZIg0KfSwNCiAgICAidG9rZW5UeXBlIjogImFjY2VzcyIsDQogICAgImNsaWVudFR5cGUiOiAic2VydmVyIiwNCiAgICAiYXV0aERhdGEiOiAiZXlKMGVYQWlPaUpLVjFRaUxDSmpkSGtpT2lKS1YxUWlMQ0poYkdjaU9pSlNVMEV0VDBGRlVDSXNJbVZ1WXlJNklrRXlOVFpEUWtNdFNGTTFNVElpZlEuSDUySGtnSlBYWjloOUQ2RUdKSDFsTUNKbWRXTGNuUmVMUTlmMXpXWWpzU2JYSXZLNlN6OGJaMFZQSGZDX29lcFlWUmxyTXlBdXpkMTVnME9Fc1JyUUVWYm1taW5xSU42TlljQXZsWkZXd01JN0dGV1NkbTktcDJVdGR5dlQwRmFfYlktQkFGWVhOb3U0YjBBX1AwUWNpYnhBNzlRbDFWYlZrLUMzM0ZhcEZjUkJrUm0yOGJSbllfd3hYOXp6Z2Rqb3oxejVtcDBLbjJ3UFdLT3BNUjNPd3V6NzJvSjFSTHlxNFhpZWYzVnNiY2FHZG9OM3lhdklxU3F0aXJqSEh0dHlzb1Q2SkRLUWdFZHRKYXlVRS1zZWpqTWFqS2Q4RXBUcEFtRFhWdmtSczVFNk0tckFteW0yaS1RbjVWWFNLQVI1VTZjWDJzYUhMQTZ3WFZTanh0R2E2c0hyMjRNRDFMcF9WZXNyN29YWFcwT052anpndnVENEkxT25FeDkyM3VTNkFuc3g5Y0gzS0gtSVZnS3doMUdGWXlTRGo5Z1pnVGNiQldPa1p2emFWaE8weEJ6X0E5dFBPQkR6VVZ3Mi1XRDZBbW0zb0NqWFUxcUxsU2JFckhWeHVNeDI2OEpQaWFfc1phc25HNmNxMTM1cC1oNzFVd3JjZG5WRTFlWE9wT3oyNHN1WFZpVzRCdmZKQjVJdEpyRzdmT2t4QjBoQ05wd19UeWVaRk5sN2ptSXl0NVdOR09fWHdlN1VzMVNMU1RoRGtFTHBRbGZ1d1J6M3RRdXJ4YWREZ3h1a29JYUh0VlF4bGg3eG1oSnJWdFBVWFhYdWFhNGV0bUR3TlVNSzZ1dktWV0dkNS1aLXRFSnNuNUFMVGJ0WjBJUDFYY0ViMkdwMmNSQ2dWdGlrUDguaWxhV1hDdU1rZE5ON2hBbFVMQ294Zy5wYnVqQmg1M1ZnV0lPb25jQmFYaThacHh1N1hDR1dHZGRKb0pWMU9KbXNEWXhoQ0oxcXFfbWt3N3praUtwbERta2paQ1BCS3F6WDBxbGYwZUl6WlY1bEdlcy1OLVM4U3BXc3p2OEtHV2JVXzE5OUpBVnNOZmpjazdRTy03ODZSRlE1N0lUd29YakJjV3NrbWYyRnhhMl9Vcm9kN0FsRjBCS2g3Z3d6UnRZcW9CSUpELURtQURrU3YxYTdJY0NEamY3QXFUeDhVd18xcEx4OG1pQjBBMnpOMGdpLXUzQXVGUlJFZVFFcHNaelVjVHJfLVMxQmVWTjV0bUFuZ0dxWng1dkN2TzBveUh0Z0k5cG1pbjBOX0pMeElvUlJMYm13dmZ4T1Rkb3VvOEU0SGp6enB0ZlVTdHVkeWh6cXFpa3ZBaGx1enlVWDlpOG5hb3dZbHZNSm1QdkVvbEpJUy15dTF0d3ZOd2NsdFdrdEZlTnFxZndpYTI2V05iY3N2TGwtOS1ac2FrQTg5bmdadndRczdvakwzT0FDS2ZkTWtoV2lpWTAydE1XQnhpRzc1bzRjT1M1Nm52TWw2eVNXYTF5d2tENVRiWE1ndnJqVWxyc2NNTFJzaTdXYW9Zbk9WdUN3TGdYNXdFQW5yYXdrVm5WUkxQWWRvbnQ2WjY2VDBqcTJqWDRSa2dBNVRGNUhfWHc3c2x6LU8zS2FXM1lSQ0JyNWhnWEp3RFRUdXBpNjV0akowSEJMME0tS1pfUDZKU1ItSEdsQTlTN050VjlfTHJSRHBkeFA0cGZXUXIxc0l3bFItZzRkQTB5T0VwanNvVWVaUWw0bU0tTFBsSi1zbTJicm95bzZDLVVIRGQwN2hMSkxJLXpZMEZCWDJlejlMbGdzeWZIVW1JdGQwQXphaVBXTjNxZ3o3VnI2LWRGQ0NtQWVUZ2ZJVktuLTNYMS1USHdreFJyVl9sbjJYQ1B0ZE5ZY0hucG11dUp1My1wVGFnUEd3ZFd6Sllyb1NOLXVMdUdWZjlNU2RQaFlOeXV1YlRjb2NZTXVMdnJVSE8zX2FMOHZkRnNpOTExUXc4SGNpTDE0WDBwUkM1ZV9xaFFObEluOEs3SDg3encyTldFc0hGVnJucVBQN3ljSVF5bktIbXF0alo0U2w3U1ZMMUwtcGJ5Z0V0dzBhVlZOOTZ6bDIwZWQzUHlXbV9abnlCQUZlLXJrR3JRRE9ZOHgzR2EtLWhjQmNaSG5uYXI4MUFfNi1PU21za0J3Ui1VZXNNdmF5T1hDSDJ1VkN5T1J0N243bXBvWDB3V01zM2U2WjZpcDFHYVI0dWtEX29oYTlaZmlucnpSWkVUOGZwQW1yR1F4YWlXaVhhU29PNEYyRkotd19VbExqeWdrRWt3b09zMFdMRVFOaUlicGo1Tng0YUdtZUlKcndnaDdvdXc1UUJpZHVTUjVmNTkwM2RBTXJnY2JJU0hsOFRqQ3htSmZObTNSbFU3aUtDV2QyTWNHUUp1ZndDVTFhV3F4MXBwQlJ1Ry1XUWdfTDFXR25QSXpsNUIyd3R5LW5pZ1h2T014Mm40cnNuOTVBTDRiWW9ySjQ2STJ5QXZad3BYeXRuZUZjckhxWlNxTm5ZVDIzbXNfd1Q0QlJsRTBMZ2FjYVZsZW95S29kMHBPaS1ubmZ5b01xeDVWYS1SeTQ5T0pKdWlMQllwQnlnS0hVa3Q3VlZxQjdLMGFqc1A2dll0SUFHSTZxNnFycnV0Umo0UTA3TTI0cWZhdk9xU05RZGRyVzVZLVdBMUJpb25IUVFIVnhISlFRc1VrWW1FQU1jaFJFdnhvajFVSUlGX1gwd3pJbVA1M29ISjR5b1ZrSDM3b2VQWWZpQnotekJkZzZvR19fYlgtQWU0Q2dzZXI4ckZRU0toV0JnOUg2blZNUTdqaUV3YmcyaVZFTjhpNHVLSVlNcTVKSTdpVWtEeldKR1Qxem15MkJOM1FCbEhsSW0zanNQZmZPMGhOSUE5cGR1TnBfaXFocS04M21zcHRTZG80Z1p0X0N2T0JoQm56M2lWVWp6X0t5SERHejZCUmR3QTYzN2ZraUpUWHZWaXdnTHBuZ2pMOVVJbE9fcnJFTW1Sc3Fnc2lrTGwwZ1NXdUZ3SUFUeHM0bzNicEdub0lwTktLLTNKVXM3Uml5RXAxc2pQYmFsbTZjbGRGWnRvbWtNZDJtQnRDZEd6QmI2UzZ1bS51WVRzU19Md1JWbk5YczdjMlp5YzNNclNqYU1ySVRMeG5FandSYThfZzZrIg0KfQ.YIKSaFpSjDSKgzOCakhuXd0eujKv5uABW9rZSlcs6soWHqevBM7tQzLC_W6v_d1MwyFwt59mpgEGILNtDZCZ4vJc73a7bBqpHj4fLjjVEOSPucdjbUlN1BIODXQF5WJKcwsbxjKwZi99gDCY0RwQFzr1A0gClle4tea_p9CV4Jud5gM_JJ2MyJR1kqsLSs3AGR2bTir5y7FTOB2QGyyZBUBV6vrgRDjrSz1sbAHHw12Duuvdj4TDKy2I3hvRUURAiSSQfCzegtSuogRgyEiASY2GIXI4BJz3ag1oWJJOLY_MpEQJl1B-zLFzivF3nbJwpEH_waOQaJc8b9jPPu9CnFKmaPEJQ3EPQ-wBe25gqs-JxKOl3RMAAkIIO0IW2qlJON54kVDRKoxbC9-vj1_q0bjJW9RKnaIm07TiHdX4VEwZmzUG9RUCiuqTCIHH00GZVi_l99wkIRgcZ0lkgo28ETedJnQbyMiRPjd88JgKl39YwgkSDQxL1LlxPO0-7TKvDpr_mJI2hKB-e9509ceC0daKiNqjXwHORFaxxKCV4TNA8tG43i0mi7vI1a8biMhgDIVNonrFnnz-TqRf7z72Fn0yNEJJx0q5SYl6eUv7Qz7fKpqgjeI8f4gasxevLI4si8TlqA2YVECVwAt9Xpom4S3fFrm3dkbkljymLhyACWA' 
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
