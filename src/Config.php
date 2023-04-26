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
    use Injectable;
    use Configurable;

    private static $session_key = 'SearchListRememberedClient';

    private static $indices = [];

    private static $clients = [];

    private static $batch_length = 100;

    public static function indices(): array
    {
        $indices = self::config()->get('indices');

        return $indices ?? [];
    }

    public static function clients(): array
    {
        $client = self::config()->get('clients');

        return $client ?? [];
    }

    public static function batchLength(): int
    {
        return self::config()->get('batch_length') ?? 100;
    }

    public static function resolveIndex(string $indexName = ''): string
    {
        $indices = ArrayList::create(self::indices());
        $noIndex = sprintf(
            'Exception: No index configuration. See example below:%s%s',
            PHP_EOL,
            self::config()->get('example_index_config')
        );

        if (!$indices->exists()) {
            throw new Exception($noIndex);
        }

        if ($indexName) {
            $index = $indices->find('name', $indexName);

            if (!$index) {
                throw new Exception($noIndex);
            }

            return $index['name'];
        }

        return $indices->first()['name'];
    }

    public static function resolveClient(string $clientName = ''): string
    {
        $controller       = Controller::curr();
        $request          = $controller->getRequest();
        $session          = $request->getSession();
        $clients          = ArrayList::create(self::clients());
        $rememberedClient = $session->get(self::config()->get('session_key'));

        if ($clientName) {
            $client = $clients->find('name', $clientName);

            if (!$client) {
                $noClient = sprintf(
                    'Exception: No clients configuration. See example below:%s%s',
                    PHP_EOL,
                    self::config()->get('example_client_config')
                );

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
