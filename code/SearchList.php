<?php

namespace Marcz\Search;

use SilverStripe\View\ViewableData;
use Marcz\Search\Config as SearchConfig;
use SilverStripe\Core\Injector\Injector;
use InvalidArgumentException;

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
    protected $filters = [];

    public function __construct($term = '', $indexName = null, $clientName = null)
    {
        $this->term   = $term;
        $this->index  = SearchConfig::resolveIndex($indexName);
        $this->client = SearchConfig::resolveClient($clientName);
    }

    public function fetch()
    {
        $clientConfig = SearchConfig::getCurrentClient();
        $clientObj    = Injector::inst()->create($clientConfig['class']);

        $clientObj->initIndex($this->index);

        return $clientObj->search($this->term);
    }

    public function filter()
    {
        // Validate and process arguments
        $arguments = func_get_args();
        switch (sizeof($arguments)) {
            case 1:
                $filters = $arguments[0];

                break;
            case 2:
                $filters = [$arguments[0] => $arguments[1]];

                break;
            default:
                throw new InvalidArgumentException('Incorrect number of arguments passed to filter()');
        }

        return $this->addFilter($filters);
    }

    /**
     * Collates an array of filter requirements
     *
     * @param array $filterArray
     * @return $this
     */
    public function addFilter($filterArray)
    {
        foreach ($filterArray as $expression => $value) {
            $this->filters[$expression] = $value;
        }

        return $this;
    }
}
