<?php


namespace Marcz\Search\Tests;

use Marcz\Search\Config as SearchConfig;
use Symfony\Component\Yaml\Yaml;

trait FlushHelper
{
    /**
     * Makes the test flush work on this project
     * vendor/bin/phpunit TEST_FILE '' flush=1
     * @return void
     * @throws \Exception
     */
    public static function setUpBeforeClass(): void
    {
        if (in_array('flush=1', $_SERVER['argv'])) {
            $GLOBALS['_GET']['flush'] = 1;
        }
        parent::setUpBeforeClass();
    }

    /**
     * Creating indices manually similar to adding YAML configuration
     * @return \SilverStripe\Core\Config\Config_ForClass
     */
    public function fixtureApplyConfiguration()
    {
        // Creating indices manually similar to adding YAML configuration
        $config = SearchConfig::config();
        $yaml = Yaml::parseFile(realpath(dirname(__DIR__)) . '/fixture/config.test.yml');
        $this->assertArrayHasKey('indices', $yaml[SearchConfig::class]);
        $config->merge('indices', $yaml[SearchConfig::class]['indices']);

        return $config;
    }
}
