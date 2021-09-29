<?php

namespace Weblike\Cms\Shop\Interfaces;

interface IProduct
{
    const STATUS = [
        1 => [
            'color' => 'primary',
            'name' => 'Publikovaný',
        ],
        2 => [
            'color' => 'warning',
            'name' => 'Koncept',
        ],
        3 => [
            'color' => 'info',
            'name' => 'Naplánovaný'
        ]
    ];

    const AVAILABILITY = [
        'enable'    => [
            'color' => 'primary',
            'name' => 'Zapnutý'
        ],
        'disable'   => [
            'color' => 'danger',
            'name' => 'Vypnutý'
        ]
    ];

    const PUBLISHED = 1;

    const DRAFT = 2;
    
    const SHEDULED = 3;

    const ENABLED = 1;

    const DISABLED = 2;

}