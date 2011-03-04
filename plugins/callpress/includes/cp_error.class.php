<?php

class cp_error
{
				/*
				 * 	file where the error occured
				 */

				public $file;

				/*
				 * 	function where the code did not turn out properly
				 */

				public $function;

				/*
				 * 	optional - line of code failure
				 */

				public $line;

				/*
				 * 	optional - string of variables invovled
				 */

				public $vars;

				/*
				 *  constructor that sets all of the object variables
				 */

				function __construct( $file, $function, $line, $vars )
				{
								$this->file=$file;
								$this->function=$function;
								$this->line=$line;
								$this->vars=$vars;
				}
				
				/*
				 *	sets error up as ticket in the callpress database
				 */
				function record_error()
				{
								$params = array(
												'post_content'	=> $this->vars,
												'post_title'		=> $this->file,
												'post_excerpt' 	=> $this->line,
												'post_name'			=> $this->function
								)
								wp_insert_post( $params );
				}

				/*
				 *  displays formateed warnings for debugging purposes.
				 */

				function print_warning()
				{
								echo 'file: '.$this->file;
								echo 'function: '.$this->file;
				}

				/*
				 *  emails all recorded errors to the orginal creator for the alpha and beta and rcs of the project. Controllable on 1.0 release.
				 */

				static function send_all_errors()
				{
								$posts_to_send = get_posts( array( 'post_type' => 'error', 'post_status' => 'publish' ) );
								foreach( $posts_to_send as $post )
								{
												wp_update_post( array( 'ID' => $post->ID, 'post_status' => 'closed' ) );
												$error_descs = 'vars: '.$post->post_content.' file: '.$post->post_title.' line: '.$post->post_excerpt.' function: '.$post->post_name.' \r\n';
								}

							mail( 'jrlmx2callpress@gmail.com', 'Callpress Errors', $error_descs);

				}
}
