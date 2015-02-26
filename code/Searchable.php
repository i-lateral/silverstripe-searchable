<?php

class Searchable extends ViewableData {
    
    /**
     * Cache of objects added via Searchable::add. This is used to
     * determine if the SearchForm is usable
     * 
     * @var array
     */
    private static $objects = array();
    
    public static function getObjects() {
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
     * Add an object to the Searchable module, this object will
     * automatically be added to the results page dashboard 
     * 
     * @param $classname Classname of the object we want to search
     * @param $columns An array of database column names we will search
     * @param $title The title of this object (that will appear in the dashboard) 
     * 
     */
    static function add($classname, $columns = array(), $title) {
        self::$objects[] = array(
            "ClassName" => $classname,
            "Columns" => $columns,
            "Title" => $title
        );
        
        $cols_string = '"' . implode('","', $columns) . '"';
        
        Config::inst()->update($classname, 'create_table_options', array('MySQLDatabase' => 'ENGINE=MyISAM'));
        $classname::add_extension("SearchableDataObject('{$cols_string}')");
    }
} 
