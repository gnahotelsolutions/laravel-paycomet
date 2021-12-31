<?php

namespace GNAHotelSolutions\LaravelPaycomet;

use Illuminate\Support\Facades\Http;

class ApiRest
{
    const HEADER_NAME = 'PAYCOMET-API-TOKEN';
    const URL = 'https://rest.paycomet.com';

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
    ): mixed
    {
        return $this->executeRequest('cards', [
            'terminal' => $terminal,
            'cvc2' => $cvv,
            'expiryYear' => $expirationYear,
            'expiryMonth' => $expirationMonth,
            'pan' => $pan,
            'order' => $order,
            'productDescription' => $productDescription,
            'language' => 'ES',
            'notify' => 1
        ]);
    }

    protected function executeRequest($endpoint, $params): mixed
    {
        return Http::asJson()
            ->withHeaders([self::HEADER_NAME => $this->token])
            ->post($this->getUrl($endpoint), [$params])
            ->json();
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
