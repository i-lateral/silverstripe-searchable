<div class="search-results col-sm-12 content-container">
    <h1><%t Searchable.SearchResultsFor "Search Results for '{query}'" query=$Query %></h1>

    <% if $Results %>
        <div class="search-results-list">
            <% loop $Results %>
                <% include ilateral\SilverStripe\Searchable\SearchResultsSummary %>
            <% end_loop %>

            <% with $Results %>
                <% if $MoreThanOnePage %>
                    <ul class="pagination">
                        <% if $NotFirstPage %>
                            <li><a class="prev" href="{$PrevLink}">&larr;</a></li>
                        <% end_if %>

                        <% loop $Pages %>
                            <% if $CurrentBool %>
                                <li><span>$PageNum</span></li>
                            <% else %>
                                <% if $Link %>
                                    <li><a href="$Link">$PageNum</a></li>
                                <% else %>
                                    <li><span>...</span></li>
                                <% end_if %>
                            <% end_if %>
                        <% end_loop %>

                        <% if $NotLastPage %>
                            <li><a class="next" href="{$NextLink}">&rarr;</a></li>
                        <% end_if %>
                    </ul>
                <% end_if %>
            <% end_with %>
        </div>
    <% else %>
        <p><%t Searchable.NoResults "Sorry, your search did not return any results." %></p>
    <% end_if %>
</div>
