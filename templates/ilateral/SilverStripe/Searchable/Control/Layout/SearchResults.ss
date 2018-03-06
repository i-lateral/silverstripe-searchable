<div class="search-results col-sm-12 content-container">
    <h1><%t Searchable.TopSearchResults "Top Search Results for '{query}'" query=$Query %></h1>

    <% if $Objects.exists %>
        <p class="search-query">
            <%t Searchable.TopSearchExplination "Below are the top results for your search, click 'View all results' to see more results of that type." %>
        </p>

        <% loop $Objects %>
            <h2>$Title</h2>

            <div class="search-results-list {$ClassName} line">
                <div class="unit size4of4">
                    <% loop $Results %>
                        <% include ilateral\SilverStripe\Searchable\SearchResultsSummary %>
                    <% end_loop %>

                    <p>
                        <a class="view-all-link btn btn-primary" href="{$Link}">
                            <%t Searchable.ViewAll "View all results" %>
                        </a>
                    </p>
                </div>
            </div>

            <hr/>
        <% end_loop %>
    <% else %>
        <p><%t Searchable.NoResults "Sorry, your search did not return any results." %></p>
    <% end_if %>
</div>
