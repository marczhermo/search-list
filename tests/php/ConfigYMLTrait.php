<?php

namespace Marcz\Search\Tests;

use Marcz\Search\Config as SearchConfig;
use Injector;
use Spyc;
use Config;

trait ConfigYMLTrait
{
    private function searchConfigYmlFile()
    {
        $fixture = Injector::inst()->create('YamlFixture', 'search-list/tests/fixture/search-config.yml');
        $parser = new Spyc();
        $fixtureContent = $parser->loadFile($fixture->getFixtureFile());
        Config::inst()->update(
            SearchConfig::class,
            'indices',
            $fixtureContent['Marcz\Search\Config']['indices']
        );
    }
}
