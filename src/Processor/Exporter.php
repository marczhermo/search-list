<?php

namespace Marcz\Search\Processor;

use UploadField;
use File;
use DataObject;
use DataList;
use Marcz\Search\Config;
use Versioned;

class Exporter extends SS_Object
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
            if (!$dataObject) {
                return null;
            }
        }

        $hasOne   = (array) $dataObject->config()->get('has_one');
        $hasMany  = (array) $dataObject->config()->get('has_many');
        $manyMany = (array) $dataObject->config()->get('many_many');

        $record    = $dataObject->toMap();
        $fields = Config::databaseFields($dataClassName);
        $this->extend('updateExport', $record, $clientClassName);

        foreach ($fields as $column => $fieldType) {
            if (!isset($record[$column])) {
                continue;
            }

            if ($fieldType === 'ForeignKey') {
                $field = Injector::inst()->create($fieldType, $column, $dataObject);
                $record[$column] = (int) $record[$column];
            } else {
                $field = Injector::inst()->create($fieldType);
            }

            $formField = $field->scaffoldFormField();
            if ($formField instanceof UploadField) {
                $record[$column] = (int) $record[$column];
            } else {
                $formField->setValue($record[$column]);
                $record[$column] = $formField->dataValue();
            }
        }

        foreach ($hasOne as $column => $className) {
            $oneItem = $dataObject->{$column}();
            if ($oneItem instanceof File) {
                $record[$column . '_URL'] = $oneItem->getAbsoluteURL();
                $record[$column . '_Title'] = $oneItem->getTitle();
            } else {
                $record[$column] = $oneItem->getTitle();
            }
        }

        foreach ($hasMany as $column => $className) {
            $items = [];
            foreach ($dataObject->{$column}() as $item) {
                $items[] = $item->getTitle();
            }
            if ($items) {
                $record[$column] = $items;
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
                $record[$column] = $items;
                if ($contents) {
                    $record[$column . '_content'] = $contents;
                }
            }
        }

        $dataObject->destroy();

        return $record;
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
