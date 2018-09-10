<?php

namespace Marcz\Search;

use Object;
use DataObject;
use Controller;
use Session;
use ArrayList;
use SS_HTTPRequest;
use Injector;
use Exception;

class Config extends Object
{
    private static $session_key = 'SearchListRememberedClient';

    protected static $default_session = null;

    public static function currentSession()
    {
        if (Controller::has_curr()) {
            return Controller::curr()->getSession();
        } else {
            if (!self::$default_session) {
                self::$default_session = Injector::inst()->create('Session', isset($_SESSION) ? $_SESSION : array());
            }

            return self::$default_session;
        }
    }

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
        $index   = $indices->find('name', $indexName);

        return $index ? $index['name'] : $indices->first()['name'];
    }

    public static function resolveClient($clientName = null)
    {
        $request          = Injector::inst()->get(SS_HTTPRequest::class);
        $session          = self::currentSession();
        $clients          = ArrayList::create(self::config()->get('clients'));
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
        $clients = ArrayList::create(self::config()->get('clients'));

        return $clients->find('name', self::resolveClient());
    }

    public static function databaseFields($className, $parentClass = 'SiteTree')
    {
        $fields = DataObject::database_fields($className);
        if (is_subclass_of($className, $parentClass)) {
            $parentFields = DataObject::database_fields($parentClass);
            $fields = array_merge((array) $parentFields, (array) $fields);
        }

        return $fields;
    }
}
