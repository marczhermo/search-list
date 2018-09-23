<?php

namespace Marcz\Search\Tests;

use SapphireTest;
use Marcz\Search\Config as SearchConfig;
use Injector;
use SS_HTTPRequest;
use Session;
use Spyc;
use Config;

/**
 * Config Test
 */
class ConfigTest extends SapphireTest
{
    use ConfigYMLTrait;

    protected $usesDatabase = true;

    public function setUp()
    {
        parent::setUp();
        // Created a singleton HTTPRequest with Session attached just like normal browsing
        $request = Injector::inst()->get(SS_HTTPRequest::class, true, ['GET', '/']);
        $this->searchConfigYmlFile();
    }

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
                'crawlBased' => '1',
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

    public function testResolveIndex()
    {
        $config = SearchConfig::create();
        $this->assertEquals('Pages', $config->resolveIndex());
        $this->assertEquals('Pages', $config->resolveIndex('Pages'));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Error: No clients configurations.
     */
    public function testResolveClientWithException()
    {
        SearchConfig::resolveClient('Unknown');
    }

    public function testResolveClient()
    {
        // Save client name to session
        $this->assertEquals('MySQL', SearchConfig::resolveClient('MySQL'));
        $this->assertEquals('MySQL', SearchConfig::resolveClient());

        // Existing Session by other sources
        $request = Injector::inst()->get(SS_HTTPRequest::class);
        $session = SearchConfig::currentSession();
        $session->set(SearchConfig::config()->get('session_key'), 'CustomClient');
        $this->assertEquals('CustomClient', SearchConfig::resolveClient());

        // Override existing session
        $this->assertEquals('MySQL', SearchConfig::resolveClient('MySQL'));
        $this->assertEquals('MySQL', SearchConfig::resolveClient());
    }

    public function testGetCurrentClient()
    {
        SearchConfig::resolveClient('MySQL');
        $this->assertEquals(
            [
                'name' => 'MySQL',
                'write' => false,
                'delete' => false,
                'export' => false,
                'class' => 'Marcz\Search\Client\MySQLClient',
            ],
            SearchConfig::getCurrentClient()
        );
    }
}
