<?php
class CallPress {
	//This will remain an empty construct until I find a need for it.
	function __constuct() {}
	
	/* init makes all the necessary declarations and defanitions and runs on the wordpress init action. The add_action function for this function is located at the bottom of this function.*/
	public static function init() {

		// The ticket labels are defined here. 
		$ticket_labels = array(
			'name' => __('Tickets'),
			'singular_name' => __('Ticket'),
			'add_new' => _x('Add New', 'ticket'),
			'add_new_item' => __('Add New Ticket'),
			'edit_item' => __('Edit Ticekts'),
			'new_item' => __('New Ticket'),
			'view_item' => __('View Ticket'),
			'search_items' => __('Search tickets'),
			'not_found' => __('Ticket not found'),
			'not_found_in_trash' => __('Ticket not in trash'),
			'parent_item_colon' => __('Ticket'),
			'menu_name' => __('Ticket Menu')
		);//end var ticket_labels

		//This array is used to define rewrite rules
		$rewrite = array(
			'slug' => 'ticket',
			'front' => true,
		);
		
		// This array sets the diffults for the post type ticket. It is highly recomended that you do not edit defaults form here but rely on the action after this array delcartion 
		$ticket_args = array( 
			'label' => __('Tickets'),
			'labels' => $ticket_labels,
			'description' => __( 'Tickets are the core building blocks of CallPress. The ticket structure is an asysclic directed graph. Tickets are hierarchical and can also be merged into other tickets. This puts duplication control in the users hands.' ),
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 5,
			'menu_icon' => false, //todo
			//'capability_type' //WTF?
			//'capability' => ,  //todo maybe?
			'map_meta_cap' => true,
			'hierarchical' => true,
			//'supports' => '',
			//'taxonomies' => //todo
			//'permalink_epmask' => //wtf
			'rewrite' => $rewrite,
			'query_var' => 'ticket',
			'can_export' => true,
			'show_in_nav_menus' => true,
		);// end var ticket args

		do_action( 'callpress_before_tag_net' );

		if( !get_site_option( 'callpress_tag_net' ) ) {
			add_option( 'callpress_tag_net', implode( ',', array( 'combination', 'unmatched', 'on', 'and', 'people', 'crazy', 'strange', 'acting', 'type', 'why', 'of', 'should', 'would', 'could', 'for', 'i', 'will', 'if', 'am', 'from', 'more', 'than', 'he', 'she', 'it', 'they', 'we', 'was', 'were', 'being', 'been', 'be', 'are', 'is', 'their', "they\'re", 'there', 'us', 'you',  'an', 'so', 'the' ,'a'  ,'break' ,'cut' ,'run' ,'play' ,'make' ,'light' ,'set' ,'hold' ,'clear' ,'give' ,'draw' ,'take' ,'fall' ,'pass' ,'head' ,'call' ,'carry' ,'charge' ,'point' ,'catch' ,'turn' ,'check' ,'get' ,'close' ,'line' ,'right' ,'lift' ,'cover' ,'open' ,'go' ,'beat' ,'work' ,'drive' ,'roll' ,'drop' ,'place' ,'lead' ,'raise' ,'clean' ,'mark' ,'base' ,'heavy' ,'return' ,'blow' ,'block' ,'back' ,'strike' ,'rise' ,'good' ,'touch' ,'stock' ,'snap' ,'slip' ,'down' ,'round' ,'keep' ,'stick' ,'square' ,'white' ,'see' ,'crack' ,'sound' ,'stand' ,'follow' ,'pull' ,'flat' ,'direct' ,'order' ,'hit' ,'dip' ,'spread' ,'post' ,'settle' ,'ground' ,'center' ,'short' ,'shift' ,'press' ,'twist' ,'form' ,'swing' ,'start' ,'pitch' ,'well' ,'shoot' ,'release' ,'top' ,'deal' ,'pound' ,'free' ,'black' ,'face' ,'stop' ,'hard' ,'support' ,'pack' ,'case' ,'come' ,'pick' ,'jump' ,'develop' ,'wash' ,'straight' ,'deep' ,'key' ,'double' ,'last' ,'dead' ,'dress' ,'hot' ,'move' ,'step' ,'field' ,'change' ,'throw' ,'sign' ,'reduce' ,'tap' ,'discharge' ,'have' ,'cast' ,'strain' ,'spot' ,'stretch' ,'advance' ,'hook' ,'soft' ,'burn' ,'fly' ,'control' ,'break up' ,'strip' ,'separate' ,'dull' ,'force' ,'flash' ,'rule' ,'pop' ,'bar' ,'match' ,'live' ,'dry' ,'level' ,'end' ,'score' ,'crash' ,'land' ,'rack' ,'position' ,'hang' ,'rest' ,'bank' ,'piece' ,'high' ,'stamp' ,'service' ,'present' ,'scale' ,'figure' ,'shell' ,'loose' ,'tie' ,'still' ,'part' ,'solid' ,'find' ,'fire' ,'shot' ,'flip' ,'address' ,'quarter' ,'flush' ,'tender' ,'rough' ,'split' ,'train' ,'squeeze' ,'shock' ,'spike' ,'regular' ,'bad' ,'home' ,'feel' ,'frame' ,'bound' ,'extend' ,'exchange' ,'scratch' ,'active' ,'drag' ,'number' ,'drift' ,'yield' ,'job' ,'wild' ,'take in' ,'upset' ,'trim' ,'out' ,'big' ,'leave' ,'range' ,'fair' ,'walk' ,'slack' ,'puff' ,'fix' ,'string' ,'register' ,'kill' ,'fit' ,'cold' ,'float' ,'blast' ,'smash' ,'model' ,'sweet' ,'choke' ,'do' ,'cross' ,'first' ,'track' ,'screen' ,'tight' ,'hack' ,'dark' ,'balance' ,'stroke' ,'mean' ,'stay' ,'crown' ,'blue' ,'serve' ,'fret' ,'low' ,'pick up' ,'study' ,'foul' ,'stone' ,'hand' ,'ride' ,'plate' ,'show' ,'rush' ,'seat' ,'issue' ,'bolt' ,'offer' ,'waste' ,'ring' ,'subject' ,'name' ,'air' ,'finish' ,'bear' ,'lock' ,'sharp' ,'gray' ,'rail' ,'bull' ,'meet' ,'wind' ,'grain' ,'push' ,'color' ,'sweep' ,'grant' ,'true' ,'set up' ,'book' ,'answer' ,'shake' ,'pin' ,'easy' ,'review' ,'band' ,'second' ,'transfer' ,'master' ,'represent' ,'attack' ,'brush' ,'escape' ,'loop' ,'act' ,'seal' ,'time' ,'hunt' ,'fast' ,'tone' ,'section' ,'colour' ,'flare' ,'port' ,'even' ,'load' ,'hurt' ,'trace' ,'account' ,'bond' ,'draft' ,'outside' ,'think' ,'wave' ,'tip' ,'pit' ,'guard' ,'house' ,'freeze' ,'project' ,'life' ,'green' ,'look' ,'switch' ,'bow' ,'flow' ,'die' ,'foot' ,'march' ,'opening' ,'reverse' ,'jack' ,'design' ,'render' ,'side' ,'firm' ,'heave' ,'secret' ,'raw' ,'cloud' ,'mold' ,'root' ,'away' ,'young' ,'stuff' ,'approach' ,'chip' ,'up' ,'bag' ,'kick' ,'game' ,'corner' ,'be' ,'nose' ,'court' ,'take out' ,'stall' ,'shaft' ,'dig' ,'patch' ,'plug' ,'date' ,'sack' ,'visit' ,'measure' ,'express' ,'seed' ,'test' ,'bite' ,'sink' ,'shade' ,'report' ,'mount' ,'grade' ,'beam' ,'natural' ,'rank' ,'bare' ,'small' ,'front' ,'broken' ,'warm' ,'brace' ,'receive' ,'mate' ,'use' ,'watch' ,'take up' ,'reach' ,'gain' ,'better' ,'way' ,'record' ,'voice' ,'bill' ,'credit' ,'thick' ,'real' ,'spare' ,'condition' ,'thrust' ,'grey' ,'box' ,'fresh' ,'joint' ,'hall' ,'positive' ,'process' ,'rich' ,'note' ,'man' ,'superior' ,'wrong' ,'tumble' ,'medium' ,'splash' ,'link' ,'full' ,'bed' ,'course' ,'pole' ,'c' ,'view' ,'thin' ,'major' ,'board' ,'quiet' ,'union' ,'dirty' ,'spin' ,'ball' ,'taste' ,'counter' ,'mind' ,'cutting' ,'card' ,'camp' ,'miss' ,'count' ,'long' ,'shadow' ,'pay' ,'crop' ,'flight' ,'extension' ,'collar' ,'know' ,'glass' ,'silver' ,'thing' ,'dash' ,'bottom' ,'horn' ,'smack' ,'title' ,'bend' ,'average' ,'fold' ,'style' ,'tack' ,'tough' ,'read' ,'defense' ,'deposit' ,'chain' ,'bitter' ,'hitch' ,'star' ,'commission' ,'floor' ,'bang' ,'forward' ,'prime' ,'give up' ,'puddle' ,'command' ,'wing' ,'say' ,'pinch' ,'focus' ,'help' ,'body' ,'glow' ,'net' ,'action' ,'new' ,'recall' ,'cry' ,'proof' ,'ruffle' ,'negative' ,'turn out' ,'dock' ,'build' ,'come up' ,'deliver' ,'butt' ,'trust' ,'burst' ,'bob' ,'picture' ,'weak' ,'tease' ,'trap' ,'bowl' ,'flag' ,'passing' ,'bridge' ,'division' ,'flick' ,'crush' ,'convert' ,'ruin' ,'trip' ,'save' ,'queen' ,'withdraw' ,'wear' ,'dissolve' ,'trade' ,'smooth' ,'contract' ,'feed' ,'correct' ,'circle' ,'rear' ,'false' ,'source' ,'image' ,'contact' ,'like' ,'chop' ,'pool' ,'clip' ,'running' ,'spoil' ,'mass' ,'get off' ,'stiff' ,'rat' ,'so' ,'connect' ,'click' ,'host' ,'crab' ,'fail' ,'bit' ,'talk' ,'centre' ,'collapse' ,'obscure' ,'pile' ,'heel' ,'club' ,'capture' ,'pump' ,'heat' ,'demand' ,'whistle' ,'carrier' ,'irregular' ,'mature' ,'cap' ,'sour' ,'shine' ,'reference' ,'cool' ,'mouth' ,'upgrade' ,'clinch' ,'opposite' ,'grind' ,'reserve' ,'swallow' ,'decline' ,'ridge' ,'ice' ,'pad' ,'state' ,'rag' ,'ace' ,'wilson' ,'jackson' ,'word' ,'print' ,'minor' ,'whip' ,'jam' ,'retreat' ,'lose' ,'bright' ,'troll' ,'claim' ,'transport' ,'rig' ,'mould' ,'standard' ,'bind' ,'plain' ,'prick' ,'material' ,'defence' ,'relief' ,'pall' ,'bring' ,'stir' ,'cup' ,'shape' ,'tail' ,'wheel' ,'grass' ,'offset' ,'begin' ,'accept' ,'flux' ,'core' ,'cycle' ,'liquid' ,'channel' ,'plaster' ,'pocket' ,'resistance' ,'slow' ,'care' ,'question' ,'guide' ,'suffer' ,'come out' ,'fill' ,'retire' ,'knock' ,'hood' ,'secure' ,'trouble' ,'value' ,'lap' ,'variation' ,'jerk' ,'resolution' ,'soak' ,'swell' ,'shower' ,'wide' ,'movement' ,'relieve' ,'notice' ,'concentrate' ,'operation' ,'large' ,'late' ,'compound' ,'spring' ,'capital' ,'radical' ,'gather' ,'flap' ,'gauge' ,'aim' ,'bell' ,'edge' ,'slide' ,'panel' ,'lodge' ,'softness' ,'share' ,'ray' ,'runner' ,'passage' ,'space' ,'undercut' ,'blind' ,'little' ,'strong' ,'factor' ,'expose' ,'put' ,'peg' ,'regard' ,'exercise' ,'edward' ,'explode' ,'put out' ,'projection' ,'crank' ,'bid' ,'continue' ,'steady' ,'practice' ,'hammer' ,'regenerate' ,'apply' ,'race' ,'badly' ,'make out' ,'dim' ,'tramp' ,'skin' ,'idle' ,'hole' ,'left' ,'depression' ,'tag' ,'slice' ,'knot' ,'try' ,'labor' ,'dump' ,'extract' ,'stage' ,'border' ,'compact' ,'waver' ,'blaze' ,'cradle' ,'bust' ,'special' ,'around' ,'living' ,'purge' ,'sheet' ,'common' ,'paddle' ,'mat' ,'moderate' ,'far' ,'curve' ,'bat' ,'scene' ,'canvas' ,'pot' ,'love' ,'interest' ,'clap' ,'james' ,'function' ,'spiral' ,'grow' ,'brand' ,'submit' ,'bay' ,'radiate' ,'king' ,'program' ,'indifferent' ,'representation' ,'maintain' ,'divine' ,'smith' ,'romance' ,'inside' ,'tongue' ,'treat' ,'stake' ,'grand' ,'power' ,'zero' ,'perch' ,'gentle' ,'spat' ,'fine' ,'clutch' ,'plunge' ,'pace' ,'vote' ,'company' ,'boom' ,'surface' ,'digest' ,'character' ,'tread' ,'allow' ,'distribute' ,'rap' ,'complete' ,'lie' ,'empty' ,'yoke' ,'fox' ,'inactive' ,'heart' ,'spill' ,'plant' ,'put on' ,'alternate' ,'cat' ,'west' ,'stem' ,'poke' ,'slug' ,'slick' ,'scrape' ,'water' ,'stream' ,'nail' ,'canvass' ,'spur' ,'under' ,'sit' ,'suit' ,'single' ,'smell' ,'engage' ,'rally' ,'gross' ,'school' ,'gum' ,'spell' ,'speed' ,'belt' ,'screw' ,'lean' ,'general' ,'flood' ,'write' ,'mantle' ,'translate' ,'smoke' ,'sure' ,'skim' ,'combine' ,'drink' ,'grip' ,'lost' ,'pattern' ,'weight' ,'narrow' ,'content' ,'wish' ,'concord' ,'bounce' ,'exposure' ,'just' ,'introduce' ,'day') ) );

		}
		// Callpress action ticket_before_register alls for rewriting defaults.
		do_action( 'callpress_ticket_before_register' );

		//Register the type ticket with wordpress with all of the arguments defined above.
		register_post_type( 'ticket', $ticket_args );
		
		do_action( 'callpress_init' );
	}//end init
	public static function scripts() {

		do_action( 'callpress_js' );	
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js');
    wp_enqueue_script( 'jquery' );	
		wp_register_script( 'ticket', get_bloginfo( 'template_directory' ).'/js/ticket.js' );
		wp_enqueue_script( 'ticket' );

	}

}//end CallPress

?>
