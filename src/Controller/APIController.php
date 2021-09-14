<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class APIController extends AbstractController
{
    // Fonction permettant de faire appel à l'API et de retourner son contenu
    public function callAPI()
    {
        $url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest';
        // Pour limiter la consommation de crédit de l'API et améliorer la vitesse de réponse, on limite les retours aux 10 premières crypto-monnaies, ce qui comprend les 3 crypto-monnaies qui nous intéresse
        $parameters = [
            'start' => '1',
            'limit' => '10',
            'convert' => 'EUR'
        ];

        $headers = [
            'Accepts: application/json',
            'X-CMC_PRO_API_KEY: '
        ];
        $qs = http_build_query($parameters);
        $request = "{$url}?{$qs}";

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $request,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => 1
        ));

        $response = curl_exec($curl);

        $parsed_json = json_decode($response);

        curl_close($curl);

        return $parsed_json;
    }
}
