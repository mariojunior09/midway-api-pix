<?php

namespace App\pix;



class Payload
{
    /**
     * IDs do Payload do Pix
     * @var string
     */
    const ID_PAYLOAD_FORMAT_INDICATOR = '00';
    const ID_POINT_OFINITIATION_METHOD = '01';
    const ID_MERCHANT_ACCOUNT_INFORMATION = '26';
    const ID_MERCHANT_ACCOUNT_INFORMATION_GUI = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION_KEY = '01';
    const ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION = '02';
    const ID_MERCHANT_ACCOUNT_INFORMATION_URL = '25';
    const ID_MERCHANT_CATEGORY_CODE = '52';
    const ID_TRANSACTION_CURRENCY = '53';
    const ID_TRANSACTION_AMOUNT = '54';
    const ID_COUNTRY_CODE = '58';
    const ID_MERCHANT_NAME = '59';
    const ID_MERCHANT_CITY = '60';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE = '62';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID = '05';
    const ID_CRC16 = '63';

    /**
     * Chave pix
     * @var string
     */
    private $pixkey;


    /**
     * Descrição do pagamento
     * @var string
     */
    private $description;


    /**
     * Nome do titular da conta
     * @var string
     */
    private $merchantName;


    /**
     * Nome da cidade do titular da conta
     * @var string
     */
    private $merchantCity;


    /**
     * Id da transação pix
     * @var string
     */
    private $txId;


    /**
     * Valor  da transação pix
     * @var string
     */
    private $amount;


    /**
     * Definbe se o pagamento deve ser feito apenas uma vez
     * @var boolean
     */
    private $uniquePayment = false;

    /**
     * URL do payload dinamico
     * @var string
     */
    private $url;




    /**
     * @param string $pixkey
     */
    public function setPixkey($pixkey)
    {
        $this->pixkey = $pixkey;
        return $this;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string $merchantName
     */
    public function setMerchantName($merchantName)
    {
        $this->merchantName = $merchantName;
        return $this;
    }

    /**
     * @param string $merchantChity
     */
    public function setMerchantCity($merchantCity)
    {
        $this->merchantCity = $merchantCity;
        return $this;
    }

    /**
     * @param string $txId
     */
    public function setTxId($txId)
    {
        $this->txId = $txId;
        return $this;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = (string)number_format($amount, 2, '.', '');
        return $this;
    }


    /**
     * @param boolean $uniquePayment
     */
    public function setUniquePayment($uniquePayment)
    {
        $this->uniquePayment = $uniquePayment;
        return $this;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     *  Retorna o valor completo do payload
     *
     */
    private  function getValue($id, $valor)
    {

        $size = str_pad(strlen($valor), 2, '0', STR_PAD_LEFT);
        return $id . $size . $valor;
    }


    /**
     * Metodo responsavel por retornar os valores completos da informação da conta.
     * @return string
     */

    private function getMerchantAccountInformat()
    {

        $gui = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_GUI, 'br.gov.bcb.pix');

      // $key = strlen($this->key) ? $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_KEY, $this->pixkey) : '';

        $description = strlen($this->description) ? $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION, $this->description) : '';

        $url = strlen($this->url) ? $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_URL, $this->url) : '';

        return $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION, $gui . $description . $url);
    }


    /**
     * Retorna o valor completo do campo adicional do pix (TXID)
     * @return string
     */
    private function getAdditionalDataFieldTemplate()
    {
        $txId = $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID, $this->txId);

        return $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE, $txId);
    }

    /**
     * Método responsável por calcular o valor da hash de validação do código pix
     * @return string
     */
    public static function getCRC16($payload)
    {
    
        //ADICIONA DADOS GERAIS NO PAYLOAD
        $payload .= self::ID_CRC16 . '04';

        //DADOS DEFINIDOS PELO BACEN
        $polinomio = 0x1021;
        $resultado = 0xFFFF;

        //CHECKSUM
        if (($length = strlen($payload)) > 0) {
            for ($offset = 0; $offset < $length; $offset++) {
                $resultado ^= (ord($payload[$offset]) << 8);
                for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                    if (($resultado <<= 1) & 0x10000) $resultado ^= $polinomio;
                    $resultado &= 0xFFFF;
                }
            }
        }

        //RETORNA CÓDIGO CRC16 DE 4 CARACTERES
        return self::ID_CRC16 . '04' . strtoupper(dechex($resultado));
    }


    /**
     * Responsalve por retorna o ID_POINT_OFINITIATION_METHOD
     * @return string
     * 
     */
    private function getUniquePayment()
    {
        return $this->uniquePayment ? $this->getValue(self::ID_POINT_OFINITIATION_METHOD,'12') : '';
    }

    /**
     * Método responssável por gerar o cogido completo do payload do pix
     * @return strung
     */

    public function getPayload()
    {
        $payload = $this->getValue(self::ID_PAYLOAD_FORMAT_INDICATOR, '01') .
            $this->getUniquePayment() .
            $this->getMerchantAccountInformat() .
            $this->getValue(self::ID_MERCHANT_CATEGORY_CODE, '0000') .
            $this->getValue(self::ID_TRANSACTION_CURRENCY, '986') .
            $this->getValue(self::ID_TRANSACTION_AMOUNT, $this->amount) .
            $this->getValue(self::ID_COUNTRY_CODE, 'BR') .
            $this->getValue(self::ID_MERCHANT_NAME, $this->merchantName) .
            $this->getValue(self::ID_MERCHANT_CITY, $this->merchantCity) .
            $this->getAdditionalDataFieldTemplate();

        return $payload . $this->getCRC16($payload);
    }
}
