<?php

namespace Marcz\Search\Processor;

use UploadField;
use File;
use Object;
use DataObject;
use DataList;
use Marcz\Search\Config;
use Versioned;

class Exporter extends Object
{
    protected $className;

    public function setClassName($className)
    {
        $this->className = $className;
    }

    public function export($dataObject, $clientClassName = null)
    {
        $dataClassName = get_class($dataObject);
        if ($dataObject->hasExtension(Versioned::class)) {
            $dataObject = Versioned::get_by_stage(
                $dataClassName,
                'Live'
            )->byID($dataObject->ID);
        }

        $hasOne   = (array) $dataObject->config()->get('has_one');
        $hasMany  = (array) $dataObject->config()->get('has_many');
        $manyMany = (array) $dataObject->config()->get('many_many');

        $map    = $dataObject->toMap();
        $fields = Config::databaseFields($dataClassName);

        foreach ($fields as $column => $fieldType) {
            if (!isset($map[$column])) {
                continue;
            }

            if ($fieldType === 'ForeignKey') {
                $field = Injector::inst()->create($fieldType, $column, $dataObject);
                $map[$column] = (int) $map[$column];
            } else {
                $field = Injector::inst()->create($fieldType);
            }

            $formField = $field->scaffoldFormField();
            if ($formField instanceof UploadField) {
                $map[$column] = (int) $map[$column];
            } else {
                $formField->setValue($map[$column]);
                $map[$column] = $formField->dataValue();
            }
        }

        foreach ($hasOne as $column => $className) {
            $oneItem = $dataObject->{$column}();
            if ($oneItem instanceof File) {
                $map[$column . '_URL'] = $oneItem->getAbsoluteURL();
                $map[$column . '_Title'] = $oneItem->getTitle();
            } else {
                $map[$column] = $oneItem->getTitle();
            }
        }

        foreach ($hasMany as $column => $className) {
            $items = [];
            foreach ($dataObject->{$column}() as $item) {
                $items[] = $item->getTitle();
            }
            if ($items) {
                $map[$column] = $items;
            }
        }

        foreach ($manyMany as $column => $className) {
            $items    = [];
            $contents = [];
            foreach ($dataObject->{$column}() as $item) {
                $items[] = $item->getTitle();
                if (!empty($item->Content)) {
                    $contents[] = $item->Content;
                } elseif (!empty($item->HTML)) {
                    $contents[] = $item->HTML;
                }
            }
            if ($items) {
                $map[$column] = $items;
                if ($contents) {
                    $map[$column . '_content'] = $contents;
                }
            }
        }

        $this->extend('updateExport', $map, $clientClassName);
        $dataObject->destroy();

        return $map;
    }

    public function bulkExport($className, $startAt = 0, $max = 0, $clientClassName = null)
    {
        $list   = new DataList($className);
        $fields = Config::databaseFields($className);
        if (isset($fields['ShowInSearch'])) {
            $list = $list->filter('ShowInSearch', true);
        }

        $total  = $list->count();
        $length = 20;
        $max    = $max ?: Config::config()->get('batch_length');
        $bulk   = [];
        $start  = $startAt;
        $pages  = $list->limit("$start,$length");
        $count  = 0;

        while ($pages) {
            foreach ($pages as $page) {
                if (!$page) {
                    break;
                }

                $bulk[] = $this->export($page, $clientClassName);
                $page->destroy();
                unset($page);
                $count++;
            }

            if ($pages->count() > ($length - 1)) {
                $start += $length;
                $pages = $list->limit("$start,$length");
            } else {
                break;
            }

            if ($max && $max > 0 && count($bulk) >= $max) {
                break;
            }
        }

        return $bulk;
    }
}
