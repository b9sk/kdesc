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
ini_set('error_log', 'error_output.log');



/**
 * kdesc_table_exist()
 * Проверяет есть ли искомая колонка в указаной таблице
 *
 * @param $tbl str Имя таблицы без префиса в которой производится поиск.
 * @param $col str Искомая колонка.
 * @return boolean true - col is exist, NULL - col is not exist.
 */
function kdesc_table_exist( $tbl = 'term_taxonomy', $col = 'keywords') {
  global $wpdb;
  $table_name = $wpdb->prefix . $tbl;
  //var_dump("tbl, col, table_name", $tbl, $col, $table_name);
  //foreach ( $GLOBALS['wpdb']->get_results( "SHOW COLUMNS FROM $table_name", ARRAY_A ) as $column_name ) { // Это работает, но возвращает геморрой
  foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column_name ) {
    $table_cols[] = $column_name;
  }
  //var_dump($table_cols);
  if ( array_search( $col, $table_cols ) !== FALSE )
    return true;

  //var_dump($wpdb->query("SHOW COLUMNS FROM $tbl"));

}

/**
 * Установка плагина
 */

register_activation_hook( __FILE__, 'kdesc_activate' );

function kdesc_activate() {

  /*add_action( 'admin_notices', function() {
    echo "<div id=\"message\" class=\"updated\"><pre>";
    var_dump($kdesc_table_cols);
    echo "</pre>";
  } );*/

  /*is_admin() && add_filter( 'gettext',


    function( $translated_text, $untranslated_text, $domain )
    {
        $old = array(
            "Plugin <strong>activated</strong>.",
            "Selected plugins <strong>activated</strong>."
        );

        ob_start();
        var_dump($kdesc_table_cols);
        $result = ob_get_clean();

        $new = "<pre>" . $kdesc_table_cols . "</pre>";

        if ( in_array( $untranslated_text, $old, true ) )
            $translated_text = $new;

        return $translated_text;
     }
  , 99, 3 );*/

  //if (!array_search( 'keywords', $kdesc_table_cols )) {
  if ( !kdesc_table_exist('term_taxonomy', 'keywords') ) {
    //var_dump("Time to add col to table!");
    global $wpdb;
    $wpdb->query("
      ALTER TABLE wp_term_taxonomy ADD keywords LONGTEXT CHARACTER SET utf8mb4
      COLLATE utf8mb4_unicode_ci NOT NULL AFTER description
    ");

  }

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
    $kdesc_tax_id = get_query_var('tag_id');

    $kdesc_get_data = $wpdb->get_row("
      SELECT description, keywords FROM $wpdb->term_taxonomy
      WHERE term_taxonomy_id = $kdesc_tax_id
    ");
  }

  if ( is_category() ) {

    global $wpdb;
    $kdesc_tax_id = get_query_var('cat');

    $kdesc_get_data = $wpdb->get_row("
      SELECT description, keywords FROM $wpdb->term_taxonomy
      WHERE term_taxonomy_id = $kdesc_tax_id
    ");
  }

  if ( is_category() || is_tag() ) {
    do_action('wp_head_add_meta', 'keywords', $kdesc_get_data->keywords);
    do_action('wp_head_add_meta', 'description', $kdesc_get_data->description);
  }
} );

//add_action( 'admin_notices', 'kdesc_table_exist' );

/*
 * Добавить поле
 * ALTER TABLE  `wp_term_taxonomy` ADD  `keywords` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER  `description`
 *
 * Удалить поле
 ALTER TABLE `wp_term_taxonomy` DROP `keywords`
 */
