<?php

namespace App\Library;

use Exception;
use Xenon\MultiCourier\Courier;
use Xenon\MultiCourier\Handler\RenderException;
use Xenon\MultiCourier\Provider\ECourier as EcourierProvider;
use function env;

class Ecourier
{
    /**
     * @return void
     * @throws RenderException
     * @throws Exception
     */
    public static function getTracking($ecr)
    {

        $courier = Courier::getInstance();
        $courier->setProvider(EcourierProvider::class, env('ECOURIER_ENVIRONMENT'));
        $courier->setConfig([
            'API-KEY' => env('ECOURIER_API_KEY'),
            'API-SECRET' => env('ECOURIER_API_SECRET'),
            'USER-ID' => env('ECOURIER_USER_ID'),
        ]);

        $courier->setRequestEndpoint('track', ['ecr' => $ecr]);

        try {
            $response = $courier->send();
            return $response->getData();
            //write here
        } catch (Exception $e) {
            $e->getMessage();
        }


    }
}
