<div class="search-results">
    <h1><%t Searchable.SearchResults "Search Results" %></h1>

    <% if $Query %>
        <p class="search-query"><%t Searchable.YouSearchedFor "You searched for" %> &quot;{$Query}&quot;</p>
    <% end_if %>

    <% if $Objects.exists %>
        <% loop $Objects %>
            <h2>$Title</h2>
            
            <div class="search-results-list">
                <% loop $Results %>
                    <div class="search-results-item">
                        <h2><a href="$Link">$Title</a></h2>
                        
                        <% if $Content %>
                            <p>$Content.FirstParagraph</p>
                        <% end_if %>
                        
                        <p>
                            <a class="read-more-link btn" href="$Link">
                                <%t Searchable.ReadMore "Read More" %>
                            </a>
                        </p>
                    </div>
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
