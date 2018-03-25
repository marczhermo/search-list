<?php

namespace Marcz\Search\Extensions;

use SilverStripe\ORM\DataExtension;

// use Marcz\Algolia\AlgoliaClient;
// use Marcz\Search\Exporter;

class SearchListDataListener extends DataExtension
{
    public function onAfterWrite()
    {
        parent::onAfterWrite();
        // $client   = AlgoliaClient::create();
        // $exporter = Exporter::create();
        // $data     = $exporter->export($this->owner);

        // $client->update($data);
    }

    public function onAfterDelete()
    {
        parent::onAfterDelete();
        // Todo
    }
}
