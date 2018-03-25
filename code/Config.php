<?php

namespace Marcz\Search;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Config\Configurable;

class Config
{
    use Injectable, Configurable;

    public function details()
    {
        // $details = [];
        // foreach (self::config()->get('indices') as $index) {
        //     $config = [
        //         'clients'    => [],
        //         'indexName'  => $index['name'],
        //         'dataObject' => $index['class'],
        //     ];

        //     foreach (self::config()->get('clients') as $client) {
        //         $config['clients'][] = $client;
        //     }

        //     $details[] = $config;
        // }

        return [
            // 'configs'     => $details,
            'indices'     => self::config()->get('indices'),
            'clients'     => self::config()->get('clients'),
            'page_length' => self::config()->get('page_length'),
        ];
    }
}
