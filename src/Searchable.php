<?php

namespace ilateral\SilverStripe\Searchable;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataQuery;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Dev\Deprecation;
use SilverStripe\View\ViewableData;
use ilateral\SilverStripe\Searchable\Model\SearchTable;
use ilateral\SilverStripe\Searchable\Control\SearchResults;
use SilverStripe\ORM\Filters\FulltextFilter;

class Searchable extends ViewableData
{
    const DEFAULT_SORT = 'SearchableRelevance';

    const DEFAULT_ORDER = "DESC";

    /**
     * Cache of objects added via Searchable::add. This is used to
     * determine if the SearchForm is usable
     *
     * @var    array
     * @config
     */
    private static $objects = [];

    /**
     * Specify how many items should appear per page of results.
     *
     * @var    int
     * @config
     */
    private static $page_length = 10;

    /**
     * Specify how many items should appear per object on the results
     * dashboard.
     *
     * @var    int
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
     * @var    array
     * @config
     */
    private static $custom_filters = array();

    /**
     *
     *
     * @var    string
     * @config
     */
    private static $template_class = SearchResults::class;

    /**
     * Add an object to the Searchable module, this object will
     * automatically be added to the results page dashboard
     *
     * @param $classname Classname of the object we want to search
     * @param $columns   An array of database column names we will search
     * @param $title     The title of this object (that will appear in the dashboard)
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
     * @param array  $columns   an array of the column names we will be sorting
     * @param $query     the current search query
     *
     * @return SS_List
     */
    public static function findResults(
        $classname,
        $keywords,
        $limit = 0,
        $sort = self::DEFAULT_SORT,
        $order = self::DEFAULT_ORDER
    ) {
        $custom_filters = Searchable::config()->custom_filters;
        $results = ArrayList::create();
        $all_classes = [$classname];
        $all_classes = array_merge(
            $all_classes,
            ClassInfo::subclassesFor($classname)
        );

        $search_filter = FulltextFilter::create('SearchFields', $keywords);
        $search_filter->setModel(SearchTable::class);
        $select = sprintf(
            "(MATCH (%s) AGAINST ('{$keywords}'))",
            $search_filter->getDbName()
        );

        // Get a core results set from search table
        $search = SearchTable::get()
            ->filter(
                [
                'SearchFields:Fulltext' => $keywords,
                'BaseObjectClass' => $all_classes
                ]
            );

        // If custom filters used, filter any relevent items in search 
        if (is_array($custom_filters) && array_key_exists($classname, $custom_filters) && is_array($custom_filters[$classname])) {
            $object_ids = $classname::get()
                ->filter($custom_filters[$classname])
                ->columnUnique('ID');

            if (count($object_ids) > 0) {
                $search = $search->filter('BaseObjectID', $object_ids);
            }
        }

        $search = $search->alterDataQuery(
            function (DataQuery $query) use ($select, $sort, $order) {
                $query->selectField($select, self::DEFAULT_SORT);
                $query->sort($sort, $order);
            }
        );

        // Check if a custom filter method has been defined
        $searchable = Searchable::singleton();

        if ($searchable->hasMethod('filterResultsByCallback')) {
            $search = $searchable->filterResultsByCallback($search, $classname);
        }

        if ($limit > 0) {
            $search = $search->limit($limit);
        }

        foreach ($search as $result) {
            $object = $result->BaseObject();
            if ($object->canView() || (isset($object->ShowInSearch) && $object->ShowInSearch)) {
                $results->add($object);
            }
        }

        return $results->removeDuplicates();
    }

    /**
     * @param $classname Name of the object we will be filtering
     * @param $columns   an array of the column names we will be sorting
     * @param $query     the current search query
     *
     * @return SS_List
     */
    public static function Results($classname, $columns, $keywords, $limit = 0)
    {
        Deprecation::notice(4.0, "Serachable::Results() is depreciated, use Serachable::findResults() instead");

        return self::findResults($classname, $keywords, $limit);
    }
}
