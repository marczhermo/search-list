<?php
namespace Marcz\Search\Client;

interface SearchClientAdaptor
{
    /**
     * Instantiates the Client Library API
     */
    public function createClient();

    /**
     * Initialise the Index after creating a client instance
     *
     * @param string $indexName
     */
    public function initIndex($indexName);

    /**
     * Creates the Index when not found.
     * Note: Some clients automatically creates the index for you when importing documents.
     *
     * @param string $indexName
     */
    public function createIndex($indexName);

    /**
     * Some clients have documents run through index object
     *
     * @param string $methodName
     * @param array $parameters
     * @return mixed
     */
    public function callIndexMethod($methodName, $parameters);

    /**
     * Some clients have documents run through client object
     *
     * @param string $methodName
     * @param array $parameters
     * @return mixed
     */
    public function callClientMethod($methodName, $parameters);

    /**
     * Returns the actual response from the Client API
     *
     * @return mixed
     */
    public function getResponse();
}
