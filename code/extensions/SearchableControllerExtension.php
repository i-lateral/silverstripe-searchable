<?php

class SearchableControllerExtension extends Extension
{

    private static $allowed_actions = array(
        "SearchForm",
        'results',
    );

    /**
     * Add a site search form to all controllers that links to the
     * results controller.
     *
     * @return Form
     */
    public function SearchForm()
    {
        // If we have setup objects to search
        if (count(Searchable::getObjects())) {
            $searchText =  "";

            if ($this->owner->request && $this->owner->request->getVar('Search')) {
                $searchText = $this->owner->request->getVar('Search');
            }

            $fields = FieldList::create(
                TextField::create('Search', false, $searchText)
                    ->setAttribute("placeholder", _t('Searchable.Search', 'Search'))
            );

            $actions = FieldList::create(
                FormAction::create('results', _t('Searchable.Go', 'Go'))
            );

            $template_class = Searchable::config()->template_class;
            $results_page = new $template_class;

            $form = Form::create($this->owner, 'SearchForm', $fields, $actions)
                ->setFormMethod('get')
                ->setFormAction($results_page->Link())
                ->disableSecurityToken();

            $this->owner->extend("updateSearchForm", $form);

            return $form;
        }
    }
}
