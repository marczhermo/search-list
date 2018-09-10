<?php

namespace Marcz\Search\Extensions;

use DataExtension;
use Marcz\Search\Config;
use ArrayList;
use Injector;

class SearchDataListener extends DataExtension
{
    protected $indices;
    protected $clients;

    protected function setUp()
    {
        $this->indices = Config::config()->get('indices');
        $this->clients = ArrayList::create(Config::config()->get('clients'))
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

            foreach ($this->clients as $client) {
                $className = $client->getField('class');
                $clientObj = Injector::inst()->create($className);
                $clientObj->createDeleteJob($index['name'], $index['class'], $this->owner->ID);
            }
        }
    }
}
