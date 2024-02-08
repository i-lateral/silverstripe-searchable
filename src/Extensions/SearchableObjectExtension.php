<?php

namespace ilateral\SilverStripe\Searchable\Extensions;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Core\Config\Config;
use ilateral\SilverStripe\Searchable\Searchable;
use ilateral\SilverStripe\Searchable\Model\SearchTable;

class SearchableObjectExtension extends DataExtension
{
    private static $belongs_to = [
        'SearchRecord' => SearchTable::class . '.BaseObject'
    ];

    /**
     * @return DataObject
     */
    public function getOwner()
    {
        return parent::getOwner();
    }

    public function saveToSearchRecord()
    {
        $owner = $this->getOwner();
        $ancestors = $owner->getClassAncestry();
        $search = $owner->SearchRecord();
        $objects = Config::inst()->get(Searchable::class, 'objects');

        // if objects isn't set cancel
        if (!is_array($objects)) {
            return;
        }

        $write = false;

        foreach (array_keys($objects) as $classname) {
            if (in_array($classname, $ancestors)) {
                foreach ($objects[$classname] as $field) {
                    if (isset($owner->$field)) {
                        $search->$field = $owner->$field;
                        $write = true;
                    }
                }
            }
        }

        if ($write) {
            $search->write();
        }
    }

    /**
     * After base object is written, push fields to search table
     *
     * @return null
     */
    public function onAfterWrite()
    {
        $owner = $this->getOwner();

        // If this is a versioned record, push to search after publish (not write)
        if ($owner->hasMethod('isPublished')) {
            return;
        }

        if ($owner->hasMethod('saveToSearchRecord')) {
            $owner->saveToSearchRecord();
        }
    }

    /**
     * After base object is published (if available), push fields to search table
     *
     * @return null
     */
    public function onAfterPublish()
    {
        $owner = $this->getOwner();

        if ($owner->hasMethod('saveToSearchRecord')) {
            $owner->saveToSearchRecord();
        }
    }

    /**
     * Delete the linked search record if
     * this record is deleted
     *
     * @return null
     */
    public function onAfterDelete()
    {
        $owner = $this->getOwner();
        /**
 * @var SearchTable 
*/
        $search = $owner->SearchRecord();

        if ($search->exists()) {
            $search->delete();
        }
    }
}