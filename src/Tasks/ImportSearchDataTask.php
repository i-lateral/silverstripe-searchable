<?php

namespace ilateral\SilverStripe\Searchable\Tasks;

use ilateral\SilverStripe\Searchable\Model\SearchTable;
use ilateral\SilverStripe\Searchable\Searchable;
use SilverStripe\ORM\DB;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Control\Director;
use SilverStripe\ORM\DatabaseAdmin;
use SilverStripe\Control\Controller;

/**
 * Take data from linked objects and import into search table
 */
class ImportSearchDataTask extends BuildTask
{

    /**
     * Should this task be invoked automatically via dev/build?
     *
     * @config
     *
     * @var bool
     */
    private static $run_during_dev_build = true;

    private static $segment = 'ImportSearchDataTask';

    protected $title = "Import search data";

    protected $description = "Import data into search table";

    /**
     * Run this task
     *
     * @param \SilverStripe\Control\HTTPRequest $request The current request
     *
     * @return void
     */
    public function run($request)
    {
        $objects = Searchable::config()->objects;

        foreach (array_keys($objects) as $classname) {
            $this->log("Importing {$classname}");
            $count = $this->importObject($classname);
            $this->log("Impoted {$count} records");
        }
    }

    /**
     * Import a single object class into search table and return how
     * many objects were imported
     *
     * @param string $classname
     * 
     * @return int
     */
    protected function importObject($classname)
    {
        $count = 0;

        foreach ($classname::get() as $object) {
            $search = SearchTable::get()
                ->filter(
                    [
                        'BaseObjectID' => $object->ID,
                        'BaseObjectClass' => get_class($object)
                    ]
                )->first();

            if (empty($search)) {
                $search = SearchTable::create();
                $search->BaseObjectID = $object->ID;
                $search->BaseObjectClass = get_class($object);
            }

            $data = $object->toMap();
            unset($data['ID']);
            unset($data['ClassName']);

            $search->update($data);
            $search->write();
            $count++;
        }

        return $count;
    }

    /**
     * @param string $text
     */
    protected function log($text)
    {
        if (Controller::curr() instanceof DatabaseAdmin) {
            DB::alteration_message($text, 'obsolete');
        } elseif (Director::is_cli()) {
            echo $text . "\n";
        } else {
            echo $text . "<br/>";
        }
    }
}
