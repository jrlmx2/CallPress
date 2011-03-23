<?php
/*
 *    These items belong in the run once upon initial install of callpress
 *
 *  TagNet
 *  This is the list of words that should not be caught by tagnet.
 *  This neeeds to be put into site options with the name tag_net.
 *  A status of on and off are also necessary for this to be completed
 *  along with the ability to add and delete entries. It is also a good idea
 *  to add reset functionallity.
 *  This is the initial list of words:
 *  array( 'combination', 'unmatched', 'on', 'and', 'people', 'crazy', 'strange', 'acting', 'type', 'why', 'of', 'should', 'would', 'could', 'for', 'i', 'will', 'if', 'am', 'from', 'more', 'than', 'he', 'she', 'it', 'they', 'we', 'was', 'were', 'being', 'been', 'be', 'are', 'is', 'their', "they\'re", 'there', 'us', 'you',  'an', 'so', 'the' ,'a'  ,'break' ,'cut' ,'run' ,'play' ,'make' ,'light' ,'set' ,'hold' ,'clear' ,'give' ,'draw' ,'take' ,'fall' ,'pass' ,'head' ,'call' ,'carry' ,'charge' ,'point' ,'catch' ,'turn' ,'check' ,'get' ,'close' ,'line' ,'right' ,'lift' ,'cover' ,'open' ,'go' ,'beat' ,'work' ,'drive' ,'roll' ,'drop' ,'place' ,'lead' ,'raise' ,'clean' ,'mark' ,'base' ,'heavy' ,'return' ,'blow' ,'block' ,'back' ,'strike' ,'rise' ,'good' ,'touch' ,'stock' ,'snap' ,'slip' ,'down' ,'round' ,'keep' ,'stick' ,'square' ,'white' ,'see' ,'crack' ,'sound' ,'stand' ,'follow' ,'pull' ,'flat' ,'direct' ,'order' ,'hit' ,'dip' ,'spread' ,'post' ,'settle' ,'ground' ,'center' ,'short' ,'shift' ,'press' ,'twist' ,'form' ,'swing' ,'start' ,'pitch' ,'well' ,'shoot' ,'release' ,'top' ,'deal' ,'pound' ,'free' ,'black' ,'face' ,'stop' ,'hard' ,'support' ,'pack' ,'case' ,'come' ,'pick' ,'jump' ,'develop' ,'wash' ,'straight' ,'deep' ,'key' ,'double' ,'last' ,'dead' ,'dress' ,'hot' ,'move' ,'step' ,'field' ,'change' ,'throw' ,'sign' ,'reduce' ,'tap' ,'discharge' ,'have' ,'cast' ,'strain' ,'spot' ,'stretch' ,'advance' ,'hook' ,'soft' ,'burn' ,'fly' ,'control' ,'break up' ,'strip' ,'separate' ,'dull' ,'force' ,'flash' ,'rule' ,'pop' ,'bar' ,'match' ,'live' ,'dry' ,'level' ,'end' ,'score' ,'crash' ,'land' ,'rack' ,'position' ,'hang' ,'rest' ,'bank' ,'piece' ,'high' ,'stamp' ,'service' ,'present' ,'scale' ,'figure' ,'shell' ,'loose' ,'tie' ,'still' ,'part' ,'solid' ,'find' ,'fire' ,'shot' ,'flip' ,'address' ,'quarter' ,'flush' ,'tender' ,'rough' ,'split' ,'train' ,'squeeze' ,'shock' ,'spike' ,'regular' ,'bad' ,'home' ,'feel' ,'frame' ,'bound' ,'extend' ,'exchange' ,'scratch' ,'active' ,'drag' ,'number' ,'drift' ,'yield' ,'job' ,'wild' ,'take in' ,'upset' ,'trim' ,'out' ,'big' ,'leave' ,'range' ,'fair' ,'walk' ,'slack' ,'puff' ,'fix' ,'string' ,'register' ,'kill' ,'fit' ,'cold' ,'float' ,'blast' ,'smash' ,'model' ,'sweet' ,'choke' ,'do' ,'cross' ,'first' ,'track' ,'screen' ,'tight' ,'hack' ,'dark' ,'balance' ,'stroke' ,'mean' ,'stay' ,'crown' ,'blue' ,'serve' ,'fret' ,'low' ,'pick up' ,'study' ,'foul' ,'stone' ,'hand' ,'ride' ,'plate' ,'show' ,'rush' ,'seat' ,'issue' ,'bolt' ,'offer' ,'waste' ,'ring' ,'subject' ,'name' ,'air' ,'finish' ,'bear' ,'lock' ,'sharp' ,'gray' ,'rail' ,'bull' ,'meet' ,'wind' ,'grain' ,'push' ,'color' ,'sweep' ,'grant' ,'true' ,'set up' ,'book' ,'answer' ,'shake' ,'pin' ,'easy' ,'review' ,'band' ,'second' ,'transfer' ,'master' ,'represent' ,'attack' ,'brush' ,'escape' ,'loop' ,'act' ,'seal' ,'time' ,'hunt' ,'fast' ,'tone' ,'section' ,'colour' ,'flare' ,'port' ,'even' ,'load' ,'hurt' ,'trace' ,'account' ,'bond' ,'draft' ,'outside' ,'think' ,'wave' ,'tip' ,'pit' ,'guard' ,'house' ,'freeze' ,'project' ,'life' ,'green' ,'look' ,'switch' ,'bow' ,'flow' ,'die' ,'foot' ,'march' ,'opening' ,'reverse' ,'jack' ,'design' ,'render' ,'side' ,'firm' ,'heave' ,'secret' ,'raw' ,'cloud' ,'mold' ,'root' ,'away' ,'young' ,'stuff' ,'approach' ,'chip' ,'up' ,'bag' ,'kick' ,'game' ,'corner' ,'be' ,'nose' ,'court' ,'take out' ,'stall' ,'shaft' ,'dig' ,'patch' ,'plug' ,'date' ,'sack' ,'visit' ,'measure' ,'express' ,'seed' ,'test' ,'bite' ,'sink' ,'shade' ,'report' ,'mount' ,'grade' ,'beam' ,'natural' ,'rank' ,'bare' ,'small' ,'front' ,'broken' ,'warm' ,'brace' ,'receive' ,'mate' ,'use' ,'watch' ,'take up' ,'reach' ,'gain' ,'better' ,'way' ,'record' ,'voice' ,'bill' ,'credit' ,'thick' ,'real' ,'spare' ,'condition' ,'thrust' ,'grey' ,'box' ,'fresh' ,'joint' ,'hall' ,'positive' ,'process' ,'rich' ,'note' ,'man' ,'superior' ,'wrong' ,'tumble' ,'medium' ,'splash' ,'link' ,'full' ,'bed' ,'course' ,'pole' ,'c' ,'view' ,'thin' ,'major' ,'board' ,'quiet' ,'union' ,'dirty' ,'spin' ,'ball' ,'taste' ,'counter' ,'mind' ,'cutting' ,'card' ,'camp' ,'miss' ,'count' ,'long' ,'shadow' ,'pay' ,'crop' ,'flight' ,'extension' ,'collar' ,'know' ,'glass' ,'silver' ,'thing' ,'dash' ,'bottom' ,'horn' ,'smack' ,'title' ,'bend' ,'average' ,'fold' ,'style' ,'tack' ,'tough' ,'read' ,'defense' ,'deposit' ,'chain' ,'bitter' ,'hitch' ,'star' ,'commission' ,'floor' ,'bang' ,'forward' ,'prime' ,'give up' ,'puddle' ,'command' ,'wing' ,'say' ,'pinch' ,'focus' ,'help' ,'body' ,'glow' ,'net' ,'action' ,'new' ,'recall' ,'cry' ,'proof' ,'ruffle' ,'negative' ,'turn out' ,'dock' ,'build' ,'come up' ,'deliver' ,'butt' ,'trust' ,'burst' ,'bob' ,'picture' ,'weak' ,'tease' ,'trap' ,'bowl' ,'flag' ,'passing' ,'bridge' ,'division' ,'flick' ,'crush' ,'convert' ,'ruin' ,'trip' ,'save' ,'queen' ,'withdraw' ,'wear' ,'dissolve' ,'trade' ,'smooth' ,'contract' ,'feed' ,'correct' ,'circle' ,'rear' ,'false' ,'source' ,'image' ,'contact' ,'like' ,'chop' ,'pool' ,'clip' ,'running' ,'spoil' ,'mass' ,'get off' ,'stiff' ,'rat' ,'so' ,'connect' ,'click' ,'host' ,'crab' ,'fail' ,'bit' ,'talk' ,'centre' ,'collapse' ,'obscure' ,'pile' ,'heel' ,'club' ,'capture' ,'pump' ,'heat' ,'demand' ,'whistle' ,'carrier' ,'irregular' ,'mature' ,'cap' ,'sour' ,'shine' ,'reference' ,'cool' ,'mouth' ,'upgrade' ,'clinch' ,'opposite' ,'grind' ,'reserve' ,'swallow' ,'decline' ,'ridge' ,'ice' ,'pad' ,'state' ,'rag' ,'ace' ,'wilson' ,'jackson' ,'word' ,'print' ,'minor' ,'whip' ,'jam' ,'retreat' ,'lose' ,'bright' ,'troll' ,'claim' ,'transport' ,'rig' ,'mould' ,'standard' ,'bind' ,'plain' ,'prick' ,'material' ,'defence' ,'relief' ,'pall' ,'bring' ,'stir' ,'cup' ,'shape' ,'tail' ,'wheel' ,'grass' ,'offset' ,'begin' ,'accept' ,'flux' ,'core' ,'cycle' ,'liquid' ,'channel' ,'plaster' ,'pocket' ,'resistance' ,'slow' ,'care' ,'question' ,'guide' ,'suffer' ,'come out' ,'fill' ,'retire' ,'knock' ,'hood' ,'secure' ,'trouble' ,'value' ,'lap' ,'variation' ,'jerk' ,'resolution' ,'soak' ,'swell' ,'shower' ,'wide' ,'movement' ,'relieve' ,'notice' ,'concentrate' ,'operation' ,'large' ,'late' ,'compound' ,'spring' ,'capital' ,'radical' ,'gather' ,'flap' ,'gauge' ,'aim' ,'bell' ,'edge' ,'slide' ,'panel' ,'lodge' ,'softness' ,'share' ,'ray' ,'runner' ,'passage' ,'space' ,'undercut' ,'blind' ,'little' ,'strong' ,'factor' ,'expose' ,'put' ,'peg' ,'regard' ,'exercise' ,'edward' ,'explode' ,'put out' ,'projection' ,'crank' ,'bid' ,'continue' ,'steady' ,'practice' ,'hammer' ,'regenerate' ,'apply' ,'race' ,'badly' ,'make out' ,'dim' ,'tramp' ,'skin' ,'idle' ,'hole' ,'left' ,'depression' ,'tag' ,'slice' ,'knot' ,'try' ,'labor' ,'dump' ,'extract' ,'stage' ,'border' ,'compact' ,'waver' ,'blaze' ,'cradle' ,'bust' ,'special' ,'around' ,'living' ,'purge' ,'sheet' ,'common' ,'paddle' ,'mat' ,'moderate' ,'far' ,'curve' ,'bat' ,'scene' ,'canvas' ,'pot' ,'love' ,'interest' ,'clap' ,'james' ,'function' ,'spiral' ,'grow' ,'brand' ,'submit' ,'bay' ,'radiate' ,'king' ,'program' ,'indifferent' ,'representation' ,'maintain' ,'divine' ,'smith' ,'romance' ,'inside' ,'tongue' ,'treat' ,'stake' ,'grand' ,'power' ,'zero' ,'perch' ,'gentle' ,'spat' ,'fine' ,'clutch' ,'plunge' ,'pace' ,'vote' ,'company' ,'boom' ,'surface' ,'digest' ,'character' ,'tread' ,'allow' ,'distribute' ,'rap' ,'complete' ,'lie' ,'empty' ,'yoke' ,'fox' ,'inactive' ,'heart' ,'spill' ,'plant' ,'put on' ,'alternate' ,'cat' ,'west' ,'stem' ,'poke' ,'slug' ,'slick' ,'scrape' ,'water' ,'stream' ,'nail' ,'canvass' ,'spur' ,'under' ,'sit' ,'suit' ,'single' ,'smell' ,'engage' ,'rally' ,'gross' ,'school' ,'gum' ,'spell' ,'speed' ,'belt' ,'screw' ,'lean' ,'general' ,'flood' ,'write' ,'mantle' ,'translate' ,'smoke' ,'sure' ,'skim' ,'combine' ,'drink' ,'grip' ,'lost' ,'pattern' ,'weight' ,'narrow' ,'content' ,'wish' ,'concord' ,'bounce' ,'exposure' ,'just' ,'introduce' ,'day');
 *
 *
 */

class cp_general
{
				public static $tag_net;

				function __construct()
				{
								$tag_net = explode( ',', get_option( 'callpress_tag_net' ) );
				}
				
 				public static function update_all_taxonomy_count()
				{
								global $wpdb;
								$wpdb->show_errors();
								$wpdb->debug=true;
								$taxonomies = $wpdb->get_results('SELECT DISTINCT taxonomy FROM wp_term_taxonomy', 'ARRAY_N');

								foreach( (array)$taxonomies as $taxonomy )
								{
												$sql = 'SELECT term_taxonomy_id FROM wp_term_taxonomy WHERE taxonomy="'.$taxonomy[0].'"';
												$tt_id = $wpdb->get_results($sql, 'ARRAY_N');
												foreach( (array)$tt_id as $term )
												{

																$sql = 'SELECT COUNT( * ) 
																				FROM wp_term_relationships
																				WHERE term_taxonomy_id = '.$term[0];
																$count = $wpdb->get_results($sql, 'ARRAY_N');
																$wpdb->update( 'wp_term_taxonomy', array( 'count' => $count[0][0] ) , array( 'term_taxonomy_id' => $term[0], 'taxonomy' => $taxonomy[0] ) );  

												}
								}
				}
				public static function cp_email( $reason, $post = null )
				{
								/*
								 * $reasons is a string of 3 capital letters that defines the actions taken by the cp_email
								 * The content of the email sent out is most directly altered by this 3 letter code
								 *
								 *	example cp_email('TCO', get_post($post_id, 'OBJECT'))
								 *
								 *	here TCO stands for Ticket Close Out.
								 *
								 * Reasons:
								 *
								 *		charAt(1) 				 
								 *			T = Ticket
								 *			S = Site
								 *			E = Error
								 *		suffix
								 *			For T:
								 *				CO = Close Out
								 *				RO = Reopen
								 *				CR = Creation
								 *				AL = Alert
								 *			For S:
								 *				TODO
								 *			FOR E:
								 *				RP = Report
								 *				FL = Site Failure
								 *
								 */

								global $wpdb;

								if( substr( $reason, 0, 1 ) == 'T' )
								{
												if( $post !== null )
												{
																$id = $post->ID;
																$title = $post->post_title;
																$users_attached = get_post_custom_values( 'users_attached', $id );

																if( substr( $reason, 1 ) == 'CO' )
																				$content = ' was recently closed. If you require further assistance please check this link.\n';

																elseif( substr( $reason, 1 ) == 'RO' )
																				$content = ' was reopened due to new ticket information or an oversight on our part. Feel free to leave any additional information with us here:\n';

																elseif( substr( $reason, 1 ) == 'CR' )
																				$content = ' has recently been created! We are working diligently to solve your problem and will post updates here:\n';

																elseif( substr( $reason, 1 ) == 'AL' );
																else return false;
																foreach( (array)$users_attached as $user )
																{
																				if( !$user_name = get_user_meta( $user, 'first_name', TRUE ) )
																				{
																								$user_name = get_option( 'company_name', 'CallPress' ).' User';
																				}

																				$email_content = 'Hey '.$user_name.'!\n
																								The ticket '.$title.$content.'
																								'.get_bloginfo( 'url' ).'/?navigation=tickets&p='.$id.'\n\n

																								If you still have questions or a new problem has arisen please contact '.get_option('company_name', 'CallPress').' at\n e-mail: '.get_option( 'company_email_dump', 'jrlemieux@playground_sandbox.net' ).'\n or feel free to reply to this e-mail.
																								Or feel free to tell us about your problem here:\n
																								'.get_bloginfo( 'url' ).'/?navigation=tickets';

																				if( $phone = get_option( 'company_phone' ) )
																								$email_content .= '\n Phone: '.$phone;

																				$headers = ' From: '.get_option( 'company_email_dump', 'jrlemieux@playground_sandbox.net' );
																				mail( get_profile( 'user_email', $user ), 'CallPress Ticket Closed', $email_content, $headers ); 
																}

																$ticket_class = wp_get_object_terms( $id, 'ticket_classifications' );
																$sql = "SELECT * FROM wp_usermeta WHERE meta_key='".$ticket_class."_ticket_submission'";
																$employee_email_ids = $wpdb->get_results( $sql, 'ARRAY_N' );

																foreach( (array)$employee_email_ids as $id )
																{
																				if( !$user_name = get_user_meta( $user, 'first_name', TRUE ) )
																				{
																								$user_name = get_option( 'company_name', 'CallPress' ).' User';
																				}
																				$content ='Hello '.$user_name.'!\n
																								The ticket '.$title.' has been closed. If you are still curious about the ticket, you\ncan get to it here:\n' 
																								.get_bloginfo( 'url' ).'/?navigation=tickets&p='.$id.'\n\n';
																				'Thanks\n'.get_option('company_name');

																				$headers = ' From: '.get_option( 'company_email_dump', 'jrlemieux@playground_sandbox.net' );
																				mail( get_profile( 'user_email', $id ), 'CallPress Ticket Closed', $email_content, $headers ); 

																}

												}
								}	
								elseif( substr( $reason, 0, 1 ) == 'S' )
								{

								}	
								elseif( substr( $reason, 0, 1) == 'E' )	
								{

								}
								else
								{
												echo 'There has been a system error. Please contact an administrator.';
								}
				}
}

?>
