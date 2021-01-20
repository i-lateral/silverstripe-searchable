<?php

namespace ilateral\SilverStripe\Searchable\Middleware;

use ilateral\SilverStripe\Searchable\Extensions\SearchableObjectExtension;
use ilateral\SilverStripe\Searchable\Model\SearchTable;
use ilateral\SilverStripe\Searchable\Searchable;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Middleware\HTTPMiddleware;
use SilverStripe\Core\Config\Config;

/**
 * Take loaded config and update searchtable with all fields
 */
class SearchableMiddleware implements HTTPMiddleware
{
    public function process(HTTPRequest $request, callable $delegate)
    {
        $objects = Config::inst()->get(Searchable::class, 'objects');

        // Build a master list of fields that will be added to search table
        $fields_to_search = [];

        foreach ($objects as $classname => $fields) {
            foreach (Config::inst()->get($classname, 'db') as $name => $type) {
                if (isset($fields_to_search[$name]) || !in_array($name, $fields)) {
                    continue;
                }

                $fields_to_search[$name] = $type;
            }

            // Add searchable extension to relevent classes
            Config::modify()->merge(
                $classname,
                'extensions',
                [SearchableObjectExtension::class]
            );
        }

        // Add found search fields to searchtable DB
        Config::modify()->merge(
            SearchTable::class,
            'db',
            $fields_to_search
        );

        // Setup fulltext indexes based on found search fields
        Config::modify()->merge(
            SearchTable::class,
            'indexes',
            [
                'SearchFields' => [
                    'type' => 'fulltext',
                    'columns' => array_keys($fields_to_search),
                ]
            ]
        );

        return $delegate($request);
    }
}
