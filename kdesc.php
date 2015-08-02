<?php
/*
Plugin Name: KDESC
Plugin URI: http://example.com/wordpress-plugins/my-plugin
Description: Custom keywords and description for tags and category.
Version: 0.1
Author: Your mom
Author URI: http://somegitlink.com
License: Propietary
*/

function kdesc_add_meta($name, $content) {
  echo '<meta name="'.$name.'" itemprop="'.$name.'" content="'.$content.'">';
}

add_action( 'wp_head_add_meta', 'kdesc_add_meta', 10, 2);

add_action( 'wp_head', function() {

  if (is_tag()) {

    global $wpdb;
    $kdesc_tax_id = get_query_var('tag_id');

    $kdesc_get_data = $wpdb->get_row("
      SELECT description, keywords FROM $wpdb->term_taxonomy
      WHERE term_taxonomy_id = $kdesc_tax_id
    ");
  }

  if (is_category()) {

    global $wpdb;
    $kdesc_tax_id = get_query_var('cat');

    $kdesc_get_data = $wpdb->get_row("
      SELECT description, keywords FROM $wpdb->term_taxonomy
      WHERE term_taxonomy_id = $kdesc_tax_id
    ");
  }

  if (is_category() || is_tag()) {
    do_action('wp_head_add_meta', 'keywords', $kdesc_get_data->keywords);
    do_action('wp_head_add_meta', 'description', $kdesc_get_data->description);
  }
} );

/*
 * Добавить поле
 * ALTER TABLE  `wp_term_taxonomy` ADD  `keywords` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER  `description`
 */
