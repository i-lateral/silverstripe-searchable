<?php

namespace ilateral\SilverStripe\Searchable\Extensions;

use ilateral\SilverStripe\Searchable\Model\SearchTable;
use ilateral\SilverStripe\Searchable\Searchable;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataExtension;

class SearchableObjectExtension extends DataExtension
{
    private static $belongs_to = [
        'SearchRecord' => SearchTable::class . '.BaseObject'
    ];

    /**
     * After base object is written, sync fields to search table
     *
     * @return null 
     */
    public function onAfterWrite()
    {
        /** @var \SilverStripe\ORM\DataObject */
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
     * Delete the linked search record if this record is deleted
     *
     * @return null
     */
    public function onAfterDelete()
    {
        $this->getOwner()->SearchRecord()->delete();
    }
}