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
    
    /**
	 * Return DataList of the results using $_REQUEST to get search info
	 * Wraps around {@link searchEngine()}.
	 * 
     * @param $classname Name of the object we will be filtering
     * @param $columns an array of the column names we will be sorting
     * @param $query the current search query
     * 
	 * @return SS_List
	 */
	protected function Results($classname, $columns, $keywords, $limit = 0) {
        $cols_string = implode('","', $columns);

	 	$andProcessor = create_function('$matches','
	 		return " +" . $matches[2] . " +" . $matches[4] . " ";
	 	');
        
	 	$notProcessor = create_function('$matches', '
	 		return " -" . $matches[3];
	 	');

	 	$keywords = preg_replace_callback('/()("[^()"]+")( and )("[^"()]+")()/i', $andProcessor, $keywords);
	 	$keywords = preg_replace_callback('/(^| )([^() ]+)( and )([^ ()]+)( |$)/i', $andProcessor, $keywords);
		$keywords = preg_replace_callback('/(^| )(not )("[^"()]+")/i', $notProcessor, $keywords);
		$keywords = preg_replace_callback('/(^| )(not )([^() ]+)( |$)/i', $notProcessor, $keywords);
		
		$keywords = $this->addStarsToKeywords($keywords);
        
        $results = $classname::get()
            ->filter($cols_string . ':FullText', $keywords);
        
        if($limit) $results = $results->limit($limit);
        
		foreach($results as $result) {
			if(!$result->canView() || (isset($result->ShowInSearch) && !$result->ShowInSearch))
                $results->remove($result);
		}

		return $results;
	}

	protected function addStarsToKeywords($keywords) {
		if(!trim($keywords)) return "";
        
		// Add * to each keyword
		$splitWords = preg_split("/ +/" , trim($keywords));
		while(list($i,$word) = each($splitWords)) {
			if($word[0] == '"') {
				while(list($i,$subword) = each($splitWords)) {
					$word .= ' ' . $subword;
					if(substr($subword,-1) == '"') break;
				}
			} else {
				$word .= '*';
			}
			$newWords[] = $word;
		}
        
		return implode(" ", $newWords);
	}
    
    public function getQuery() {
        return $this->request->getVar('Search');
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
            $results = $this->Results($object["ClassName"], $object["Columns"], $keywords, $limit);
            
            if($results->exists()) {
                $objects_list->add(ArrayData::create(array(
                    "Title" => $object["Title"],
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
            "Results" => PaginatedList::create(
                $this->Results($classname, $cols, $keywords),
                $this->request
            )
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
