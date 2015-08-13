<?php
/*
Plugin Name: KDESC
Plugin URI: https://github.com/b9sk/kdesc
Description: Adding keywords field for tag and category taxonomies. Output META description and META keywords to HEAD.
Version: 0.3
Author: Your mom
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

  $kdesc_field->addText( 'm_kdesc_keywords', array('name'=> __('Meta Keywords', 'tax-meta'), 'desc' => 'Custom keywords for this taxonomy.' ) );
  $kdesc_field->addText( 'm_kdesc_description', array('name'=> __('Meta Description', 'tax-meta'), 'desc' => 'Custom description for this taxonomy.' ) );
  $kdesc_field->addText( 'm_kdesc_title', array('name'=> __('Custom title', 'tax-meta'), 'desc' => 'Rewrite title-tag.' ) );

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

    /*$kdesc_data = $wpdb->get_row("
          SELECT description FROM $wpdb->term_taxonomy
          WHERE term_taxonomy_id = $tax_id
    ");*/

    $kdesc_keywords_data = get_tax_meta(
          $tax_id,
          'm_kdesc_keywords'
    );

    $kdesc_description_data = get_tax_meta(
          $tax_id,
          'm_kdesc_description'
    );

    do_action('wp_head_add_meta', 'keywords', $kdesc_keywords_data);
    do_action('wp_head_add_meta', 'description', $kdesc_description_data);

  }
} ); //---/wp_head---


/*
 * Перезаписать title
 */

add_filter( 'wp_title', 'kdesc_title_tag_rewtite' );

function kdesc_title_tag_rewtite($title) {
  if ( is_category() || is_tag() ) {

    $tax_id = is_category() ? get_query_var('cat') : get_query_var('tag_id');
    $title = get_tax_meta($tax_id, 'm_kdesc_title') ?
      get_tax_meta($tax_id, 'm_kdesc_title') . ' | ' . get_bloginfo( 'name' ) :
      $title;

  }
  return $title;
}



