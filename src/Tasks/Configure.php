<?php

namespace Marcz\Search\Tasks;

use BuildTask;
use Marcz\Search\Config;
use ArrayList;
use Injector;

class Configure extends BuildTask
{
    private static $segment = 'SearchList_Configure';

    protected $title = 'SearchList: Configure DataObjects to Indices';

    protected $description = 'Creates and initialise indices using the client API.';

    public function run($request)
    {
        $indices = Config::config()->get('indices');
        $clients = ArrayList::create(Config::config()->get('clients'))
                    ->filter([
                        'write'  => true,
                    ]);

        if (!$clients->exists()) {
            $noClient = <<<NOCLIENT
Error: No clients with 'write' configurations. See example below:
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
            die($noClient);
        }

        $message = '';
        foreach ($indices as $index) {
            $message .= sprintf('<p>Creating index, "%s" for class "%s"</p>', $index['name'], $index['class']);
            foreach ($clients as $client) {
                $className = $client->getField('class');
                $clientObj = Injector::inst()->create($className);
                $message .= sprintf('<p>Using client "%s"</p>', $className);
                try {
                    $clientObj->createIndex($index['name']);
                } catch (\Exception $e) {
                    $message .= sprintf('<p>Error client "%s" : "%s"</p>', $className, $e->getMessage());
                }
            }
        }

        echo $message;
    }
}
