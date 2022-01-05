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
        protected string $version = 'v1'
    ) {}

    public function addUser(
        int    $terminal,
        string $cvv,
        string $expirationYear,
        string $expirationMonth,
        string $pan,
        string $order,
        string $productDescription = '',
        string $language = 'es'
    ): object
    {
        return $this->executeRequest('cards', [
            'terminal' => $terminal,
            'cvc2' => $cvv,
            'expiryYear' => $expirationYear,
            'expiryMonth' => $expirationMonth,
            'pan' => $pan,
            'order' => $order,
            'productDescription' => $productDescription,
            'language' => $language,
        ]);
    }

    public function executePurchase(
        int    $terminal,
        string $order,
        float  $amount,
        string $currency,
        string $ip,
        ?string $userId,
        ?string $userToken,
        string $description,
        string $urlOk,
        string $urlKo,
        string $email,
        int $paymentMethod,
    ): object
    {
        return $this->executeRequest('payments', [
            'payment' => [
                'terminal' => $terminal,
                'order' => $order,
                'amount' => $amount,
                'currency' => $currency,
                'methodId' => $paymentMethod,
                'originalIp' => $ip,
                'secure' => '1',
                'idUser' => $userId,
                'tokenUser' => $userToken,
                'productDescription' => $description,
                'merchantData' => [
                    'customer' => [
                        'email' => $email
                    ]
                ],
                'urlOk' => $urlOk,
                'urlKo' => $urlKo
            ]
        ]);
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
