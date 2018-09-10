<?php

namespace Marcz\Search\Extensions;

use Extension;
use Marcz\Search\SearchList;

class SearchContentExtension extends Extension
{
    public function createSearch($term = '', $index = null, $client = null)
    {
        return SearchList::create($term, $index, $client);
    }
}
