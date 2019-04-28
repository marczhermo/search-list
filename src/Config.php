<?php

namespace Marcz\Search;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Dev\Debug;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use Exception;

class Config
{
    use Injectable, Configurable;
    private static $session_key = 'SearchListRememberedClient';
    private static $indices = [];
    private static $clients = [];
    private static $batch_length = 100;

    public static function indices()
    {
        return self::config()->get('indices');
    }

    public static function clients()
    {
        return self::config()->get('clients');
    }

    public static function batchLength()
    {
        return self::config()->get('batch_length');
    }

    public static function resolveIndex($indexName = null)
    {
        $indices = ArrayList::create(self::indices());
        $index   = $indices->find('name', $indexName);

        return $index ? $index['name'] : $indices->first()['name'];
    }

    public static function resolveClient($clientName = null)
    {
        $controller       = Controller::curr();
        $request          = $controller->getRequest();
        $session          = $request->getSession();
        $clients          = ArrayList::create(self::clients());
        $rememberedClient = $session->get(self::config()->get('session_key'));

        if ($clientName) {
            $client = $clients->find('name', $clientName);
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

            return $requestedClient;
        }

        if ($rememberedClient) {
            return $rememberedClient;
        }

        $requestedClient = $clients->first()['name'];
        $session->set(self::config()->get('session_key'), $requestedClient);

        return $requestedClient;
    }

    public static function getCurrentClient()
    {
        $clients = ArrayList::create(self::clients());

        return $clients->find('name', self::resolveClient());
    }
}
