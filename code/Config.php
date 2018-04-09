<?php

namespace Marcz\Search;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use Exception;

class Config
{
    use Injectable, Configurable;
    private static $session_key = 'SearchListRememberedClient';

    public function details()
    {
        return [
            'indices'      => self::config()->get('indices'),
            'clients'      => self::config()->get('clients'),
            'batch_length' => self::config()->get('batch_length'),
        ];
    }

    public static function resolveIndex($index = null)
    {
        $indices = ArrayList::create(self::config()->get('indices'));
        $currentIndex = $indices->filter(['name' => $index])->first();

        return $currentIndex ? $currentIndex['name'] : $indices->first()['name'];
    }

    public static function resolveClient($client)
    {
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();
        $rememberedClient = $session->get(self::config()->get('session_key'));
        $clients = ArrayList::create(self::config()->get('clients'));
        $requestedClient = $clients->filter(['name' => $client])->first();

        if (!$requestedClient) {
            $noClient = <<<NOCLIENT
Error: No clients configurations. See example below:
<pre>
Marcz\Search\Config:
    clients:
    - name: 'Algolia'
        write: true
        export: 'json'
        class: 'Marcz\Algolia\AlgoliaClient'
</pre>
NOCLIENT;
            throw new Exception($noClient);
        }

        if ($rememberedClient === $requestedClient['name']) {
            return $rememberedClient;
        }

        $session->set(self::config()->get('session_key'), $requestedClient['name']);

        return $requestedClient['name'];
    }

    public static function getCurrentClient()
    {
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();
        $rememberedClient = $session->get(self::config()->get('session_key'));

        return ArrayList::create(self::config()->get('clients'))
            ->filter(['name' => $session->get(self::config()->get('session_key'))])->first();
    }
}
