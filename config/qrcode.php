<?php

return [

    'default' => env('QRCODE_DRIVER', 'gd'),

    'drivers' => [

        'gd' => [
            'driver' => 'gd',
        ],

        'imagick' => [
            'driver' => 'imagick',
        ],

    ],

];
