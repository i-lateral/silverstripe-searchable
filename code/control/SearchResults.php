<?php

class SearchResults extends Controller
{

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
    public static $allowed_actions = array(
        "object"
    );

    public function getQuery()
    {
        return $this->request->getVar('Search');
    }

    public function Link($action = null)
    {
        return Controller::join_links(
            $this->config()->url_segment,
            $action
        );
    }

    public function AbsoluteLink($action = null)
    {
        return Controller::join_links(
            Director::absoluteBaseURL(),
            $this->Link($action)
        );
    }

    public function index()
    {
        $keywords = $this->getQuery();
        $limit = Searchable::config()->dashboard_items;
        $classes_to_search = Searchable::config()->objects;
        $objects_list = ArrayList::create();

        // If only one class available, redirect to the object
        // action
        if (count($classes_to_search) == 1) {
            reset($classes_to_search);
            $classname = key($classes_to_search);
            return $this->redirect(Controller::join_links(
                $this->Link("object"),
                $classname,
                "?Search={$keywords}"
            ));
        }

        foreach ($classes_to_search as $classname => $cols) {
            $results = Searchable::Results(
                $classname,
                $cols,
                $keywords,
                $limit
            );

            if ($results->exists()) {
                $objects_list->add(ArrayData::create([
                    "Title" => _t($classname.".PLURALNAME", $classname),
                    "ClassName" => $classname,
                    "Results" => $results,
                    "Link" => Controller::join_links(
                        $this->Link("object"),
                        $classname,
                        "?Search={$keywords}"
                    )
                ]));
            }
        }

        $this->customise(array(
            "MetaTitle" => _t(
                "Searchable.TopSearchResults",
                "Top Search Results for '{query}'",
                'This is the title used for viewing the top results of a search for each object',
                ['query' => $this->getQuery()]
            ),
            "Objects" => $objects_list
        ));

        $this->extend("onBeforeIndex");

        return $this->renderWith(array(
            "SearchResults",
            "Page"
        ));
    }

    public function object()
    {
        $class_param = $this->request->param("ID");
        $classes_to_search = Searchable::config()->objects;
        $page_length = Searchable::config()->page_length;
        $cols = [];
        $classname = null;

        foreach ($classes_to_search as $search_class => $columns) {
            if ($class_param == $search_class) {
                $classname = $search_class;
                $cols = $columns;
            }
        }
        
        if (!count($cols)) {
            return $this->httpError(
                500,
                "No searchable classes configured"
            );
        }

        $keywords = $this->getQuery();

        $this->customise([
            "MetaTitle" => _t(
                'Searchable.SearchResultsFor',
                "Search Results for '{query}'",
                'This is the title used for viewing the results of a search',
                ['query' => $this->getQuery()]
            ),
            "Results" => PaginatedList::create(
                Searchable::Results($class_param, $cols, $keywords),
                $this->request
            )->setPageLength($page_length)
        ]);

        $this->extend("onBeforeObject");

        return $this->renderWith(array(
            "SearchResults_{$classname}",
            "SearchResults_object",
            "SearchResults",
            "Page"
        ));
    }
}
