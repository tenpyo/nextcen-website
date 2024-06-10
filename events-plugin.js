jQuery(document).ready(function($) {
    // Function to fetch events based on search and category
    function fetchEvents() {
        var search = $('#event_search').val();
        var category = $('#event_category_filter').val();

        // Data to be sent via AJAX request
        var data = {
            action: 'filter_events',
            search: search,
            category: category,
        };

        // AJAX POST request to filter events
        $.post(ajaxurl, data, function(response) {
            var eventsTable = $('#events_table tbody');
            eventsTable.empty();

            // Populate events table with retrieved data
            if (response.length) {
                $.each(response, function(index, event) {
                    // Construct table rows for each event
                    var categories = event.categories.map(function(cat) {
                        return cat.name;
                    }).join(', ');

                    var row = '<tr>';
                    row += '<td data-label="Title">' + event.title + '</td>';
                    row += '<td data-label="Date">' + event.date + '</td>';
                    row += '<td data-label="Location">' + event.location + '</td>';
                    row += '<td data-label="Organizer">' + event.organizer + '</td>';
                    row += '<td data-label="Categories">' + categories + '</td>';
                    row += '</tr>';

                    eventsTable.append(row);
                });
            } else {
                // Display message if no events found
                eventsTable.append('<tr><td colspan="5">No events found</td></tr>');
            }
        });
    }

    // Event listeners for filtering events
    $('#filter_button').on('click', fetchEvents);
    $('#event_search').on('keyup', fetchEvents);

    // Fetch events on page load
    fetchEvents();
});
