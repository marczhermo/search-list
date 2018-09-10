<?php

namespace Marcz\Search\Tests;

use SapphireTest;
use Marcz\Search\Config as SearchConfig;
use Injector;
use SS_HTTPRequest;
use Session;
use Marcz\Search\SearchList;
use ArrayList;
use Page;

/**
 * Config Test
 */
class SearchListTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function setUp()
    {
        parent::setUp();
        // $_REQUEST['showqueries'] = 'inline';
        // Created a singleton SS_HTTPRequest with Session attached just like normal browsing
        $request = Injector::inst()->get(SS_HTTPRequest::class, true, ['GET', '/']);
        // $request->setSession(new Session([]));
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(
            SearchList::class,
            SearchList::create('Apple', 'Page', 'MySQL')
        );
    }

    public function testFetch()
    {
        $search = SearchList::create('Apple', 'Page', 'MySQL');

        $this->assertInstanceOf(ArrayList::class, $search->fetch());
    }

    public function testGetResponse()
    {
        $search = SearchList::create('Apple', 'Page', 'MySQL');
        $search->fetch();

        $this->assertArrayHasKey('_total', $search->getResponse());
        $this->assertArrayHasKey('hits', $search->getResponse());
    }

    public function testSetPageNumber()
    {
        $search = SearchList::create('Apple', 'Page', 'MySQL');
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
        $search = SearchList::create('Apple', 'Page', 'MySQL');
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
        $search = SearchList::create('Apple', 'Page', 'MySQL');

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
