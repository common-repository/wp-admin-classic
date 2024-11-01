<?php
/*
Plugin Name: WP Admin Classic
Author URI: http://mindomobile.com
Plugin URI: http://wordpress.org/plugins/wp-admin-classic/
Description: Classic WordPress Admin theme (prior 3.8 style).
Version: 1.0.8
Author: Mindo Mobile
License: GPL2
*/

class wp_admin_classic
{
    function __construct()
    {        
        // Dequeue new & enqueue "classic" styles
        add_action('admin_enqueue_scripts', array($this, 'wpadmin_classic'));
        add_action('admin_enqueue_scripts', array($this, 'remove_wpadmin_styles'), 20);
        
        // Admin bar action handler
        add_action('admin_enqueue_scripts', array($this, 'admin_bar'), 100);
        add_action('wp_enqueue_scripts', array($this, 'admin_bar'), 100);
        
        // WP NAV Shadow
        add_action('admin_footer', array($this, 'fix_wp_shadow'));
        
        // Remove Admin Color Scheme from user edit page
        remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
        
        // User profile hooks
        add_action('show_user_profile', array(&$this, 'user_profile_details'));
        add_action('edit_user_profile', array(&$this, 'user_profile_details'));
        add_action('personal_options_update', array(&$this,'user_profile_details_save'));
        add_action('edit_user_profile_update', array(&$this,'user_profile_details_save'));
    }
    
    public function user_profile_details($user)
    {
        // USER DATA
        $user_color_scheme = esc_attr(get_the_author_meta('wp_admin_class_color_scheme', $user->ID));
        $options = array('grey'=>'Grey', 'blue'=>'Blue');
        
        // Sociallos User information
        $html =
            '<table class="form-table">'.
                '<tr>'.
                  '<th><label for="wp_admin_class_color_scheme">Color scheme</label></th>'.
                  '<td>'.  
                    '<select name="wp_admin_class_color_scheme" id="wp_admin_class_color_scheme">';
        
                        foreach($options as $key => $value) :
                            $selected = ($key == $user_color_scheme)?' selected="selected"':'';
                            $html .= '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
                        endforeach;
        
        $html .=      '</select>'.
                  '</td>'.
                '</tr>'.
            '</table>';
    
        echo $html;
    }
    
    public function user_profile_details_save($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
          return false;
        }
        
        update_user_meta($user_id, 'wp_admin_class_color_scheme', $_POST['wp_admin_class_color_scheme']);
    }
    
    function fix_wp_shadow()
    {
        ?>
        <script>
        jQuery(document).ready( function($) {
            $("#adminmenuwrap").prepend("<div id='adminmenushadow'></div>");
        });
        </script>
        <?php
    }
    
    function wpadmin_classic()
    {
        // Get user color scheme
        $user_color_scheme = esc_attr(get_the_author_meta('wp_admin_class_color_scheme', wp_get_current_user()->ID));
        $user_color_scheme  = (!in_array($user_color_scheme, array('grey', 'blue')))?'grey':$user_color_scheme;
        
        // Load main stylesheet
        $GLOBALS['wp_styles']->add('wp-admin-classic', plugin_dir_url( __FILE__ ) . 'wp-admin-css/wp-admin.min.css');
    
        // Conditional IE stylesheet
        $GLOBALS['wp_styles']->add('ie-classic', plugin_dir_url( __FILE__ ) . 'wp-admin-css/ie.min.css');
        $GLOBALS['wp_styles']->add_data('ie-classic', 'conditional', 'lte IE 7');
        
        // Register other styles
        wp_register_style('buttons-classic', plugin_dir_url( __FILE__ ) . 'wp-includes-css/buttons.min.css');
        wp_register_style('colors-classic-classic', plugin_dir_url( __FILE__ ) . 'wp-admin-css/colors-classic.min.css');
        wp_register_style('colors-fresh-classic', plugin_dir_url( __FILE__ ) . 'wp-admin-css/colors-fresh.min.css'); 
        wp_register_style('editor-buttons-classic', plugin_dir_url( __FILE__ ) . 'wp-includes-css/editor.min.css');
        wp_register_style('media-classic', plugin_dir_url( __FILE__ ) . 'wp-admin-css/media.min.css');
        wp_register_style('media-views-classic', plugin_dir_url( __FILE__ ) . 'wp-includes-css/media-views.min.css');

        // Enqueu styles
        wp_enqueue_style('wp-admin-classic');
        wp_enqueue_style('buttons-classic');
        
        // Load color scheme based on global scheme option
        if ($user_color_scheme == 'grey') {
            wp_enqueue_style('colors-classic-classic', true, array('wp-admin-classic', 'buttons-classic'));
            wp_enqueue_style('colors-fresh-classic', true, array('wp-admin-classic', 'buttons-classic'));
        } else {
            wp_enqueue_style('colors-fresh-classic', true, array('wp-admin-classic', 'buttons-classic'));
            wp_enqueue_style('colors-classic-classic', true, array('wp-admin-classic', 'buttons-classic'));
        }
        
        wp_enqueue_style('editor-buttons-classic');
        wp_enqueue_style('media-classic');
        wp_enqueue_style('media-views-classic');
        wp_enqueue_style('ie-classic');
    }
    
    function remove_wpadmin_styles()
    {
        // Dequeu styles
        wp_dequeue_style('wp-admin');
        wp_dequeue_style('ie');
        wp_dequeue_style('colors');
        wp_dequeue_style('buttons');
        wp_dequeue_style('media-views');
        wp_dequeue_style('colors-fresh');
        wp_dequeue_style('media');
    }
    
    function admin_bar()
    {
        // Remove existing
        wp_dequeue_style('admin-bar');
        
        // Register and add "legacy" stylesheet
        wp_register_style('admin-bar-classic', plugin_dir_url( __FILE__ ) . 'wp-includes-css/admin-bar.min.css');
        wp_enqueue_style('admin-bar-classic');
    }
}

$wp_admin_classic = new wp_admin_classic();
?>