# Templates

Searchable contains several templates

## Results Dashboard

As Searchable can display results for multiple types of object, the
default interface used to display these objects is the "dashboard".

The dashboard displays a summary of results for each object and
generates a view more link to display more of that type of post
(for example, more products, more forum posts, etc).

You can customise how many items are displayed on the dashboard with
the config variable:

    Searchable.dashboard_items

If searchable is only setup for one type of object, the dashboard
will be disabled and instead the user will be taken directly to a
search results page for that object.

## Custom search templates

By default the search results are rendered into two templates:

    SearchResults.ss
    
Or

    SearchResults_object.ss

The former controls the apperance of the dashboard, the latter
controls the apperance of the results for a particular object.

You can further customise how the results appear on an object 
specific bases by adding a template named after the object in
question, for example:

    SearchResults_Product.ss
    
The above would be used to display only search results for a product
object.

## Search Form

The search form uses a dedicated `SearchForm` template, allowing for for customisation
of the styling.

You may also want to style the search button itself, so to aid with that, the search
button uses it's own custom template `SearchButton`.