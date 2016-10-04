<?php

/**
* WEFQuery
*/
class WEFQuery
{

  public static function query($query)
  {

    $url_parts = parse_url(  wef_current_uri() );
    parse_str($url_parts['query'], $params);

    $prod_attr = array();
    foreach ($params as $key => $value) {
    	$realKey = 'pa_'.$key;
    
    	 $prod_attr[] = [
          'taxonomy' => $realKey,
          'terms' => $value,
         ];
    	

     /* if (substr($key, 0 , 3)=='pa_'){
        $prod_attr[] = [
          'taxonomy' => $key,
          'terms' => $value,
        ];
     }*/
    }
    
    // print_r( $prod_attr);
    // die();

    $taxquery = array('relation' => 'AND');

    foreach ($prod_attr as $key => $value) {
      $terms = explode(",", $value['terms']);
      // if ( count($terms) == 0)
      //     $terms =  $value['terms'];
      $taxquery [] =  array(
        'taxonomy' => $value['taxonomy'],
        'field'=> 'slug',
        'terms' =>   $terms,
        'include_children' => false,
        //  'operator'=> 'IN',
      );
    }
    
     

     if( $query->is_main_query()  and !$query->is_search){
	    $query->set( 'tax_query', $taxquery );
	    return $query;
	 }

  }

}
