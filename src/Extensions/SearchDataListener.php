<?php

namespace Marcz\Search\Extensions;

use DataExtension;
use Config;
use Marcz\Search\Config as SearchConfig;
use ArrayList;
use Injector;

class SearchDataListener extends DataExtension
{
    protected $indices;
    protected $clients;

    protected function setUp()
    {
        $this->indices = SearchConfig::config()->get('indices');
        $this->clients = ArrayList::create(SearchConfig::config()->get('clients'))
                    ->filter([
                        'write'  => true,
                    ]);
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        $this->setUp();

        if (!$this->clients->exists()) {
            return;
        }

        foreach ($this->indices as $index) {
            if ($index['class'] !== $this->owner->ClassName) {
                continue;
            }

            if (Config::inst()->get($index['class'], 'disabledIndex')) {
                continue;
            }

            foreach ($this->clients as $client) {
                $className = $client->getField('class');
                $clientObj = Injector::inst()->create($className);
                $clientObj->createExportJob($index['name'], $index['class'], $this->owner->ID);
            }
        }
    }

    public function onAfterDelete()
    {
        parent::onAfterDelete();

        $this->setUp();

        foreach ($this->indices as $index) {
            if ($index['class'] !== $this->owner->ClassName) {
                continue;
            }

            if (Config::inst()->get($index['class'], 'disabledIndex')) {
                continue;
            }

            foreach ($this->clients as $client) {
                $className = $client->getField('class');
                $clientObj = Injector::inst()->create($className);
                $clientObj->createDeleteJob($index['name'], $index['class'], $this->owner->ID);
            }
        }
    }
}
