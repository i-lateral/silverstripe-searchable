<?php

namespace ilateral\SilverStripe\Searchable;

use SilverStripe\View\ViewableData;
use SilverStripe\Dev\Deprecation;
use SilverStripe\ORM\ArrayList;
use ilateral\SilverStripe\Searchable\Control\SearchResults;
use ilateral\SilverStripe\Searchable\Model\SearchTable;
use SilverStripe\Core\ClassInfo;

class Searchable extends ViewableData
{

    /**
     * Cache of objects added via Searchable::add. This is used to
     * determine if the SearchForm is usable
     *
     * @var array
     * @config
     */
    private static $objects = [];

    /**
     * Specify how many items should appear per page of results.
     *
     * @var int
     * @config
     */
    private static $page_length = 10;

    /**
     * Specify how many items should appear per object on the results
     * dashboard.
     *
     * @var int
     * @config
     */
    private static $dashboard_items = 5;

    /**
     * Specify a list of custom filters that can be associated against
     * an object we are searching.
     *
     * For example if we are searching a "Product" object and want to
     * only show objects that have Disabled set to 0, we would add the
     * following to our _config.php
     *
     * Searchable::config()->custom_filters = array(
     *     "Product" => array(
     *         "Disabled" => 0
     *     )
     * );
     *
     * @var array
     * @config
     */
    private static $custom_filters = array();

    /**
     *
     *
     * @var string
     * @config
     */
    private static $template_class = SearchResults::class;

    /**
     * Add an object to the Searchable module, this object will
     * automatically be added to the results page dashboard
     *
     * @param $classname Classname of the object we want to search
     * @param $columns An array of database column names we will search
     * @param $title The title of this object (that will appear in the dashboard)
     *
     */
    public static function add($classname, $columns = array())
    {
        self::config()->objects[$classname] = $columns;

        $cols_string = '"' . implode('","', $columns) . '"';
    }

    /**
     * Return DataList of the results using $_REQUEST to get search info
     * Wraps around {@link searchEngine()}.
     *
     * Results also checks to see if there is a custom filter set in
     * configuration and adds it.
     *
     * @param string $classname Name of the object we will be filtering
     * @param array $columns an array of the column names we will be sorting
     * @param $query the current search query
     *
     * @return SS_List
     */
     public static function findResults($classname, $keywords, $limit = 0)
     {
        $custom_filters = Searchable::config()->custom_filters;
        $results = ArrayList::create();
        $all_classes = ClassInfo::ancestry($classname);
        $all_classes = array_merge(
            $all_classes,
            ClassInfo::subclassesFor($classname)
        );

        // Get a core results set from search table
        $search_ids = SearchTable::get()
            ->filter([
                'SearchFields:Fulltext' => $keywords,
                'BaseObjectClass' => $all_classes
            ])->columnUnique('BaseObjectID');

        // Now get a core results set based on found IDS (if results found)
        if (count($search_ids) > 0) {
            $search = $classname::get()->filter('ID', $search_ids);
    
            if (is_array($custom_filters) && array_key_exists($classname, $custom_filters) && is_array($custom_filters[$classname])) {
                $search = $search->filter($custom_filters[$classname]);
            }
    
            $searchable = Searchable::create();
    
            if ($searchable->hasMethod('filterResultsByCallback')) {
                $search = $searchable->filterResultsByCallback($search, $classname);
            }
    
            if ($limit) {
                $search = $search->limit($limit);
            }
    
            foreach ($search as $result) {
                if ($result->canView() || (isset($result->ShowInSearch) && $result->ShowInSearch)) {
                    $results->add($result);
                }
            }
        }

        return $results;
    }

    /**
     * @param $classname Name of the object we will be filtering
     * @param $columns an array of the column names we will be sorting
     * @param $query the current search query
     *
     * @return SS_List
     */
    public static function Results($classname, $columns, $keywords, $limit = 0)
    {
        Deprecation::notice(4.0, "Serachable::Results() is depreciated, use Serachable::findResults() instead");

        return self::findResults($classname, $keywords, $limit);
    }
}
