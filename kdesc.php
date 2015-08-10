<?php
/*
Plugin Name: KDESC
Plugin URI: http://example.com/wordpress-plugins/my-plugin
Description: Custom keywords and description for tags and category.
Version: 0.2
Author: Your mom
Author URI: http://somegitlink.com
License: Propietary
*/

/**
 * Установка плагина
 */

/**
 * kdesc_table_exist()
 * Проверяет есть ли искомая колонка в указаной таблице
 *
 * @param $tbl str Имя таблицы без префиса в которой производится поиск.
 * @param $col str Искомая колонка.
 * @return boolean true - col is exist, NULL - col is not exist.
 */
/*
function kdesc_table_exist( $tbl = 'term_taxonomy', $col = 'keywords') {
  global $wpdb;
  $table_name = $wpdb->prefix . $tbl;
  foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column_name ) {
    $table_cols[] = $column_name;
  }
  if ( array_search( $col, $table_cols ) !== FALSE )
    return true;
}

function kdesc_activate() {

  if ( !kdesc_table_exist('term_taxonomy', 'keywords') ) {
    global $wpdb;
    $wpdb->query("
      ALTER TABLE wp_term_taxonomy ADD keywords LONGTEXT CHARACTER SET utf8mb4
      COLLATE utf8mb4_unicode_ci NOT NULL AFTER description
    ");
  }
}

register_activation_hook( __FILE__, 'kdesc_activate' );
*/ // Устарело. Новый класс - Tax-meta-class.php - будет обрабытывать это иначе.

/**
 * Дополнительные поле keywords для tag и catedory
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

  $kdesc_kw_field_meta =  new Tax_Meta_Class( $kdesc_config );

  $kdesc_kw_field_meta->addText( 'm_kdesc_keywords', array('name'=> __('Keywords', 'tax-meta'), 'desc' => 'Keywords for meta-tag in HEAD' ) );

  $kdesc_kw_field_meta->Finish();
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

    $kdesc_data = $wpdb->get_row("
          SELECT description FROM $wpdb->term_taxonomy
          WHERE term_taxonomy_id = $tax_id
    ");

    $kdesc_keywords_data = get_tax_meta(
          $tax_id,
          'm_kdesc_keywords'
    );
  }

  if ( is_category() ) {

    global $wpdb;
    $tax_id = get_query_var('cat');

    $kdesc_data = $wpdb->get_row("
      SELECT description FROM $wpdb->term_taxonomy
      WHERE term_taxonomy_id = $tax_id
    ");

    $kdesc_keywords_data = get_tax_meta(
          $tax_id,
          'm_kdesc_keywords'
    );
  }

  if ( is_category() || is_tag() ) {
    do_action('wp_head_add_meta', 'keywords', $kdesc_keywords_data);
    do_action('wp_head_add_meta', 'description', $kdesc_data->description);
  }
} ); //---/wp_head---


