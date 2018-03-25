<?php

namespace Marcz\Search\Tasks;

use SilverStripe\Dev\BuildTask;
use Marcz\Search\Config;
use Marcz\Search\Processor\Exporter;
use SilverStripe\Assets\File;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Core\Config\Config as FileConfig;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Core\Injector\Injector;

class DataExporter extends BuildTask
{
    private static $segment = 'SearchList_DataExporter';

    protected $title = 'SearchList: Exports DataObjects into Json or XML documents';

    public function run($request)
    {
        $length  = Config::config()->get('page_length');
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
                $clientObj->createExportJob($index['class']);
                $message .= sprintf('<p>Using client "%s"</p>', $className);
            }
        }

        echo $message;

        // var_dump($indices, $clients);
        // FileConfig::modify()->set(File::class, 'allowed_extensions', ['json']);

        // foreach ($indices as $config) {
        //     $file     = new File();
        //     $exporter = Exporter::create();
        //     $dateTime = DBDatetime::now();
        //     $bulk     = $exporter->bulkExport($config['class'], 0, $length);
        //     $fileName = $config['class'] . '_export_' . $dateTime->URLDatetime() . '.json';

        //     $file->setFromString(json_encode($bulk), $fileName);
        //     $file->write();
        //     $file->publishFile();

        //     echo '<p><a href="' . $file->getAbsoluteURL() . '" target="_blank">' . $fileName . '</a></p>';
        // }
    }
}
