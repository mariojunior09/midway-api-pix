<?php

namespace App\helpers;

class HelperCreateToken
{

    /**
     * URL base do PSP
     * @var string
     */
    private $baseUrl;

    /**
     * Client ID do oAuth2 do PSP
     * @var string
     */
    private $clientId;

    /**
     * Client secret do oAuht2 do PSP
     * @var string
     */
    private $clientSecret;

    /**
     * Caminho absoluto até o arquivo do certificado
     * @var string
     */
    private $certificate;

    /**
     * Define os dados iniciais da classe
     * @param string $baseUrl
     * @param string $clientId
     * @param string $clientSecret
     * @param string $certificate
     */
    public function __construct($baseUrl, $clientId, $clientSecret, $certificate)
    {
        $this->baseUrl      = $baseUrl;
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->certificate  = $certificate;
        
    }

    private function getAccessToken()
    {
        //ENDPOINT COMPLETO
        $endpoint = $this->baseUrl . '/oauth/token';

        //HEADERS
        $headers = [
            'Content-Type: application/json'
        ];

        //CORPO DA REQUISIÇÃO
        $request = [
            'grant_type' => 'client_credentials'
        ];

        //CONFIGURAÇÃO DO CURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $endpoint,
            CURLOPT_USERPWD        => base64_encode($this->clientId . ':' . $this->clientSecret),
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($request),
            CURLOPT_SSLCERT        => $this->certificate,
            CURLOPT_SSLCERTPASSWD  => '',
            CURLOPT_HTTPHEADER     => $headers
        ]);

        //EXECUTA O CURL
        $response = curl_exec($curl);
        curl_close($curl);

        //RESPONSE EM ARRAY
        $responseArray = json_decode($response, true);

        //RETORNA O ACCESS TOKEN
        return $responseArray['access_token'] ?? '';
    }


    public  function send(){
       return $this->getAccessToken();
    }
}
