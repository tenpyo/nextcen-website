# nextcen-website
Custom post type plugin called "Events" and implemented to site called nextcen

This plugin is a custom post type plugin with specific custom fields such as Event Date: A date picker field for selecting the event date, Event Location: A text field for specifying the event location, and Event Organizer: A text field for specifying the event organizer.
Additionally, there are some features added to the plugin such as custom taxonomy for event categories (e.g., Webinars, Workshops, Conferences), event filtering and search functionality

Before use this plugin:
1. Choose stable hosting and nice domain name

Plugin usage instructions:
1. Install latest wordpress
2. Install Gutenverse plugin (additional features for gutenberg or use other plugins)
3. Download plugin files and install to wordpress via upload plugin
4. add or customize event date, location, organizer and categories inside wordpress admin backend
5. Use this shortcode [upcoming_events] to display result of plugin everywhere

Theme usage instructions:
1. Install Kadence theme or use other theme as parent theme
2. Create child theme and includes functions.php, separate style.css and screenshoot of child theme(for aesthetic part)
3. Create a page template and put shortcode [upcoming_events] to use events-plugin
4. Install Smartslider 3 for slider feature
