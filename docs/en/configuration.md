## Configuration

To add your object to the search results, you need to make Searchable
aware of it. Do this by using the `objects` config variable:

For example, in config.yml

    Searchable:
      objects:
        "Page": ["Title","MenuTitle","Content","URLSegment"]

This will then add the object to Searchable's searchable classes.

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

## Custom Filters

If you want to specify custom filters that can be associated with
an object you are searching, you can, using the
`Searchable.custom_filters` config variable.

For example if we are searching a "Product" object and want to only
show objects that have Disabled set to 0, we would add the following
to our config.yml

    Searchable:
      custom_filters:
        Product:
          "Disabled": 0

## Overwrite the default page length

You can change the default page length of search results by using
configuration:

    Searchable.page_lenth = 20;

For example, in config.yml

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