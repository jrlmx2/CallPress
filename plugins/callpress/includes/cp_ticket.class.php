<?php

class cp_ticket extends cp_general
{

	/*
	 *  Id of the ticket
	 */

	public $id;

	/*
	 *	boolean with the comment status of the ticket.
	 */

	public $closed;

	/*
	 *	wordpress returned object of all the ticket data
 	 */

	public $ticket;

	/*
	 *	boolean describing the status of TagNet
	 */

	private $tag_net_on;

	/*
	 * constructor that sets the class variables. Takes in the ticket id. If the id does not exists, it creates a new ticket. 
	 */

	function __construct() {}

	/* :T1
	 * adds a ticket
	 */

	public static function add_ticket() {
		GLOBAL $current_user;
		$current_user = get_currentuserinfo();

		$post['post_author'] = $current_user->user_ID;
		$post['post_title'] = trim( $_POST['title'] );
		$post['post_content'] = $_POST['content'];
		$post['post_status'] = 'draft';
		$post['post_type'] = 'ticket';

		/*if( $possible_tags = explode( ' ' , strtolower($_POST['content']) ) ) {
			$replace = array( ',', '.', '<', '>', '(', ')', ';', ':', '[', ']' );
			$possible_tags = str_replace( $replace, '', $possible_tags );

			if( is_array( explode( ',', $_POST['tag']) ) ) {
				$tags = explode( ',', strtolower( $_POST['tags'] ) );

				foreach( $possible_tags as $possible_tag ) {
					trim($possible_tag);
					if( !in_array( $possible_tag, ) ) {
						if( !in_array( $possible_tag , $tags ) ) {
							$tags[] = $possible_tag;
						}
					}
				}
			} else {
				$tags = strtolower( $_POST['tags'] );
				foreach( $possible_tags as $possible_tag ) {
					trim($possible_tag);
					if( !in_array( $possible_tag, $cp_general->tag_net ) ) {
						if( strcmp( $possible_tag , $tags ) != 0 ) {
							$tags[] = $possible_tag;
						}
					}
				}
			}
		}*/
		$post_id = wp_insert_post( $post );
		wp_set_post_tags( $post_id , $tags );
		add_post_meta( $post_id, 'department_assigned', trim($_POST['category']) );
		if( $_POST[ 'closed' ] ) {
			add_post_meta( $post_id, 'closed_by', find_user( $_POST[ 'closed' ] ) );
			wp_update_post( array( 'ID' => $post_id, 'comment_status' => 'closed' ) );
		}
		if( $_POST['user_ids'] ) {	
			if( is_array( $_POST['user_ids'] ) ) {
				foreach( $_POST['users_ids'] as  $id ) {
					add_post_meta( $post_id, 'user_attached', $id );
				}
			} else {
				add_post_meta( $post_id, 'user_attached', $id );
			}
		}
		if( $_POST['object_ids'] ) {	
			if( is_array( $_POST['object_ids'] ) ) {
				foreach( $_POST['object_ids'] as  $id ) {
					add_post_meta( $post_id, 'object_attached', $id );
				}
			} else {
				add_post_meta( $post_id, 'object_attached', $id );
			}
		}
		if( $post_id ) {
			header( 'Content-type: json' ); 
			//if the post id is set, return a permalink as json to redirect to it display
			echo json_encode( array( 'url' => get_permalink( $post_id ) ) );
			die();
		}
	}

/*
* handles the display for the current ticket object
*/

function print_ticket() {
	if( $_GET[ 'search' ] ) {
	} else {
		$post = get_post( $_GET[ 'p' ], 'ARRAY_A' );

	if ( $post[ 'post_type' ] == 'ticket' ) {

		echo '<div id="ticket_view" class="grid_15 prefix_1 alpha omega">';
		echo '<h3>'.$post['post_title'].'</h3>';

	print_tags();

	echo  '<p class="grid_16">'.$post[ 'post_content' ].'</p>';
	echo '</div>';

get_post_comments();	

} else {

echo 'An error has occured. Please contact an administrator.';

}
}
}

/*
* creates a form for adding a ticket
*/

public static function ticket_form()
{?>
<div id="ticket_form">
<form method="post" name="add_form">
<h3>Add Ticket</h3>
<ul>
<li>
<label class="grid_2 alpha">Title:</label><input type="text" name="title" class="grid_4" tabindex=1 <?php if( $_POST[ 'title' ] ) echo 'value="'.$_POST[ 'title' ].'"'; ?> />
<label class="grid_2 prefix_6">Closed By:</label><input type="text" name="closed" class="grid_2 omega" tabindex=2 <?php if( $_POST[ 'closed' ] ) echo 'value="'.$_POST[ 'closed' ].'"'; ?> />
</li>
<li>
<label class="grid_2 alpha">Content:</label><textarea name="content" class="grid_14 omega" cols=6 tabindex=3 ><?php if( $_POST [ 'content' ] ) echo $_POST[ 'content' ]; ?> </textarea>
</li>
<li class="grid_16 alpha omega">
<label class="grid_2 alpha">Tags:</label><input type="text" name="tags" class="grid_6" tabindex=4 <?php if( $_POST[ 'tags' ] ) echo 'value="'.$_POST[ 'tags' ].'"'; ?> />
</li>
<li class="grid_16 alpha omega">
<label class="grid_2">Attach:</label><?php search_box( 'attachables', 'Attach users or objects', 5 ); ?>
<?php echo '<div class="attached_display">'; 
foreach( (array)$_POST[ 'attached[]' ] as $attached ) 
{
echo '<input type="text" value='.$attached.' name="attached" />';
}
echo '</div>';
?>
</li>
<li>
<label class="grid_2">Assigned:</label><?php ticket_classification_select(7, $_POST[ 'category' ]); ?>
</li>
<li class="prefix_13">
<input class="grid_3 omega" type="submit" value="Submit Ticket" name="action" />								
</li>
</ul>
</form>
<div style="clear:left;"></div>
</div>
<?php	
}

/*
*	creates the box to add a new comment to a comment to a ticket
*/

function new_comment_box()
{
global $user_login, $user_ID;
get_currentuserinfo();
echo '<form method="post">';
echo '<input name="cp_post_id" type="hidden" value='.$_GET[ 'p' ].' />';
if( isset($_GET[ 'c' ]) )
echo '<input name="cp_comment_parent" type="hidden" value='.$_GET[ 'c' ].' />';
echo '<label class="grid_2 alpha">'.$user_login.get_user_comment_count( $user_ID ).'</label<textarea rows=4 class="new_comment_content grid_14 omega" name="cp_comment_content"></textarea>';
echo '<p class="comment_box_footer grid_16 alpha omega">
<span class="grid_4 prefix_12 alpha omega">
<input class="grid_2 alpha" name="action" type="submit" value="Close" />
<input name="action" class="grid_2 omega" type="submit" value="Post" />
</span>
</p>';
echo '</form>';
}

/*
* prints tags associated witht he ticket
*/

function print_tags()
{
$tags = get_the_tags( $_GET[ 'p' ] );
echo '<p class="ticket_tag"> Tags: ';
foreach( (array)$tags as $tag	)
{
echo '<span class="ticket_tag"><a href="">'.$tag->name.' ('.$tag->count.')</a></span> ';
}
echo '</p>';

}

/*
* handles comment display for the current ticket object.
*/

function print_comments()
{
get_currentuserinfo();
$post = get_post( $_GET[ 'p' ] );
if( $post->comment_status == 'open' )
new_comment_box();
$comments = get_comments( array( 'post_id' => $_GET[ 'p' ] ) );
$c = 1;
foreach( (array)$comments as $comment )
{
echo '<div id="comment_display" class="grid_16 alpha omega comment_'.$c.'">'; 
echo '<label class="grid_2 alpha">'.$comment->comment_author.get_user_comment_count( $comment->user_id ).'</label>';
echo '<p class="grid_14 omega comment">'.$comment->comment_content.'</p>';
echo '</div>';
$c++;
}
}

/*
* alter ticket information. Only available for original creator and higher ups in the system
*/

function alter_ticket()
{
}

/*
* adds an attachment to the current ticket object.
*/

function add_attachment()
{
}

/*
*	removes an already attached item
*/

function remove_attachment()
{
}

/*
* adds a comment
*/

function add_comment()
{
global $user_ID, $user_login, $user_email, $user_url;
$current_user = get_currentuserinfo();

$time = current_time('mysql');

$data = array(
'comment_post_ID' => $_POST[ 'cp_post_id' ],
'comment_author' => $user_login,
'comment_author_email' => $user_email,
'comment_author_url' => $user_url,
'comment_content' => $_POST[ 'cp_comment_content' ],
'comment_type' => $_POST[ 'cp_is_privite_comment' ],
'user_id' => $user_ID,
'comment_author_IP' => $ip=@$REMOTE_ADDR,
'comment_date' => $time,
'comment_approved' => 1
);
if( isset( $_POST[ 'cp_comment_parent' ] ) )
$data['comment_parent'] = $_POST[ 'cp_comment_parent' ];
else
$data['comment_parent'] = 0;
wp_insert_comment($data);

}

/*
*	consider a ticket completed or depricated. Removes the ability to add comments or edit the ticket
*/

function close_ticket()
{
$post[ 'ID' ] = $_POST[ 'cp_post_id' ];
$post[ 'comment_status' ] = 'closed';

wp_update_post( $post );

$post = get_post( $_POST[ 'cp_post_id' ] );
cp_email( 'TCO', $post);

}

/*
*  if a ticket needs to be reactivated for comments or editing. Can be noisy but doesnt have to be.
*/

function reopen_ticket()
{
}

/*
*  add a comment readable at your the comment adders user level and up. Controllable at the higher levels.
*/

function add_sensitive_comment()
{
}

/*
* 	set the priority of a ticket.
*/

function set_priority()
{
}

/*
*	if someone is looking at ticket this is updated to so show other people in the logs. 
*/

function set_status()
{
}

/*
*	fetches the status of a ticket and prints it.
*/

function print_status()
{
}

/*
*	adds a user or object or ticket to a ticket
*/

function attach()
{
}

}
add_action( 'wp_ajax_add_ticket', 'cp_ticket::add_ticket' ); 
