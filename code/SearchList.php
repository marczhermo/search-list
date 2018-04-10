<?php

namespace Marcz\Search;

use SilverStripe\View\ViewableData;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ArrayList;
use Marcz\Search\Config as SearchConfig;
use SilverStripe\Control\HTTPRequest;
use Exception;

// use SilverStripe\ORM\SS_List;
// use SilverStripe\ORM\Filterable;
// use Marcz\Search\Traits\Listable;
// use Marcz\Search\Traits\Filterables;

// class SearchList extends ViewableData implements SS_List, Filterable
class SearchList extends ViewableData
{
    // use Listable;
    // use Filterables;

    protected $term;
    protected $index;
    protected $client;
    protected $query;

    public function __construct($term = '', $indexName = null, $clientName = null)
    {
        $this->term = $term;
        $this->index = SearchConfig::resolveIndex($indexName);
        $this->client = SearchConfig::resolveClient($clientName);
    }
}
