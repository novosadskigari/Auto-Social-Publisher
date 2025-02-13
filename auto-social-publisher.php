<?php
/**
 * Plugin Name: Auto Social Publisher
 * Description: Choose which social networks to post to when publishing
 * Version: 1.0.0
 * Author: DamirB
 * License: GPL-3.0
 */

if (!defined('ABSPATH')) exit;

class Auto_Social_Publisher {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('add_meta_boxes', array($this, 'add_social_meta_box'));
        add_action('save_post', array($this, 'save_social_meta'));
        add_action('publish_post', array($this, 'handle_post_publish'));
    }

    public function add_social_meta_box() {
        add_meta_box(
            'social_publishing_options',
            'Social Publishing',
            array($this, 'render_meta_box'),
            'post',
            'side',
            'high'
        );
    }

    public function render_meta_box($post) {
        wp_nonce_field('social_meta_box', 'social_meta_box_nonce');
        $networks = array('facebook', 'twitter', 'linkedin');
        $selected = get_post_meta($post->ID, '_social_networks', true);
        
        echo '<div class="social-publish-options">';
        foreach ($networks as $network) {
            $checked = is_array($selected) && in_array($network, $selected) ? 'checked' : '';
            echo sprintf(
                '<label><input type="checkbox" name="social_networks[]" value="%s" %s> Post to %s</label><br>',
                esc_attr($network),
                $checked,
                ucfirst($network)
            );
        }
        echo '</div>';
    }

    public function save_social_meta($post_id) {
        if (!isset($_POST['social_meta_box_nonce'])) return;
        if (!wp_verify_nonce($_POST['social_meta_box_nonce'], 'social_meta_box')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $networks = isset($_POST['social_networks']) ? $_POST['social_networks'] : array();
        update_post_meta($post_id, '_social_networks', $networks);
    }

    public function handle_post_publish($post_id) {
        $networks = get_post_meta($post_id, '_social_networks', true);
        if (!empty($networks)) {
            foreach ($networks as $network) {
                $this->post_to_network($post_id, $network);
            }
        }
    }

    private function post_to_network($post_id, $network) {
        $post = get_post($post_id);
        $message = array(
            'title' => $post->post_title,
            'excerpt' => get_the_excerpt($post),
            'url' => get_permalink($post_id)
        );
        
        // Simulate posting to networks (for demonstration)
        error_log("Would post to $network: " . print_r($message, true));
    }
}

Auto_Social_Publisher::get_instance();
