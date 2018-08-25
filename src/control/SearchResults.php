<?php

namespace ilateral\SilverStripe\Searchable\Control;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\CMS\Controllers\ContentController;
use ilateral\SilverStripe\Searchable\Searchable;
use SilverStripe\Subsites\Model\Subsite;

/**
 * Controller responsible for handling search results
 * 
 * @package Searchable
 */
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
    private static $allowed_actions = [
        "object"
    ];

    /**
     * Setup default templates for this controller
     *
     * @var array
     */
    protected $templates = [
        "index" => [SearchResults::class, "Page"],
        "object" => [SearchResults::class . "_object", SearchResults::class, "Page"]
    ];

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

    /**
     * If content controller exists, return it's menu function
     * @param int $level Menu level to return.
     * @return ArrayList
     */
    public function getMenu($level = 1)
    {
        if (class_exists(ContentController::class)) {
            $controller = ContentController::singleton();
            return $controller->getMenu($level);
        }
    }

    public function Menu($level)
    {
        return $this->getMenu();
    }

    /**
     * Overwrite default init to support subsites (if installed)
     * 
     * @return void 
     */
    protected function init()
    {
        parent::init();

        # Check for subsites and add support
        if (class_exists(Subsite::class)) {
            $subsite = Subsite::currentSubsite();

            if ($subsite && $subsite->Theme) {
                SSViewer::add_themes([$subsite->Theme]);
            }

            if ($subsite && i18n::getData()->validate($subsite->Language)) {
                i18n::set_locale($subsite->Language);
            }
        }
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

        return $this->render();
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

        // Add the current object classname to the start of the
        // templates array before render
        $templates = $this->templates["object"];
        array_unshift(
            $templates,
            SearchResults::class . "_{$classname}"
        );
        $this->templates["object"] = $templates;

        return $this->render();
    }
}
