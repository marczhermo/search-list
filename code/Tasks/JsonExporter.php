<?php

namespace Marcz\Search\Tasks;

use SilverStripe\Dev\BuildTask;
use Marcz\Search\Config;
use Marcz\Search\Exporter;
use SilverStripe\Assets\File;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Core\Config\Config as FileConfig;

class JsonExporter extends BuildTask
{
    private static $segment = 'SearchList_JsonExporter';

    protected $title = 'Exports DataObjects into Json documents';

    public function run($request)
    {
        $indices = Config::config()->get('indices');
        $length = Config::config()->get('page_length');

        FileConfig::modify()->set(File::class, 'allowed_extensions', ['json']);

        foreach ($indices as $config) {
            $file     = new File();
            $exporter = Exporter::create();
            $dateTime = DBDatetime::now();
            $bulk     = $exporter->bulkExport($config['class'], 0, $length);
            $fileName = $config['class'] . '_export_' . $dateTime->URLDatetime() . '.json';

            $file->setFromString(json_encode($bulk), $fileName);
            $file->write();
            $file->publishFile();

            echo '<p><a href="' . $file->getAbsoluteURL() . '" target="_blank">' . $fileName . '</a></p>';
        }
    }
}
