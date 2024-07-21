<?php

namespace Search_Logger;

class Search_Logger {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        if ( defined( 'SEARCH_LOGGER_VERSION' ) ) {
            $this->version = SEARCH_LOGGER_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'search-logger';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-search-logger-loader.php';
        $this->loader = new Search_Logger_Loader();
    }

    private function define_admin_hooks() {
        $this->loader->add_action('admin_menu', $this, 'add_admin_menu');
        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_tablesorter_assets');
        $this->loader->add_action('admin_post_handle_search_logger_requests', $this, 'handle_search_logger_requests');
    }

    private function define_public_hooks() {
        $this->loader->add_action('wp', $this, 'log_search_query');
    }

    public function add_admin_menu() {
        add_menu_page(
            'Search Logger', 
            'Search Logger', 
            'manage_options', 
            'search-logger', 
            array($this, 'search_logger_admin_page'),
            'dashicons-search',
            6
        );
    }

    public function enqueue_tablesorter_assets($hook) {
        if ($hook !== 'toplevel_page_search-logger') {
            return;
        }
        wp_enqueue_script('jquery');
        wp_enqueue_script('tablesorter', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.3/js/jquery.tablesorter.min.js', array('jquery'), '2.31.3', true);
        wp_enqueue_style('tablesorter-style', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.3/css/theme.default.min.css', array(), '2.31.3');
    }

    public function search_logger_admin_page() {
        ?>
        <div class="wrap">
            <h1>Search Logger</h1>
            <p>Below is the CSV file containing all search queries:</p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php
                wp_nonce_field('delete_csv_nonce');
                $csv_file = plugin_dir_path(__FILE__) . '../search-logs.csv';
                if (file_exists($csv_file)) {
                    echo '<p><input type="submit" name="download_csv" class="button button-primary" value="Download CSV" /></p>';
                    echo '<p><input type="submit" name="delete_csv" class="button button-secondary" value="Delete CSV" /></p>';
                    $last_downloaded = get_option('search_logger_last_downloaded');
                    if ($last_downloaded) {
                        echo '<p>Last downloaded: ' . esc_html($last_downloaded) . '</p>';
                    }
                    echo '<h2>Search Logs</h2>';
                    echo '<table id="search-logs-table" class="wp-list-table widefat fixed striped">';
                    echo '<thead><tr><th>Date</th><th>Search Query</th></thead>';
                    echo '<tbody>';

                    $file = fopen($csv_file, 'r');
                    $header = fgetcsv($file);
                    while ($row = fgetcsv($file)) {
                        echo '<tr>';
                        foreach ($row as $cell) {
                            echo '<td>' . esc_html($cell) . '</td>';
                        }
                        echo '</tr>';
                    }
                    fclose($file);

                    echo '</tbody></table>';
                } else {
                    echo '<p>No search logs found.</p>';
                }
                ?>
                <input type="hidden" name="action" value="handle_search_logger_requests">
            </form>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $("#search-logs-table").tablesorter();
            });
        </script>
        <?php
    }

    public function handle_search_logger_requests() {
        if (isset($_POST['download_csv']) && check_admin_referer('delete_csv_nonce')) {
            $csv_file = plugin_dir_path(__FILE__) . '../search-logs.csv';
            if (file_exists($csv_file)) {
                update_option('search_logger_last_downloaded', current_time('mysql'));
                header('Content-Type: application/csv');
                header('Content-Disposition: attachment; filename="search-logs.csv"');
                header('Pragma: no-cache');
                readfile($csv_file);
                exit;
            }
        }

        if (isset($_POST['delete_csv']) && check_admin_referer('delete_csv_nonce')) {
            $csv_file = plugin_dir_path(__FILE__) . '../search-logs.csv';
            if (file_exists($csv_file)) {
                unlink($csv_file);
                update_option('search_logger_last_downloaded', '');
                wp_safe_redirect(admin_url('admin.php?page=search-logger&deleted=1'));
                exit;
            }
        }

        wp_safe_redirect(admin_url('admin.php?page=search-logger'));
        exit;
    }

    public function log_search_query() {
        if (is_search() && !is_admin()) {
            $search_query = get_search_query();
            if (!empty($search_query)) {
                $sanitized_search_query = sanitize_text_field($search_query);
                $csv_file = plugin_dir_path(__FILE__) . '../search-logs.csv';
                $csv_data = array(
                    array(date('Y-m-d H:i:s'), $sanitized_search_query)
                );

                $file_exists = file_exists($csv_file);

                $file = fopen($csv_file, 'a');
                if (!$file_exists) {
                    fputcsv($file, array('Date', 'Search Query'));
                }
                foreach ($csv_data as $fields) {
                    fputcsv($file, $fields);
                }
                fclose($file);
            }
        }
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }
}

?>
