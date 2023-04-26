<?php

namespace Marcz\Search\Tests;

use SilverStripe\Dev\SapphireTest;
use Marcz\Search\Config as SearchConfig;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use Marcz\Search\SearchList;
use SilverStripe\ORM\ArrayList;
use Page;

class SearchListTest extends SapphireTest
{
    use FlushHelper;

    protected $usesDatabase = true;

    public function setUp(): void
    {
        parent::setUp();

        // $_REQUEST['showqueries'] = 'inline';
        // Creating indices manually similar to adding YAML configuration
        $this->fixtureApplyConfiguration();
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(
            SearchList::class,
            SearchList::create('Apple', 'Pages', 'MySQL')
        );
    }

    public function testFetch()
    {
        $search = SearchList::create('Apple', 'Pages', 'MySQL');

        $this->assertInstanceOf(ArrayList::class, $search->fetch());
    }

    public function testGetResponse()
    {
        $search = SearchList::create('Apple', 'Pages', 'MySQL');
        $search->fetch();

        $this->assertArrayHasKey('_total', $search->getResponse());
        $this->assertArrayHasKey('hits', $search->getResponse());
    }

    public function testSetPageNumber()
    {
        $search = SearchList::create('Apple', 'Pages', 'MySQL');
        $search->setPageNumber(10)->fetch();

        $page = Page::get()
            ->filterAny(
                [
                    'Title:PartialMatch' => 'Apple',
                    'Content:PartialMatch' => 'Apple',
                    'MetaDescription:PartialMatch' => 'Apple',
                ]
            )
            ->limit("10,20");

        $this->assertEquals($page->sql(), $search->sql());
    }

    public function testSetPageLength()
    {
        $search = SearchList::create('Apple', 'Pages', 'MySQL');
        $search->setPageLength(100)->fetch();

        $page = Page::get()
            ->filterAny(
                [
                    'Title:PartialMatch' => 'Apple',
                    'Content:PartialMatch' => 'Apple',
                    'MetaDescription:PartialMatch' => 'Apple',
                ]
            )
            ->limit("0,100");

        $this->assertEquals($page->sql(), $search->sql());
    }

    public function testSql()
    {
        $search = SearchList::create('Apple', 'Pages', 'MySQL');

        $this->assertEquals('Run fetch() first.', $search->sql());

        $search->fetch();
        $page = Page::get()
            ->filterAny(
                [
                    'Title:PartialMatch' => 'Apple',
                    'Content:PartialMatch' => 'Apple',
                    'MetaDescription:PartialMatch' => 'Apple',
                ]
            )
            ->limit("0,20");

        $this->assertEquals($page->sql(), $search->sql());
    }
}
