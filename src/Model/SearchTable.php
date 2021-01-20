<?php

namespace ilateral\SilverStripe\Searchable\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use ilateral\SilverStripe\Searchable\Tasks\ImportSearchDataTask;

/**
 * Base table to store searchable data in
 */
class SearchTable extends DataObject
{
    private static $table_name = 'Searchable_SearchTable';

    private static $has_one = [
        'BaseObject' => DataObject::class
    ];

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        $run_migration = ImportSearchDataTask::config()->run_during_dev_build;
        $existing = self::get()->exists();

        if (!$existing && $run_migration) {
            $request = Injector::inst()->get(HTTPRequest::class);
            ImportSearchDataTask::create()->run($request);
        }
    }
}