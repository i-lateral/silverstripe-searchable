# Installation

You can install this module either via composer or manually. Via composer:

    composer require DFT/silverstripe-searchable

Alternativley install this module either by downloading and adding
it to:

    [silverstripe-root]/searchable

Next, before running a `dev/build`, you will need to add at least some
basic config to tell Searchable what to index. More details can be found
via the [configuration](./configuration.md) docs, but a basic example
would be:

    DFT\SilverStripe\Searchable\Searchable:
      objects:
        "Page": ["Title","MenuTitle","Content","URLSegment"]

This will then add the object to Searchable's searchable classes.

Finally re-build the database, either via the browser or command line:

    dev/build flush=1

## Importing Search Data

Running a dev build should import all search data into the search table (which is needed
to perform accurate searches). If this doesn't happen (or you need to import data manually)
there is a buid task that you can run:

    sake dev/tasks/ImportSearchDataTask

## Add Search Form

Once installed, you will need to ensure you add the search form to your templates. This should be possible on any `Controller` using:

    $SearchForm