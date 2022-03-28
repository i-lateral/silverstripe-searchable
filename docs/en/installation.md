# Installation

You can install this module either via composer or manually. Via composer:

    composer require i-lateral/silverstripe-searchable

Alternativley install this module either by downloading and adding
it to:

    [silverstripe-root]/searchable

Finally run (either via the browser or command line):

    dev/build flush=1

## Importing Search Data

Running a dev build should import all search data into the search table (which is needed
to perform accurate searches). If this doesn't happen (or you need to import data manually)
there is a buid task that you can run:

    sake dev/tasks/ImportSearchDataTask

## Add Search Form

Once installed, you will need to ensure you add the search form to your templates. This should be possible on any `Controller` using:

    $SearchForm