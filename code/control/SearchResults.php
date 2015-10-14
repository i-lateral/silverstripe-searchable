<?php

class SearchResults extends Controller {
    
    /**
     * Designate the URL segment of this controller, used when
     * generating links to this controller.
     * 
     * @var string
     * @config
     */
    private static $url_segment = "results";
    
    /**
     * @config
     */
    static $allowed_actions = array(
        "object"
    );
    
    public function getQuery() {
        return $this->request->getVar('Search');
    }
    
    public function Link($action = null) {
		return Controller::join_links(
			$this->config()->url_segment,
			$action
		);
	}
	
    public function AbsoluteLink($action = null) {
		return Controller::join_links(
			Director::absoluteBaseURL(),
			$this->Link($action)
		);
	}
	
    public function index() {
        $keywords = $this->getQuery();
        $limit = Searchable::config()->dashboard_items;
        $classes_to_search = Searchable::getObjects();
        $objects_list = ArrayList::create();
        
        if(count($classes_to_search) == 1) {
            return $this->redirect(Controller::join_links(
                self::config()->url_segment,
                "object",
                $classes_to_search[0]['ClassName'],
                "?Search={$keywords}"
            ));
        }
        
        foreach($classes_to_search as $object) {
            $results = Searchable::Results($object["ClassName"], $object["Columns"], $keywords, $limit);
            
            if($results->exists()) {
                $objects_list->add(ArrayData::create(array(
                    "Title" => $object["Title"],
                    "ClassName" => $object["ClassName"],
                    "Results" => $results,
                    "Link" => Controller::join_links(
                        $this->config()->url_segment,
                        "object",
                        $object["ClassName"],
                        "?Search={$keywords}"
                    )
                )));
            }
        }
        
        $this->customise(array(
            "MetaTitle" => _t(
                "Searchable.TopSearchResults",
                "Top Search Results for '{query}'",
                'This is the title used for viewing the top results of a search for each object',
                array('query' => $this->getQuery())
            ),
            "Objects" => $objects_list
        ));
     
        $this->extend("onBeforeIndex");
        
        return $this->renderWith(array(
            "SearchResults",
            "Page"
        ));
    }
    
    public function object() {
        $classname = $this->request->param("ID");
        $classes_to_search = Searchable::getObjects();
        
        foreach($classes_to_search as $object) {
            if($object["ClassName"] == $classname) $cols = $object["Columns"];
        }
        
        $keywords = $this->getQuery();
        
        $this->customise(array(
            "MetaTitle" => _t(
                'Searchable.SearchResultsFor',
                "Search Results for '{query}'",
                'This is the title used for viewing the results of a search',
                array('query' => $this->getQuery())
            ),
            "Results" => PaginatedList::create(
                Searchable::Results($classname, $cols, $keywords),
                $this->request
            )->setPageLength(Searchable::config()->page_length)
        ));
        
        $this->extend("onBeforeObject");
        
        return $this->renderWith(array(
            "SearchResults_{$classname}",
            "SearchResults_object",
            "SearchResults",
            "Page"
        ));
    }
}
