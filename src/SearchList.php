<?php

namespace Marcz\Search;

use ViewableData;
use Marcz\Search\Config as SearchConfig;
use Injector;
use InvalidArgumentException;
use Exception;

class SearchList extends ViewableData
{
    protected $term;
    protected $index;
    protected $client;
    protected $clientAPI;
    protected $query;
    protected $filters    = [];
    protected $pageNumber = 0;
    protected $pageLength = 20;

    public function __construct($term = '', $indexName = null, $clientName = null)
    {
        $this->term   = $term;
        $this->index  = SearchConfig::resolveIndex($indexName);
        $this->client = SearchConfig::resolveClient($clientName);
    }

    public function setPageNumber($pageNumber)
    {
        $this->pageNumber = (int) $pageNumber;

        return $this;
    }

    public function setPageLength($pageLength)
    {
        $this->pageLength = (int) $pageLength;

        return $this;
    }

    public function fetch()
    {
        $clientConfig = SearchConfig::getCurrentClient();

        try {
            $this->clientAPI = Injector::inst()->create($clientConfig['class']);
            $this->clientAPI->initIndex($this->index);

            $response = $this->clientAPI->search(
                $this->term,
                $this->filters,
                $this->pageNumber,
                $this->pageLength
            );
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $response;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getResponse()
    {
        return $this->clientAPI->getResponse();
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
            $this->filters[][$expression] = $value;
        }

        return $this;
    }

    public function sql()
    {
        return $this->clientAPI ? $this->clientAPI->sql() : 'Run fetch() first.';
    }
}
