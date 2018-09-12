<?php

namespace Marcz\Search\Tasks;

use BuildTask;
use Config;
use Marcz\Search\Config as SearchConfig;
use ArrayList;
use Injector;

class DataExporter extends BuildTask
{
    private static $segment = 'SearchList_DataExporter';

    protected $title = 'SearchList: Exports DataObjects into Json or XML documents';

    protected $description = 'Creates a batch of queue jobs for sending bulk records to client API.';

    public function run($request)
    {
        $indices = SearchConfig::config()->get('indices');
        $clients = ArrayList::create(SearchConfig::config()->get('clients'))
                    ->filter([
                        'write'  => true,
                        'export' => ['json', 'xml'],
                    ]);

        if (!$clients->exists()) {
            $noClient = <<<NOCLIENT
Error: No clients with 'write' and 'export' configurations. See example below:
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
            if (Config::inst()->get($index['class'], 'disabledIndex')) {
                $message .= sprintf('<p>Indexing, "%s" for class "%s" is disabled.</p>', $index['name'], $index['class']);
                continue;
            }

            $message .= sprintf('<p>Creating export job for class "%s"</p>', $index['class']);
            foreach ($clients as $client) {
                $className = $client->getField('class');
                $clientObj = Injector::inst()->create($className);
                $clientObj->createBulkExportJob($index['name'], $index['class']);
                $message .= sprintf('<p>Using client "%s"</p>', $className);
            }
        }

        echo $message;
    }
}
