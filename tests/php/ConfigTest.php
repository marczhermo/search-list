<?php

namespace Marcz\Search\Tests;

use SilverStripe\Dev\SapphireTest;
use Marcz\Search\Config as SearchConfig;

/**
 * Config Test
 */
class ConfigTest extends SapphireTest
{
    public function testDetails()
    {
        $config = SearchConfig::create()->details();
        $this->assertArrayHasKey('indices', $config);
        $this->assertArrayHasKey('clients', $config);
        $this->assertArrayHasKey('batch_length', $config);

        $this->assertContains(
            [
                'name' => 'Pages',
                'class' => 'Page',
                'has_one' => '1',
                'has_many' => '1',
                'many_many' => '1',
                'searchableAttributes' => [
                    'Title', 'Content', 'MetaDescription'
                ],
                'attributesForFaceting' => [
                    'Title', 'ShowInSearch'
                ],
            ],
            $config['indices']
        );

        $this->assertContains(
            [
                'name' => 'MySQL',
                'write' => false,
                'delete' => false,
                'export' => false,
                'class' => 'Marcz\Search\Client\MySQLClient'
            ],
            $config['clients']
        );

        $this->assertEquals(100, $config['batch_length']);
    }
}
