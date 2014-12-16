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
