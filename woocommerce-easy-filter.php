<?php
/**
* Plugin Name: Woocommerce Easy Filter
* Plugin URI:
* Description: Filter for Woocommerce
* Version: 1.0.0
* Author: Neysi Tuesta
* Author URI:
* License: GPL2
*/

defined( 'ABSPATH' ) or die( 'Prohibido!' );
define('PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
require_once( ABSPATH . "wp-includes/pluggable.php" );
include( PLUGIN_DIR. 'helpers.php');
include( PLUGIN_DIR. 'WEFWidgetRender.php');
include( PLUGIN_DIR. 'WEFWidget.php');
include( PLUGIN_DIR. 'WEFQuery.php');



//Ejemplo de widgets
$wef_widgets = array(

 array(
    'id' => 'wef_6' ,
    'taxonomy'=>'pa_marca',
    'show_in_category' => array('*') ,
    'limit' => 50 ,
    'hidde_empty' => true,
    'label' => 'Marca',
    'type' => 'list',
  ),

  array(
    'id' => 'wef_1' ,
    'taxonomy'=>'pa_ram',
    'show_in_category' => array('computadoras','laptops','ultrabooks','servidores','tablets') ,
    'limit' => 50 ,
    'hidde_empty' => true,
    'label' => 'Memoria RAM',
    'type' => 'list',
  ),
  /*array(
    'id' => 'wef_2' ,
    'taxonomy'=>'pa_color',
    'show_in_category' => array('*') ,
    'limit' => 50 ,
    'hidde_empty' => true,
    'label' => 'Color',
    'type' => 'list',
  ),*/

  array(
    'id' => 'wef_3' ,
    'taxonomy'=>'pa_capacidad',
    'show_in_category' => array('memorias-usb','memorias-ram-flash') ,
    'limit' => 50 ,
    'hidde_empty' => true,
    'label' => 'Capacidad',
    'type' => 'list',
  ),


  array(
    'id' => 'wef_4' ,
    'taxonomy'=>'pa_pantalla',
    'show_in_category' => array('computadoras','laptops','ultrabooks','servidores','tablets') ,
    'limit' => 50 ,
    'hidde_empty' => true,
    'label' => 'Pantalla',
    'type' => 'list',
  ),

  array(
    'id' => 'wef_5' ,
    'taxonomy'=>'pa_procesador',
    'show_in_category' => array('computadoras','laptops','ultrabooks','servidores') ,
    'limit' => 50 ,
    'hidde_empty' => true,
    'label' => 'Procesador',
    'type' => 'list',
  ),


);


// if ( is_user_logged_in ()){

 if (!is_admin()) {
 	
 	

  add_action('pre_get_posts', function ($query)
  {
  	
    WEFQuery::query($query);
  });
  
 }



  add_action( 'widgets_init', function(){
  	 if (!is_search()){
        register_widget( 'WEFWidget' );
  	 }
  });
 


// }
