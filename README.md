# Search List

[![Build Status](https://travis-ci.org/marczhermo/search-list.svg?branch=master)](https://travis-ci.org/marczhermo/search-list)
[![Latest Stable Version](https://poser.pugx.org/marczhermo/search-list/v/stable)](https://packagist.org/packages/marczhermo/search-list)
[![codecov](https://codecov.io/gh/marczhermo/search-list/branch/master/graph/badge.svg)](https://codecov.io/gh/marczhermo/search-list)
[![Total Downloads](https://poser.pugx.org/marczhermo/search-list/downloads)](https://packagist.org/packages/marczhermo/search-list)
[![License](https://poser.pugx.org/marczhermo/search-list/license)](https://packagist.org/packages/marczhermo/search-list)

## Overview

This module provides a way to abstract different types of search engines which interfaces as a swappable client module.

The goal is to use the familiar/existing model filters as modifiers when searching for data.

Has built-in search client in the form of MySQL as the basis for third-party clients like Algolia, ElasticSearch, Swiftype and Solr for module implementation.

Currently supporting the following client modules:
- swiftype-search
- algolia-search
- elastic-serch

Todo:
- solr-search

## Usage

The example below provides a way to convert existing DataList like we used to fetch database results.

````
// Controller method using familiar DataList concrete implementation
$properties = Property::get();
$properties = $properties->filter(
    ['Title:PartialMatch'] => $request->getVar('Keywords');
);
$properties = $properties->filter([
    'AvailableStart:LessThanOrEqual' => $startDate,
    'AvailableEnd:GreaterThanOrEqual' => $endDate
]);

return ['Results' => $properties]; // DataList
````

Using this module we provide the following concrete implementation.

````
// Controller method using the module's interface
$properties = $this->createSearch(
    $request->getVar('Keywords'), 'Properties', 'Swiftype'
);
$properties->filter([
    'AvailableStart:LessThanOrEqual' => $startDate,
    'AvailableEnd:GreaterThanOrEqual' => $endDate
]);

return ['Results' =>$properties->fetch()]; // ArrayList
````

## Installation

SilverStripe 3

````
composer require marczhermo/search-list:^0.1
````

SilverStripe 4

````
composer require marczhermo/search-list
````

## Configuration
On your config.yml, the example below provides details for search engine client to gather data from your Model.
````
Marcz\Search\Config:
  indices:
    - name: 'FAQ'
      class: 'FAQ'
      has_one: true
      has_many: true
      many_many: true
      searchableAttributes:
        - 'Question'
        - 'Answer'
        - 'Keywords'
        - 'Category'
      attributesForFaceting:
        - 'Keywords'
        - 'Category'

````

For `Page` indexing, we have a different configuration by binding `SearchListSiteTree` extension to `onAfterPublish` and `onAfterUnPublish` events.

````
SiteTree:
  extensions:
    - Marcz\Search\Extenstions\SearchListSiteTree
````

And then our `config.yml` will be like the example configuration below of `Page` which also includes other dataObjects for indexing.

````
Marcz\Search\Config:
  batch_length: 100
  indices:
    - name: 'Pages'
      class: 'Page'
      crawlBased: true
      has_one: true
      has_many: true
      many_many: true
      searchableAttributes:
        - 'Title'
        - 'Content'
        - 'MetaDescription'
      attributesForFaceting:
        - 'Title'
        - 'ShowInSearch'
    - name: 'FAQ'
      class: 'FAQ'
      has_one: true
      has_many: true
      many_many: true
      searchableAttributes:
        - 'Question'
        - 'Answer'
        - 'Keywords'
        - 'Category'
      attributesForFaceting:
        - 'Keywords'
        - 'Category'
````

## Additional Tasks

- SearchList: Configure DataObjects to Indices
- Creates and initialise indices using the client API.

````
/dev/tasks/SearchList_Configure
````

- SearchList: Exports DataObjects into Json or XML documents
- Creates a batch of queue jobs for sending bulk records to client API.

````
/dev/tasks/SearchList_DataExporter
````

## Versioning

This library follows [Semver](http://semver.org). According to Semver,
you will be able to upgrade to any minor or patch version of this library
without any breaking changes to the public API. Semver also requires that
we clearly define the public API for this library.

All methods, with `public` visibility, are part of the public API. All
other methods are not part of the public API. Where possible, we'll try
to keep `protected` methods backwards-compatible in minor/patch versions,
but if you're overriding methods then please test your work before upgrading.

## Reporting Issues

Please [create an issue](https://github.com/marczhermo/silverstripe-sscounter/issues)
for any bugs you've found, or features you're missing.

## License

This module is released under the [BSD 3-Clause License](LICENSE)
