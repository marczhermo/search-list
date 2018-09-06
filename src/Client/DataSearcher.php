<?php
namespace Marcz\Search\Client;

interface DataSearcher
{
    public function search($term, $filters, $pageNumber, $pageLength);
}
