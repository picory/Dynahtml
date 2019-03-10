<?php
return [
    'providers' => [
        # Picory DynaView
        Picory\Dynahtml\DynahtmlServiceProvider::class,
    ],
    'aliases' => [
        # Picory DynaView
        'DynaView' => Picory\Dynahtml\DynahtmlServiceProvider::class
    ],
];