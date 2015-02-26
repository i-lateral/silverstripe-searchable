<div class="search-results">
    <h1><%t Searchable.TopSearchResults "Top Search Results for '{query}'" query=$Query %></h1>

    <% if $Objects.exists %>
        <p class="search-query">
            <%t Searchable.TopSearchExplination "Below are the top results for your search, click 'View all results' to see more results of that type." %>
        </p>
    
        <% loop $Objects %>
            <h2>$Title</h2>
            
            <div class="search-results-list {$ClassName}">
                <% loop $Results %>
                    <% include SearchResultsSummary %>
                <% end_loop %>
                
                <p>
                    <a class="view-all-link btn" href="{$Link}">
                        <%t Searchable.ViewAll "View all results" %>
                    </a>
                </p>
            </div>
            
            <hr/>
        <% end_loop %>
    <% else %>
        <p><%t Searchable.NoResults "Sorry, your search did not return any results." %></p>
    <% end_if %>
</div>
