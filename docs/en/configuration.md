## Configuration

To add your object to the search results, you need to make Searchable
aware of it. Do this by adding the following to your _config.php:

    Searchable::add(
        "ObjectClass",
        array("SearchCol1","SearchCol2"),
        "Friendly Title (for template"
    );

This will then add the object to Searchable's searchable classes.

For example, you can add SiteTree using the following:

    Searchable::add(
        "SiteTree",
        array("Title","MenuTitle","Content","URLSegment"),
        "Pages"
    );

**NOTE** Searchable will check an object's canView method before 
adding it to the list of results. If this returns true (the 
Silverstripe default for users not logged in) then the object
will not appear.

If you do not require custom view permissions, then the simplest
thing to do is add the following function to your dataobject:

    function canView($member = null)
    {
        return true;
    }
    
### Extended Dataobjects

At the moment Searchable generates errors if you want to try and 
search an object that extends another object using the fields of 
it's parent.

For example, the below will generate an error:

    Searchable::add(
        "Page",
        array("Title","MenuTitle"),
        "Pages"
    );
    
You will have to search SiteTree (as it contains the fields Title and
Menutitle).

### Overwrite the default page length

You can change the default page length of search results by using
configuration:

    Searchable.page_lenth = 20;

Or, in config.yml

    Searchable:
      page_lenth: 20

## Custom results controller

It's possible to change the default controller used by searchabe to
display results. You can do this by changing the template class
configuration variable:
	
    Searchable.template_class = 'your_custom_controller';
    
EG, in config.yml

    Searchable:
      template_class: 'your_custom_controller'