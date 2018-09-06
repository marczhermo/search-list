<?php
namespace Marcz\Search\Client;

interface DataWriter
{
    /**
     * Updates a single record.
     * Note: Some clients creates the record when not found by ID.
     *
     * @param array $data record
     */
    public function update($data);

    /**
     * Updates a collection of records
     *
     * @param array $list Array of arrays/records
     */
    public function bulkUpdate($list);

    /**
     * Deletes a single record.
     *
     * @param int $recordID ID
     */
    public function deleteRecord($recordID);

    /**
     * Creates a bulk export job for updating a collection of records
     *
     * @param string $indexName Index Name
     * @param string $className Name of the class e.g. 'Page'
     */
    public function createBulkExportJob($indexName, $className);

    /**
     * Creates a single export job for updating one record
     *
     * @param string $indexName Index Name
     * @param string $className Name of the class e.g. 'Page'
     * @param int    $recordId Record ID
     */
    public function createExportJob($indexName, $className, $recordId);

    /**
     * Creates a single job for deleting one record
     *
     * @param string $indexName Index Name
     * @param string $className Name of the class e.g. 'Page'
     * @param int    $recordId Record ID
     */
    public function createDeleteJob($indexName, $className, $recordId);
}
