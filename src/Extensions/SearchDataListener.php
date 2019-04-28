<?php

namespace Marcz\Search\Extensions;

use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataExtension;
use Marcz\Search\Config as SearchConfig;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\Debug;
use SilverStripe\Versioned\Versioned;

class SearchDataListener extends DataExtension
{
    protected $indices;
    protected $clients;
    protected $batchLength;

    protected function setUp()
    {
        $this->batchLength = SearchConfig::batchLength();
        $this->indices = SearchConfig::indices();
        $this->clients = ArrayList::create(SearchConfig::clients())
            ->filter(['write'  => true]);
    }

    protected function isRunningDevBuild()
    {
        $controller = Controller::curr();

        return $controller && stripos($controller->getRequest()->getURL(), 'dev/build') === 0;
    }

    protected function indexMatchesClassName($indexClass)
    {
        return $indexClass === $this->owner->ClassName || $indexClass === $this->owner->getField('ObjectClass');
    }

    protected function getOwnerID($owner)
    {
        return $owner->getField('ObjectID') ?: $owner->ID;
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        $this->setUp();

        if ($this->isRunningDevBuild()) {
            return;
        }

        if (!$this->clients->exists()) {
            return;
        }

        foreach ($this->indices as $index) {
            if (!$this->indexMatchesClassName($index['class'])) {
                continue;
            }

            foreach ($this->clients as $client) {
                $className = $client->getField('class');
                $clientObj = Injector::inst()->create($className);
                $clientObj->createExportJob($index['name'], $index['class'], $this->getOwnerID($this->owner));
            }
        }
    }

    public function onAfterDelete()
    {
        parent::onAfterDelete();

        $this->setUp();

        if ($this->isRunningDevBuild()) {
            return;
        }

        foreach ($this->indices as $index) {
            if (!$this->indexMatchesClassName($index['class'])) {
                continue;
            }

            foreach ($this->clients as $client) {
                $className = $client->getField('class');
                $clientObj = Injector::inst()->create($className);
                $clientObj->createDeleteJob($index['name'], $index['class'], $this->getOwnerID($this->owner));
            }
        }
    }
}
