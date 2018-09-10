<?php

namespace Marcz\Search\Client;

use DataList;
use ArrayList;
use DataObject;
use Marcz\Search\Config;
use Injector;

class MySQLClient implements SearchClientAdaptor, DataSearcher
{
    protected $indexName;
    protected $indexConfig;
    protected $clientAPI;
    protected $response = ['_total' => 0, 'hits' => []];
    protected $rawQuery;

    /**
     * Instantiates the Client Library API
     */
    public function createClient()
    {
        $indexConfig = ArrayList::create(Config::config()->get('indices'))
            ->filter(['name' => $this->indexName])->first();

        $this->clientAPI   = new DataList($indexConfig['class']);
        $this->indexConfig = $indexConfig;

        return $this->clientAPI;
    }

    /**
     * Initialise the Index after creating a client instance
     *
     * @param string $indexName
     */
    public function initIndex($indexName)
    {
        $this->indexName = $indexName;

        return $this->createClient();
    }

    /**
     * Creates the Index when not found.
     * Note: Some clients automatically creates the index for you when importing documents.
     *
     * @param string $indexName
     */
    public function createIndex($indexName)
    {
        return $this->initIndex($indexname);
    }

    /**
     * Some clients have documents run through index object
     *
     * @param string $methodName
     * @param array $parameters
     * @return mixed
     */
    public function callIndexMethod($methodName, $parameters)
    {
        return $this->callClientMethod($methodName, $parameters);
    }

    /**
     * Some clients have documents run through client object
     *
     * @param string $methodName
     * @param array $parameters
     * @return mixed
     */
    public function callClientMethod($methodName, $parameters = [])
    {
        return call_user_func_array([$this->clientAPI, $methodName], $parameters);
    }

    /**
     * Returns the actual response from the Client API
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function search($term, $filters, $pageNumber, $pageLength)
    {
        $attribs  = $this->indexConfig['searchableAttributes'];
        $fields   = Config::databaseFields($this->indexConfig['class']);
        $object   = Injector::inst()->create($this->indexConfig['class']);
        $hasOne   = $object->config()->get('has_one');
        $hasMany  = $object->config()->get('has_many');
        $manyMany = $object->config()->get('many_many');

        $foreign    = array_merge((array) $hasOne, (array) $hasMany, (array) $manyMany);
        $orFilters  = $this->createInitialPartialMatch($term);
        $andFilters = [];

        $columns     = array_flip($attribs);
        $foreignKeys = array_intersect_key($foreign, $columns);

        foreach ($filters as $filter) {
            $columnFilters  = explode(':', key($filter));
            $filterKey      = array_shift($columnFilters);
            $filterName     = implode(':', $columnFilters);
            $filterValue    = current($filter);

            foreach ($foreignKeys as $columnName => $dataClass) {
                if ($columnName !== $filterKey) {
                    continue;
                }
                $filterName  = $filterName ?: 'PartialMatch';
                $filterValue = $term;
                $titleOrName = '';

                if ($schema->fieldSpec($dataClass, 'Title')) {
                    $titleOrName = 'Title';
                }

                if ($schema->fieldSpec($dataClass, 'Name')) {
                    $titleOrName = 'Name';
                }

                if (!$titleOrName) {
                    continue;
                }

                $orFilters[$filterKey . '.' . $titleOrName . ':' . $filterName] = $filterValue;
                break;
            }

            $filterName = $filterName ? ':' . $filterName : '';
            $andFilters[$filterKey . $filterName] = $filterValue;
        }


        if ($orFilters) {
            $this->clientAPI = $this->clientAPI->filterAny($orFilters);
        }

        if ($andFilters) {
            $this->clientAPI = $this->clientAPI->filter($andFilters);
        }

        $this->response = ['_total' => $this->clientAPI->count()];

        $this->clientAPI = $this->clientAPI->limit("$pageNumber,$pageLength");

        $this->response['hits'] = $this->clientAPI->toArray();
        $this->rawQuery = $this->clientAPI->sql();

        return new ArrayList($this->response['hits']);
    }

    public function createInitialPartialMatch($term)
    {
        $attribs = $this->indexConfig['searchableAttributes'];
        $columns = array_flip($attribs);
        $fields   = Config::databaseFields($this->indexConfig['class']);

        return array_reduce(
            array_keys(array_intersect_key($fields, $columns)),
            function ($carry, $column) use ($term) {
                $carry[$column . ':PartialMatch'] = $term;
                return $carry;
            },
            []
        );
    }

    public function sql()
    {
        return $this->rawQuery;
    }

    public function databaseFields($className, $parentClass = 'SiteTree')
    {
        $fields = DataObject::database_fields($className);
        if (is_subclass_of($className, $parentClass)) {
            $parentFields = DataObject::database_fields($parentClass);
            $fields = array_merge((array) $parentFields, (array) $fields);
        }

        return $fields;
    }
}
