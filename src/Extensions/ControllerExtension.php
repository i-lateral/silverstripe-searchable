<?php

namespace ilateral\SilverStripe\Searchable\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FormAction;
use ilateral\SilverStripe\Searchable\Searchable;

/**
 * Add SearchForm to controllers
 * 
 * @package Searchable
 */
class ControllerExtension extends Extension
{

    /**
     * Add a site search form to all controllers that links to the
     * results controller.
     *
     * @return Form
     */
    public function SearchForm()
    {
        // If we have setup objects to search
        if (count(Searchable::config()->objects)) {
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
                    ->setUseButtonTag(true)
                    ->setTemplate('ilateral\SilverStripe\Searchable\Forms\SearchButton')
            );

            $template_class = Searchable::config()->template_class;
            $results_page = Injector::inst()->create($template_class);

            $form = Form::create(
                $this->owner,
                'SearchForm',
                $fields,
                $actions
            )->setFormMethod('get')
            ->setFormAction($results_page->Link())
            ->setTemplate('ilateral\SilverStripe\Searchable\Includes\SearchForm')
            ->disableSecurityToken();

            $this->owner->extend("updateSearchForm", $form);

            return $form;
        }
    }
}
