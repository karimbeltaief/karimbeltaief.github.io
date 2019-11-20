<?php
/**
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 08/08/16
 * Time: 10:38 PM
 */

if( !function_exists('homey_add_theme_caps') ) {
    function homey_add_theme_caps()
    {
        


        // gets the author role
        $role = get_role('homey_agent');

        $role->add_cap( 'edit_posts' ); // edit own posts
        $role->add_cap('delete_posts'); // delete own posts
        $role->add_cap('publish_posts'); // publish own posts

        $role->add_cap('upload_files');
        $role->add_cap('read_property');
        $role->add_cap('delete_property');
        $role->add_cap('edit_property');
        $role->add_cap('edit_properties');
        $role->add_cap('edit_published_properties');
        $role->add_cap('delete_published_properties');
        $role->remove_cap('read_private_properties');
        $role->remove_cap('delete_private_properties');
        $role->remove_cap('edit_others_properties');
        $role->remove_cap('delete_others_properties');
        $role->remove_cap('edit_private_properties');

        $role->add_cap('create_testimonials');

        $role->add_cap('read_testimonial');
        $role->add_cap('delete_testimonial');
        $role->add_cap('edit_testimonial');
        // $role->add_cap( 'delete_testimonial' );
        $role->remove_cap('publish_testimonials');
        $role->remove_cap('edit_testimonials');
        $role->remove_cap('edit_published_testimonials');
        $role->remove_cap('delete_published_testimonials');

    }

    add_action('admin_init', 'homey_add_theme_caps');
}
?>