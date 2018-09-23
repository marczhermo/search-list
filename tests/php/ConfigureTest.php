<?php

namespace Marcz\Search\Tests;

use SapphireTest;
use Marcz\Search\Tasks\Configure;
use Marcz\Search\Config as SearchConfig;
use Config;
use ArrayList;
use Injector;
use SS_HTTPRequest;

class ConfigureTest extends SapphireTest
{
    use ConfigYMLTrait;

    protected $usesDatabase = true;

    public function setUp()
    {
        parent::setUp();
        $this->searchConfigYmlFile();
    }

    public function testVariables()
    {
        $task = Configure::create();

        $this->assertEquals(
            'SearchList_Configure',
            $task->config()->get('segment')
        );

        $this->assertEquals(
            'SearchList: Configure DataObjects to Indices',
            $task->getTitle()
        );

        $this->assertEquals(
            'Creates and initialise indices using the client API.',
            $task->getDescription()
        );
    }

    public function testRunPageIsDisabled()
    {
        $task = Configure::create();
        $mySQL = [
            'name' => 'MySQL',
            'write' => true,
            'class' => 'Marcz\Search\Client\MySQLClient',
        ];
        $page = [
            'name' => 'Pages',
            'class' => 'Page',
            'crawlBased' => false,
        ];

        Config::inst()->remove(SearchConfig::class, 'clients');
        Config::inst()->remove(SearchConfig::class, 'indices');
        Config::inst()->update(SearchConfig::class, 'clients', [$mySQL]);
        Config::inst()->update(SearchConfig::class, 'indices', [$page]);
        Config::inst()->update('Page', 'disabledIndex', true);

        $this->expectOutputString(
            '<p>Indexing, "Pages" for class "Page" is disabled.</p>'
        );
        $task->run(new SS_HTTPRequest('GET', '/'));
    }

    public function testRunPageIsCrawlerBased()
    {
        $task = Configure::create();
        $mySQL = [
            'name' => 'MySQL',
            'write' => true,
            'class' => 'Marcz\Search\Client\MySQLClient',
        ];
        $page = [
            'name' => 'Pages',
            'class' => 'Page',
            'crawlBased' => true,
        ];

        Config::inst()->remove(SearchConfig::class, 'clients');
        Config::inst()->remove(SearchConfig::class, 'indices');
        Config::inst()->update(SearchConfig::class, 'clients', [$mySQL]);
        Config::inst()->update(SearchConfig::class, 'indices', [$page]);
        Config::inst()->update('Page', 'disabledIndex', false);

        $this->expectOutputString(
            '<p>Crawler-based index type, "Pages" for class "Page", use API dashboard.</p>'
        );
        $task->run(new SS_HTTPRequest('GET', '/'));
    }

    public function testRun()
    {
        $mySQL = [
            'name' => 'MySQL',
            'write' => true,
            'class' => 'Marcz\Search\Client\MySQLClient',
        ];
        $page = [
            'name' => 'Pages',
            'class' => 'Page',
            'crawlBased' => false,
        ];

        Config::inst()->remove(SearchConfig::class, 'clients');
        Config::inst()->remove(SearchConfig::class, 'indices');
        Config::inst()->update(SearchConfig::class, 'clients', [$mySQL]);
        Config::inst()->update(SearchConfig::class, 'indices', [$page]);
        Config::inst()->update('Page', 'disabledIndex', false);

        $this->expectOutputString(
            '<p>Creating index, "Pages" for class "Page"</p><p>Using client "Marcz\Search\Client\MySQLClient"</p>'
        );
        $task = Configure::create();
        $task->run(new SS_HTTPRequest('GET', '/'));
    }
}
