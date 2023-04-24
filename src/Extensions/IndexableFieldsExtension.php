<?php

namespace Marcz\Search\Extensions;

use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataExtension;
use Marcz\Search\Config as SearchConfig;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\Debug;
use SilverStripe\Versioned\Versioned;

class IndexableFieldsExtension extends DataExtension
{
    private static array $indexable_fields = [
        'ClassName',
        'Content',
        'Created',
        'ID',
        'LastEdited',
        'MenuTitle',
        'MetaDescription',
        'Sort',
        'Title',
        'URLSegment',
    ];
}
