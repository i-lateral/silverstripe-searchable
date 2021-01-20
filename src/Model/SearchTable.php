<?php

namespace ilateral\SilverStripe\Searchable\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Connect\MySQLSchemaManager;

/**
 * Base table to store searchable data in
 */
class SearchTable extends DataObject
{
    private static $table_name = 'SearchTable';

    private static $create_table_options = [
        MySQLSchemaManager::ID => 'ENGINE=MyISAM'
    ];

    private static $has_one = [
        'BaseObject' => DataObject::class
    ];
}