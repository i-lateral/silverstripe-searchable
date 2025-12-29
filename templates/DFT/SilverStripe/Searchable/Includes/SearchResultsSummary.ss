<div class="search-results-item">
    <h2><a href="$Link">$Title</a></h2>

    <% if $Content %>
        <p>
            $Content.FirstParagraph
            <a class="read-more-link" href="$Link">
                <%t Searchable.ReadMore "Read More About '{title}'" title=$Title %>
            </a>
        </p>
    <% end_if %>
</div>