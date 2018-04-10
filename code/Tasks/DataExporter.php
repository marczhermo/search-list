<?php

namespace Marcz\Search\Tasks;

use SilverStripe\Dev\BuildTask;
use Marcz\Search\Config;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Core\Injector\Injector;

class DataExporter extends BuildTask
{
    private static $segment = 'SearchList_DataExporter';

    protected $title = 'SearchList: Exports DataObjects into Json or XML documents';

    protected $description = 'Creates a batch of queue jobs for sending bulk records to client API.';

    public function run($request)
    {
        $indices = Config::config()->get('indices');
        $clients = ArrayList::create(Config::config()->get('clients'))
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
