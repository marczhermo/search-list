<?php

namespace Marcz\Search\Extenstions;

use SiteTreeExtension;
use Config;
use Marcz\Search\Config as SearchConfig;
use ArrayList;
use Injector;

class SearchListSiteTree extends SiteTreeExtension
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
    /**
     * Hook called after the page's {@link Versioned::publishSingle()} action is completed
     *
     * @param SiteTree &$original The current Live SiteTree record prior to publish
     */
    public function onAfterPublish(&$original)
    {
        parent::onAfterPublish($original);

        $this->setUp();

        if (!$this->clients->exists()) {
            return;
        }

        foreach ($this->indices as $index) {
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

    /**
     * Hook called after the page's {@link SiteTree::doUnpublish()} action is completed
     */
    public function onAfterUnpublish()
    {
        parent::onAfterUnpublish();

        $this->setUp();

        foreach ($this->indices as $index) {
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
