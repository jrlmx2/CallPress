<?php
  function search_users()
  {
    $phrase = trim(strtolower($_GET['name']));
		
    	global $wpdb;
    	$sql ="SELECT m.user_id
    	FROM (SELECT user_id, meta_value, meta_key FROM `wp_usermeta` WHERE meta_key='first_name' OR meta_key='last_name') AS m
    	JOIN (SELECT ID, user_email, display_name FROM wp_users) AS u
    	ON u.ID=m.user_id WHERE meta_value LIKE '".$phrase."%' OR user_email LIKE '".$phrase."%'OR display_name LIKE '".$phrase."%'";
		
    	$results = $wpdb->get_results( $sql, 'ARRAY_N' );
    $already_used = array();
  if( isset($results) )
    {
    if( is_array($results) )
    {
     foreach( $results as $result )
     {
       if( is_array( $_GET['users'] ) )
       {
         if( ( !in_array( $result[0], $already_used ) ) && ( !in_array( $result[0], $_GET['users'] ) ) )
         {
           $already_used[]=$result[0];
           $users_returned[]=get_userdata($result[0]);
         }
       }
       else
       {
         if( !in_array( $result[0], $already_used ) && $result[0]!=$_GET['users'] )
         {
           $already_used[]=$result[0];
           $users_returned[]=get_userdata($result[0]);
         }
       }
     }
    } else {
      if( is_array( $_GET['users'] ) )
      {
        if( !in_array( $results[0], $_GET['users'] ) )
          {
           $users_returned[]=get_userdata($result[0]);
          }
      } else {
        if( $results[0]!=$_GET['users'] )
        {
           $users_returned[]=get_userdata($result[0]);
        }
      }
    }
  } else {
    $users_returned = array( 'false' );
  }
			$sql ="SELECT p.id FROM (SELECT id, post_title, post_content FROM wp_posts WHERE post_type='object' AND post_status='published')AS p
						JOIN wp_postmeta AS m ON p.id=m.post_id WHERE meta_value LIKE '".$phrase."%' OR post_title LIKE '".$phrase."%' OR post_content LIKE '".$phrase."%
						'";
			$results = $wpdb->get_results($sql, 'ARRAY_N');
			if( isset( $results ) )
			{
				if( is_array( $results ) )
				{
					foreach( $results as &$result )
					{
						$result = get_post( $result[0], 'array_a' );
					}
				} else {
					$results = array( get_post( $results[0] ) );
				}
			} else {	
					$results = array ( 'false' );
			}
			$attachables = array_merge( (array)$results, (array)$users_returned );

			print json_encode( $attachables );
	}	

function search_attachables()
{
}

if( $_POST[ 'action' ] == '' && isset( $_POST[ 'add_form' ] ) ) 
		die('die');

class cp_search
{
/*
 *	category defining the search
 */
			private $search_category;
/*
 *	term further narrowing the search
 */
			private $search_term; 
/*
 *	results of the last search
 */
			public $results = array();
/*
 *  defines variables and searches.
 */
			function __construct()
			{

			}
/*
 *		function for any search
 */
			function search( $search_category, $search_term )
			{

			}
/*
 *    ??? saves search results
 */
			function save_search()
			{

			}
/*
 *    get the variables of the users prefered search
 */

			function get_preffered_search()
			{

			}


}
