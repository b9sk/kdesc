<?php
/*
Plugin Name: KDESC
Plugin URI: http://example.com/wordpress-plugins/my-plugin
Description: Custom keywords and description for tags and category.
Version: 0.2
Author: Your mom
Author URI: http://somegitlink.com
License: Free
*/

/**
 * Дополнительные поле keywords для tag и category
 */

require_once("Tax-meta-class/Tax-meta-class.php");

if (is_admin()) {

  $kdesc_config = array(
    'id' => 'kdesc_keywords_meta_box',
    'title' => 'Add Keywords',
    'pages' => array('category', 'post_tag'),
    'context' => 'normal',
    'fields' => array(),
    'use_with_theme' => false,
  );

  $kdesc_field =  new Tax_Meta_Class( $kdesc_config );

  $kdesc_field->addText( 'm_kdesc_keywords', array('name'=> __('Keywords', 'tax-meta'), 'desc' => 'Custom keywords for this taxonomy.' ) );

  $kdesc_field->Finish();
}


/**
 * Вывод keywords и description
 */

function kdesc_add_meta( $name, $content ) {
  if ( $content )
    echo '<meta name="'.$name.'" itemprop="'.$name.'" content="'.$content.'">';
}

add_action( 'wp_head_add_meta', 'kdesc_add_meta', 10, 2 );

add_action( 'wp_head', function() {

  if ( is_tag() ) {

    global $wpdb;
    $tax_id = get_query_var('tag_id');

  }

  if ( is_category() ) {

    global $wpdb;
    $tax_id = get_query_var('cat');

  }

  if ( is_category() || is_tag() ) {

    $kdesc_data = $wpdb->get_row("
          SELECT description FROM $wpdb->term_taxonomy
          WHERE term_taxonomy_id = $tax_id
    ");

    $kdesc_keywords_data = get_tax_meta(
          $tax_id,
          'm_kdesc_keywords'
    );

    do_action('wp_head_add_meta', 'keywords', $kdesc_keywords_data);
    do_action('wp_head_add_meta', 'description', $kdesc_data->description);
  }
} ); //---/wp_head---


