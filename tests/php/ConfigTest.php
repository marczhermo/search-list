<?php

namespace Marcz\Search\Tests;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\Debug;
use SilverStripe\Dev\SapphireTest;
use Marcz\Search\Config as SearchConfig;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use Symfony\Component\Yaml\Yaml;

/**
 * Config Test
 */
class ConfigTest extends SapphireTest
{
    use FlushHelper;

    protected $usesDatabase = true;

    public function testIndices()
    {
        // By default indices are configured to be empty
        // In this way the programmer needs to configure this manually
        $this->assertEmpty(SearchConfig::indices());

        // Creating indices manually similar to adding YAML configuration
        $this->helpAddIndexConfiguration();

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
            SearchConfig::indices()
        );
    }

    public function testClients()
    {
        $this->assertContains(
            [
                'name' => 'MySQL',
                'write' => false,
                'delete' => false,
                'export' => false,
                'class' => 'Marcz\Search\Client\MySQLClient'
            ],
            SearchConfig::clients()
        );
    }

    public function testBatchLength()
    {
        $this->assertEquals(100, SearchConfig::batchLength());
    }

    public function testResolveIndex()
    {
        // Creating indices manually similar to adding YAML configuration
        $this->helpAddIndexConfiguration();

        $this->assertEquals('Pages', SearchConfig::resolveIndex());
        $this->assertEquals('Pages', SearchConfig::resolveIndex('Pages'));
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

        $controller = Controller::curr();
        $request = $controller->getRequest();
        $session = $request->getSession();
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
