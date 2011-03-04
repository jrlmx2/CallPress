<?php

function handle_classification_changes()
{
				if( $_POST[ 'class_save' ] )
				{
								$classes = explode( ',', $_POST[ 'class_sav' ] );
								foreach( (array)$classes as $classe )
								{	
												wp_insert_term( $classe, 'ticket_classifications');
								}

				}elseif( $_POST[ 'class_delete' ] )
				{

								wp_delete_term( $_POST[ 'category' ], 'ticket_classifications');

				}

}

if( $_POST['class_sav'] || $_POST['class_del'] )
{
				add_action( 'init', 'handle_classification_changes' );
}

function callpress_admin_menu() 
{

	add_menu_page('CallPress Configuration', 'CallPress', 'publish_posts', 'callpress.php', 'render_options_personal');
	add_submenu_page('callpress.php', 'CallPress Configuration', 'Admin Config', 'delete_others_posts', 'admin', 'render_options_admin');

}

add_action( 'admin_menu', 'callpress_admin_menu' );
function ticket_classification_select( $tabindex )
{
				
						$classifications = get_terms( 'ticket_classifications', array( 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ) );
						if( $classifications )
						{
										echo '<select tabindex='.$tabindex.' name="category">';
																		foreach( (array) $classifications as $classification )
																		{
																				echo '<option value='.$classification->term_id.'>'.$classification->name.'</option>';
																		}
										echo '<option value="" selected="selected"></option></select>';
						}else{				
									echo 'There are no classifications set.';
						}
}
function render_options_admin()
{?>

	<a href="<?php bloginfo('url'); ?>">Back to CallPress</a>	
		<h3>Ticket classification</h3>
			<form method='post' name="classification">
				<input type="text" tabindex=1 name="class_sav" />
				<input type="submit" tabindex=2 value="Save" name="class_save" />
				<p><small>If you wish to do multiple entries at one time, separate the phrase/word with a comma.</small></p>
				<br />
					<?php
						ticket_classification_select(3);
					?>
				<input type="submit" tabindex=4 value="Delete" name="class_delete" />
			</form>
		<h3>
			<span>Announcements</span>

		</h3>
		<form method="post">
			<input type="text" name="announcement" /><br />
			<span>Minimum allowed: </span><input style="width:25px;" type="text" name="announce_min" /><br />
			<input type="submit" value="Save" />
		</form>
		<h3>Oject Management</h3>
		<form method="post">
			<span>Name:</span><input name="object_name" type="text" />			
			<span>Link:</span><input name="object_link" type="text" />
			<span>Description:</span><input name="object_description" type="text" />
			<input type="submit" value="save_object" />
		</form>
<?php
}
function render_options_personal()
{?>
<style type="text/css">
th, td
{
	width:100px;
	text-align:center;
	padding:10px 10px 10px 10px;
}
</style>
	<a href="<?php bloginfo('url'); ?>">Back to CallPress</a>
	<h3>Personal Settings</h3>
		<form id="options" method="post">
			<input class="number" type="text" style="width:25px;" name="announcement_setting" /> <span>Announcements Displayed</span>
			<h5>Departments Subscribed</h5>
<?php
			GLOBAL $wpdb, $current_user, $user_ID;
    $current_user = get_currentuserinfo();

				$classifications = get_terms( 'ticket_classifications', array( 'hide_empty' => false, 'orderby'=>'name', 'order'=>'ASC', 'fields'=>'names' ) );
				if( $classifications && !is_object($classifications) )				
				{


												echo '<table>
																<tr>
																	<th>Ticket Classification</th>
																	<th>Subscribe</th>
																	<th>Ticket E-mail Notification</th>
																	<th>High Priority Notification</th>
																	<th>Daily Summery E-mail</th>
																	<th>E-mail Alert Notifications</th>
																</tr>';

												foreach( (array)$classifications as $classification )
												{
																echo '<tr>';
																echo '<td>'.$classification.'</td>
																<td><input name="'.$classification.'[]" value="'.$classification.'_subscribe" type="checkbox" '; 
																if( get_user_meta( $user_ID, $classification.'_subscribe', true )==true )
																{
																				echo 'checked="checked" ';
																}
																echo '/></td>';
																echo '<td><input name="'.$classification.'[]" value="'.$classification.'_ticket_submission" type="checkbox" '; 
																if( get_user_meta( $user_ID, $classification.'_ticket_submission', true )==true )
																{
																				echo 'checked="checked" ';
																}
																echo '/></td>';
																echo '<td><input name="'.$classification.'[]" value="'.$classification.'_ticket_highpriority" type="checkbox" '; 
																if( get_user_meta( $user_ID, $classification.'_ticket_highpriority', true )==true )
																{
																				echo 'checked="checked" ';
																}
																echo '/></td>';
																echo '<td><input name="'.$classification.'[]" value="'.$classification.'_activity_summery" type="checkbox" '; 
																if( get_user_meta( $user_ID, $classification.'_activity_summery', true )==true )
																{
																				echo 'checked="checked" ';
																}
																echo '/></td>';
																echo '<td><input name="'.$classification.'[]" value="'.$classification.'_warning_alert_notifications" type="checkbox" '; 
																if( get_user_meta( $user_ID, $classification.'_warning_alert_notifications', true )==true )
																{
																echo 'checked="checked" ';
																echo '/></td>';
																echo '</tr>';
																}

												}
												echo '</table>';

				} else {
								echo 'Warning! No ticket classifications are set. ';

								GLOBAL $user_level;
								$current_user = get_currentuserinfo();
								
								if( $user_level > 7 )
								{
												echo 'Click <a href="'.get_bloginfo( 'url' ).'/wp-admin/admin.php?page=admin">here</a> to add classifications';
								} else {

												echo 'Please speak to a system administrator about this problem';
								}

				}				
					echo '<br />';						

			?>
			<input value="save settings" type="submit" name="options_saved" />
		</form>	
<?php
}

function save_personal_settings()
{
				$classifications = get_terms( 'ticket_classifications', array( 'hide_empty' => false, 'orderby'=>'name', 'order'=>'ASC', 'fields'=>'names' ) );

				GLOBAL $user_ID, $wpdb;
				$current_user = get_currentuserinfo();

				if( $classifications )
				{
								foreach( $classifications as $classification )
								{
													foreach( ( array )$_POST[ $classification ] as $setting )
													{
																	if($setting)
																				update_user_meta( $user_ID, $setting, true );
																	else
																				update_user_meta( $user_ID, $setting, false );
													}
								}
				}
				if ( isset($_POST[ 'announcement_setting' ]) )
				{
								if( !$minimum = get_option( 'announcement_minimum' ) )
												$minimum = 0;

								if( $_POST['announcement_setting'] >= $minimum)
												update_user_meta( $user_ID, 'announcement_amount', true );
								else
												print '<p>The minimum set for this system is '.$minimum.'. Contact the system administrator if you need assistance.</p>';
				}
}

if( $_POST[ 'options_saved' ] )
{
				add_action( 'init', 'save_personal_settings' );
}

function save_anouncement_admin()
{
					if( $_POST[ 'announcement' ] ) 
					{

									$post[ 'post_content' ] = $_POST[ 'announcement' ];
									$post[ 'post_type' ] = 'announcement' ;
									wp_publish_post( wp_insert_post( $post ) );

					}
					if( isset($_POST[ 'announce_min' ]) )
					{
									$options = get_alloptions();
									if($options['announcement_minimum'])
													update_option( 'announcement_minimum', $_POST[ 'announce_min' ]);
									else
													add_option( 'announcement_minimum', $_POST[ 'announce_min' ] );


									GLOBAL $wpdb;
									$sql = "SELECT ID FROM wp_users";

									$users = $wpdb->get_results( $sql, 'ARRAY_N' );

									foreach( (array)$users as $user )
									{

												if( !$user_amount = get_user_meta( $user[0], 'announcement_amount', true ) )
														$user_amount = 0;

												if( $user_amount < $_POST[ 'announce_min' ] )
														update_user_meta( $user[0], 'announcement_amount', $_POST[ 'announce_min' ] );
									}

					}
}

if( $_POST[ 'announcement' ] || isset( $_POST[ 'announce_min' ] ) )
{
				add_action( 'init', 'save_anouncement_admin' );
}
function admin_menu_stylesheet()
{

				echo '<link rel="stylesheet" herf="/wp-content/themes/callpress/wp-admin.css" type="text/css" />';

}

add_action( 'admin_head', 'admin_menu_stylesheet' );

function announcement_status()
{
		if( get_option( 'announcements_off' ) )
				return false;
		
		return true;
}


class cp_admin
{
}

?>
