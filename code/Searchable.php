<?php

class Searchable extends ViewableData
{
    
    /**
     * Cache of objects added via Searchable::add. This is used to
     * determine if the SearchForm is usable
     * 
     * @var array
     */
    private static $objects = array();
    
    public static function getObjects()
    {
        return self::$objects;
    }
    
    /**
     * Specify how many items should appear per page of results.
     * 
     * @var Int
     * @config
     */
    private static $page_length = 10;
    
    /**
     * Specify how many items should appear per object on the results
     * dashboard.
     * 
     * @var Int
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
     * 
     * @var Array
     * @config
     */
    private static $custom_filters = array();
    
    /**
     * 
     * 
     * @var Text
     * @config
     */
    private static $template_class = 'SearchResults';
    
    /**
     * Add an object to the Searchable module, this object will
     * automatically be added to the results page dashboard 
     * 
     * @param $classname Classname of the object we want to search
     * @param $columns An array of database column names we will search
     * @param $title The title of this object (that will appear in the dashboard)
     * 
     */
    public static function add($classname, $columns = array(), $title)
    {
        self::$objects[] = array(
            "ClassName" => $classname,
            "Columns" => $columns,
            "Title" => $title
        );
        
        $cols_string = '"' . implode('","', $columns) . '"';
    }
    
    /**
     * Return DataList of the results using $_REQUEST to get search info
     * Wraps around {@link searchEngine()}.
     * 
     * Results also checks to see if there is a custom filter set in
     * configuration and adds it.
     * 
     * @param $classname Name of the object we will be filtering
     * @param $columns an array of the column names we will be sorting
     * @param $query the current search query
     * 
     * @return SS_List
     */
     public static function Results($classname, $columns, $keywords, $limit = 0)
     {
        $cols_string = implode('","', $columns);
        $custom_filters = Searchable::config()->custom_filters;
        $results = ArrayList::create();

        $filter = array();

        foreach ($columns as $col) {
            $filter["{$col}:PartialMatch"] = $keywords;
        }

        $search = $classname::get()
            ->filterAny($filter);

        if (is_array($custom_filters) && array_key_exists($classname, $custom_filters) && is_array($custom_filters[$classname])) {
            $search = $search->filter($custom_filters[$classname]);
        }

        if ($limit) {
            $search = $search->limit($limit);
        }

        foreach ($search as $result) {
            if ($result->canView() || (isset($result->ShowInSearch) && $result->ShowInSearch)) {
                $results->add($result);
            }
        }

        return $results;
     }
}
