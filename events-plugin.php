<?php
/*
Plugin Name: Events Plugin
Description: A plugin to create a custom post type for Events.
Version: 1.1
Author: Your Name
*/

// Function to create custom post type for Events
function create_events_post_type() {
    register_post_type('events',
        array(
            'labels'      => array(
                'name'          => __('Events'),
                'singular_name' => __('Event'),
            ),
            'public'      => true,
            'has_archive' => true,
            'supports'    => array('title', 'editor', 'thumbnail'),
            'menu_icon'   => 'dashicons-calendar',
        )
    );
}
add_action('init', 'create_events_post_type');

// Function to add meta box for Event Details
function add_events_meta_boxes() {
    add_meta_box(
        'event_details',
        'Event Details',
        'render_event_details_meta_box',
        'events',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_events_meta_boxes');

// Function to render Event Details meta box
function render_event_details_meta_box($post) {
    wp_nonce_field('save_event_details', 'event_details_nonce');
    $event_date = get_post_meta($post->ID, '_event_date', true);
    $event_location = get_post_meta($post->ID, '_event_location', true);
    $event_organizer = get_post_meta($post->ID, '_event_organizer', true);
    ?>
    <!-- HTML form fields for event details -->
    <p>
        <label for="event_date">Event Date:</label>
        <input type="date" id="event_date" name="event_date" value="<?php echo esc_attr($event_date); ?>" />
    </p>
    <p>
        <label for="event_location">Event Location:</label>
        <input type="text" id="event_location" name="event_location" value="<?php echo esc_attr($event_location); ?>" />
    </p>
    <p>
        <label for="event_organizer">Event Organizer:</label>
        <input type="text" id="event_organizer" name="event_organizer" value="<?php echo esc_attr($event_organizer); ?>" />
    </p>
    <?php
}

// Function to save Event Details meta box data
function save_event_details($post_id) {
    if (!isset($_POST['event_details_nonce']) || !wp_verify_nonce($_POST['event_details_nonce'], 'save_event_details')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Update event details meta fields
    if (isset($_POST['event_date'])) {
        update_post_meta($post_id, '_event_date', sanitize_text_field($_POST['event_date']));
    }

    if (isset($_POST['event_location'])) {
        update_post_meta($post_id, '_event_location', sanitize_text_field($_POST['event_location']));
    }

    if (isset($_POST['event_organizer'])) {
        update_post_meta($post_id, '_event_organizer', sanitize_text_field($_POST['event_organizer']));
    }
}
add_action('save_post', 'save_event_details');

// Function to create taxonomy for event categories
function create_event_taxonomy() {
    register_taxonomy(
        'event_category',
        'events',
        array(
            'label' => __( 'Event Categories' ),
            'rewrite' => array( 'slug' => 'event-category' ),
            'hierarchical' => true,
        )
    );
}
add_action( 'init', 'create_event_taxonomy' );

// Function to display upcoming events using a shortcode
function display_upcoming_events($atts) {
    $atts = shortcode_atts(array(
        'category' => '',
    ), $atts, 'upcoming_events');

    // Output HTML for displaying upcoming events
    ob_start();
    ?>
    <!-- HTML form for filtering events -->
    <form id="event-filter">
        <input type="text" id="event_search" placeholder="Search Events">
        <select id="event_category_filter">
            <option value="">All Categories</option>
            <?php
            // Retrieve and display event categories
            $categories = get_terms(array(
                'taxonomy' => 'event_category',
                'hide_empty' => false,
            ));
            foreach ($categories as $category) {
                echo '<option value="' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</option>';
            }
            ?>
        </select>
        <button type="button" id="filter_button">Filter</button>
    </form>
    <!-- Table for displaying event details -->
    <table id="events_table" class="events-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Date</th>
                <th>Location</th>
                <th>Organizer</th>
                <th>Categories</th>
            </tr>
        </thead>
        <tbody>
            <!-- Content will be filled by JavaScript -->
        </tbody>
    </table>
    <?php
    $output = ob_get_clean();
    return $output;
}
add_shortcode('upcoming_events', 'display_upcoming_events');

// Function to enqueue plugin styles and scripts
function enqueue_event_plugin_styles() {
    wp_enqueue_style('events-plugin-style', plugins_url('events-plugin.css', __FILE__));
    wp_enqueue_script('events-plugin-script', plugins_url('events-plugin.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('events-plugin-script', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action('wp_enqueue_scripts', 'enqueue_event_plugin_styles');

// Function to filter events based on search and category
function filter_events() {
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

    // Query to retrieve events based on search and category
    $args = array(
        'post_type' => 'events',
        'meta_key'  => '_event_date',
        'orderby'   => 'meta_value',
        'order'     => 'ASC',
        's'         => $search,
    );

    if (!empty($category)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'event_category',
                'field'    => 'slug',
                'terms'    => $category,
            ),
        );
    }

    $query = new WP_Query($args);

    // Prepare event data for JSON response
    $events = array();
    while ($query->have_posts()) : $query->the_post();
        $event_date = get_post_meta(get_the_ID(), '_event_date', true);
        $event_location = get_post_meta(get_the_ID(), '_event_location', true);
        $event_organizer = get_post_meta(get_the_ID(), '_event_organizer', true);
        $event_categories = get_the_terms(get_the_ID(), 'event_category');

        $events[] = array(
            'title' => get_the_title(),
            'date' => $event_date
