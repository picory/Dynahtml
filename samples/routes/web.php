<?php
return [
    'providers' => [
        # Picory View
        Picory\Dynahtml\DynahtmlServiceProvider::class,
    ],
    'aliases' => [
        # Picory View
        'View' => Picory\Dynahtml\DynahtmlServiceProvider::class
    ],
];