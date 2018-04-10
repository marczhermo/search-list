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

    public static function resolveIndex($indexName = null)
    {
        $indices = ArrayList::create(self::config()->get('indices'));
        $index = $indices->filter(['name' => $indexName])->first();

        return $index ? $index['name'] : $indices->first()['name'];
    }

    public static function resolveClient($clientName)
    {
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();
        $clients = ArrayList::create(self::config()->get('clients'));
        $rememberedClient = $session->get(self::config()->get('session_key'));

        if ($clientName) {
            $client = $clients->filter(['name' => $clientName])->first();
            if (!$client) {
                $noClient = <<<NOCLIENT
Error: No clients configurations. See example below:
<pre>
Marcz\Search\Config:
  clients:
    - name: 'Algolia'
      write: true
      delete: true
      export: 'json'
      class: 'Marcz\Algolia\AlgoliaClient'
</pre>
NOCLIENT;
                throw new Exception($noClient);
            }

            $requestedClient = $client['name'];
            $session->set(self::config()->get('session_key'), $requestedClient);
        } else {
            $requestedClient = $rememberedClient;
        }

        if ($requestedClient) {
            return $requestedClient;
        }

        $requestedClient = $clients->first()['name'];
        $session->set(self::config()->get('session_key'), $requestedClient);

        return $requestedClient;
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
