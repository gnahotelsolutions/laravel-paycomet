<?php

namespace GNAHotelSolutions\LaravelPaycomet;

use Illuminate\Support\Facades\Http;

class ApiRest
{
    const HEADER_NAME = 'PAYCOMET-API-TOKEN';
    const URL = 'https://rest.paycomet.com';

    const PAYMENT_METHOD_CARD = 1;
    const PAYMENT_METHOD_BIZUM = 11;

    public function __construct(
        protected string $token,
        protected string $code,
        protected string $terminal,
        protected string $password,
        protected string $version = 'v1'
    ) {}

    public function addUser(
        string $cvv,
        string $expirationYear,
        string $expirationMonth,
        string $pan,
        string $order = '',
        string $productDescription = '',
        string $language = 'es'
    ): object
    {
        return $this->executeRequest('cards', [
            'terminal' => $this->terminal,
            'cvc2' => $cvv,
            'expiryYear' => $expirationYear,
            'expiryMonth' => $expirationMonth,
            'pan' => $pan,
            'order' => $order,
            'productDescription' => $productDescription,
            'language' => $language,
        ]);
    }

    public function infoUser(string $id, string $token): object
    {
        return $this->executeRequest('cards/info', [
            'terminal' => $this->terminal,
            'idUser' => $id,
            'tokenUser' => $token,
        ]);
    }

    public function executePurchase(
        string $order,
        int  $amount,
        string $currency,
        string $ip,
        ?string $userId,
        ?string $userToken,
        string $description,
        string $urlOk,
        string $urlKo,
        string $email,
        int $paymentMethod,
        bool $insecure = false,
    ): object
    {
        $params = [
            'payment' => [
                'terminal' => $this->terminal,
                'order' => $order,
                'amount' => $amount,
                'currency' => $currency,
                'methodId' => $paymentMethod,
                'originalIp' => $ip,
                'secure' => '1',
                'idUser' => $userId,
                'trxType' => 'N',
                'tokenUser' => $userToken,
                'productDescription' => $description,
                'merchantData' => [
                    'customer' => [
                        'email' => $email
                    ]
                ],
            ]
        ];

        if ($insecure) {
            $params['payment']['secure'] = 0;
            $params['payment']['scaException'] = 'MIT';
            $params['payment']['userInteraction'] = 0;
        } else {
            $params['payment']['userInteraction'] = 1;
            $params['payment']['urlOk'] = $urlOk;
            $params['payment']['urlKo'] = $urlKo;
        }

        return $this->executeRequest('payments', $params);
    }

    public function executePurchaseUrl(
        string $order,
        int  $amount,
        string $currency,
        string $locale,
        string $urlOk,
        string $urlKo,
    ): string
    {
        $params = [
            'MERCHANT_MERCHANTCODE' => $this->code,
            'MERCHANT_TERMINAL' => $this->terminal,
            'OPERATION' => 1,
            'LANGUAGE' => $locale, 'URLOK' => $urlOk,
            'URLKO' => $urlKo,
            'MERCHANT_ORDER' => $order,
            '3DSECURE' => true,
            'MERCHANT_AMOUNT' => $amount,
            'MERCHANT_CURRENCY' => $currency,
        ];

        $params['MERCHANT_MERCHANTSIGNATURE'] = hash('sha512', implode('', [
            $this->code,
            $this->terminal,
            1,
            $order,
            $amount,
            $currency,
            md5($this->password)
        ]));

        $params['VHASH'] = hash('sha512', implode('', [
            md5(http_build_query($params)),
            md5($this->password)
        ]));

        return 'https://api.paycomet.com/gateway/ifr-bankstore?' . http_build_query($params);
    }

    protected function executeRequest($endpoint, $params): object
    {
        return Http::asJson()
            ->withHeaders([self::HEADER_NAME => $this->token])
            ->post($this->getUrl($endpoint), $params)
            ->object();
    }

    protected function getUrl(string $endpoint): string
    {
        return implode('/', [
            self::URL,
            $this->version,
            $endpoint
        ]);
    }
}
