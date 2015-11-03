<?php

function wp_us_cities_install() {
    global $wpdb;

    $table_name = $wpdb->prefix . "us_cities";

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          id int(5) NOT NULL AUTO_INCREMENT,
          name varchar(100) NOT NULL,
          state varchar(2) NOT NULL,
          slug varchar(100) NOT NULL,
          population int(9) NOT NULL,
          PRIMARY KEY  (id),
          FULLTEXT wp_us_city_name (name),
          KEY wp_us_city_population (population)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    dbDelta($sql);
}

function wp_us_cities_install_data() {
    global $wpdb;

    $table_name = $wpdb->prefix . "us_cities";

    $city_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    
    if($city_count < 54882){
        if($city_count > 0){
            $wpdb->query("TRUNCATE TABLE $table_name");
        }
        
        set_time_limit(3600);

        require_once(dirname( __FILE__ ) . '/data.php');

        $wpdb->query($sql_data);
    }
/*
    if(!has_action('location-search', 'wp_us_cities_open')){
        add_action('location-search', 'wp_us_cities_open');
    }*/
}

function wp_us_cities_deactivation(){
    /*if(has_action('location-search', 'wp_us_cities_open')){
        remove_action('location-search', 'wp_us_cities_open');
    }*/
}

function wp_us_cities_uninstall() {
    if(!defined('WP_UNINSTALL_PLUGIN')){
        exit();
    }
    
    global $wpdb;

    $table_name = $wpdb->prefix . "us_cities";
    
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
/*
    if(has_action('location-search', 'wp_us_cities_open')){
        remove_action('location-search', 'wp_us_cities_open');
    }*/
}

function wp_us_cities_create_rewrite_rules($rules){
    global $wp_rewrite;
    
    $newRule1 = array('search-city/(.+)' => 'index.php?search-city='.$wp_rewrite->preg_index(1));
    $newRule2 = array('location-search' => 'index.php?location-search=1');
    
    $newRules = $newRule1 + $newRule2 + $rules;
    return $newRules;
}

function wp_us_cities_add_query_vars($qvars) {
    $qvars[] = array('location-search','search-city');
    return $qvars;
}

function wp_us_cities_flush_rewrite_rules() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}

function wp_us_cities_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_style('styles', plugin_dir_url(dirname(__FILE__)) . '/assets/stylesheets/styles.css');
    wp_enqueue_script('script', plugin_dir_url(dirname(__FILE__)) . '/assets/scripts/script.js', array( 'jquery' ));
}

function wp_us_cities_template_redirect_intercept() {
    global $wp_query;
    if ($wp_query->get('location-search')) {
        get_header();
        
        echo '
            <div id="primary" class="content-area">
                <main id="main" class="site-main" role="main">
                    <article class="post-1 post type-post status-publish format-standard hentry category-uncategorized">
                        <header class="entry-header">
                            <h1 class="entry-title">Location Search</h1>
                        </header>
                        <div class="entry-content">
                            <p>
                                <div class="wp-city-container">
                                    <input type="text" name="city-term" class="wp-city-term" placeholder="City" slug="" path="' . plugin_dir_url(dirname(__FILE__)) . '">
                                    <div class="wp-city-output">
                                    </div>
                                </div>
                            </p>
                        </div>
                    </article>
                </main><!-- .site-main -->
            </div><!-- .content-area -->
        ';
        
//        get_sidebar();
        
        get_footer();
        //require_once('../home.php');
        exit;
    } elseif ($wp_query->get('search-city')) {
        wp_us_cities_search_city($wp_query->get('search-city'));
        exit;
    }
}

function wp_us_cities_search_city($term){
    if(strlen($term) >= 3){
        global $wpdb;
        
        $json_obj = array();
        $cities = array();

        $stmt = $wpdb->prepare( "SELECT "
                                    . "name, state, slug  "
                              . "FROM "
                                    . "{$wpdb->prefix}us_cities "
                              . "WHERE "
                                    . "LOWER(CONCAT(name, ', ', state)) like LOWER(%s) or "
                                    . "LOWER(CONCAT(name, ', ', state)) like LOWER(%s) "
                              . "ORDER BY "
                                    . "population desc "
                              . "LIMIT 10",
                        urldecode($term).'%',
                        '% '.urldecode($term).'%'
        );

        $results = $wpdb->get_results($stmt);

        if($results){
            $i = 0;

            foreach ( $results as $row ) {
                $city = array(
                    "city" => $row->name . ", " . $row->state,
                    "slug" => $row->slug
                );

                $cities[$i] = $city;

                $i++;
            }
        }

        $json_obj["cities"] = $cities;

        header('Content-Type: application/json');
        echo json_encode($json_obj);
    } else {
        header('HTTP/1.0 403 Forbidden');
    }

}
