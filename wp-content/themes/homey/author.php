<?php
get_header();
global $wp_query, $homey_local, $homey_prefix;
$current_author = $wp_query->get_queried_object();
$author_id = $current_author->ID;

if(homey_is_renter($author_id)) {
    get_template_part('template-parts/profile/guest');
} else {
    get_template_part('template-parts/profile/host');
}

get_footer(); 
?>
