<?php
return [
    'providers' => [
        # Picory Dynahtml
        Picory\Dynahtml\DynahtmlServiceProvider::class,
    ],
    'aliases' => [
        # Picory Dynahtml
        'Dynahtml' => Picory\Dynahtml\DynahtmlServiceProvider::class
    ],
];