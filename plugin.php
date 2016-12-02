<?php
	/*
	Plugin Name: Geeky Posh's Functionality Plugin
	Description: All of the important functionality of your site belongs in this.
	Version: 0.1
	License: MIT
	Author: Jenny Wu
	Author URI: http://www.geekyposh.com
	*/

//removes automatic placement of jetpack related posts
	function jetpackme_remove_rp() {
		$jprp = Jetpack_RelatedPosts::init();
		$callback = array( $jprp, 'filter_add_target_to_dom' );
		remove_filter( 'the_content', $callback, 40 );
		if ( class_exists( 'Jetpack_Likes' ) ) {
      		remove_filter( 'the_content', array( Jetpack_Likes::init(), 'post_likes' ), 30, 1 );
    	}
	}
// Remove invalid rel attribute values in the categorylist
	function remove_category_rel_from_category_list($thelist)
	{
		return str_replace('rel="category tag"', 'rel="tag"', $thelist);
	}
// Remove 'text/css' from our enqueued stylesheet
	function html5_style_remove($tag)
	{
		return preg_replace('~\s+type=["\'][^"\']++["\']~', '', $tag);
	}
//add async to enqueued scripts
	function gp_add_async($tag){
	    if (is_admin()){
	        return $tag;
	    } else{
	        return str_replace( ' src', ' async src', $tag );
    	}
	}
//remove sponsored category from appearing in jetpack related posts
	function jetpackme_filter_exclude_category( $filters ) {
		$filters[] = array( 'not' => array( 'term' => array( 'category.slug' => 'holiday',  'category.slug' => 'stuff') ) );
		return $filters;
	}

	/** admin stuff **/
	function gp_add_tinymce_plugin($plugin_array) {
		$plugin_array['gp_tc_button'] = plugins_url( '/emoji-button.js', __FILE__ );
		return $plugin_array;
	}
	function gp_register_my_tc_button($buttons) {
		array_push($buttons, "gp_tc_button");
		return $buttons;
	}
	function gp_tc_css() {
		wp_enqueue_style('gp-tc', plugins_url('/style.css', __FILE__));
	}
	function my_mce_buttons_2( $buttons ) {
		array_unshift( $buttons, 'styleselect' );
		return $buttons;
	}

//adds the custom styles to the editor dropdown
	function my_mce_before_init_insert_formats( $init_array ) {  
		$style_formats = array(  
			array(  
				'title' => '.underline',  
				'inline' => 'span',  
				'classes' => 'underline',
				'wrapper' => false,
				),  
			array(  
				'title' => 'tooltip',  
				'inline' => 'span',  
				'classes' => 'tooltip',
				'wrapper' => false,
				),
			array(  
				'title' => 'good',  
				'inline' => 'span',  
				'classes' => 'good',
				'wrapper' => false,
				),
			array(  
				'title' => 'questionable',  
				'inline' => 'span',  
				'classes' => 'questionable',
				'wrapper' => false,
				),
			array(  
				'title' => 'bad',  
				'inline' => 'span',  
				'classes' => 'bad',
				'wrapper' => false,
				),
			array(  
				'title' => 'small',  
				'inline' => 'small',
				'wrapper' => false,
				),
			array(  
				'title' => 'inline code',  
				'inline' => 'code',
				'wrapper' => false,
				),
			array(  
				'title' => 'definition',  
				'block' => 'dl',
				'wrapper' => true,
				),
			array(  
				'title' => 'definition term',  
				'inline' => 'dt',
				'wrapper' => false,
				),
			array(  
				'title' => 'definition description',  
				'inline' => 'dd',
				'wrapper' => false,
				),
			array(  
				'title' => 'ol-numbers',  
				'selector' => 'ol',
				'classes' => 'list',
				'wrapper' => true,
				),
			);  
		$init_array['style_formats'] = json_encode( $style_formats );  
		return $init_array;  
	} 

//add some additional fields to the post screen
	function admin_init(){
		add_meta_box("product-meta", "Product Details", "product_details", "post", "normal", "default");
		add_meta_box("disclaimer", "Disclaimer", "disclaimer", "post", "side", "default");
		add_meta_box("pinterest-meta", "Pinterest Image", "pinterest_image", "post", "side", "default");
		add_meta_box("purchase-link", "Purchase Link", "purchase_link", "products", "normal", "high");
		add_meta_box("purchase-price", "Price", "purchase_price", "products", "side", "high");
		add_meta_box("hide-meta", "Hide Post?", "hide_post", "post", "side", "high");
		add_editor_style( 'editor-style.css' );

		global $typenow;
		if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
			return;
		}
		if( ! in_array( $typenow, array( 'post', 'page' ) ) ){
			return;
		}
		if ( get_user_option('rich_editing') == 'true') {
			add_filter("mce_external_plugins", "gp_add_tinymce_plugin");
			add_filter('mce_buttons', 'gp_register_my_tc_button');
		}
	}

	function product_details(){
		global $post;
		$custom = get_post_custom($post-> ID);
		$product_name = $custom["product_name"][0];
		$brand_name = $custom["brand_name"][0];
		$is_hg = $custom["is_hg"][0];
		echo '<p class="wp-review-field"><label>Brand Name: </label><input type="text" name="brand_name" value="' . esc_attr($brand_name).'" size="50"></p>';
		echo '<p class="wp-review-field"><label>Product Name: </label><input type="text" name="product_name" value="' . esc_attr($product_name).'" size="50"></p>';
		echo '<p class="wp-review-field"><label>Product Category: </label><select name="product_cat">'; 
		$option_values = array('','Cleanser','Toner', 'First Essence', 'Serum', 'Oil', 'Exfoliant', 'Moisturizer', 'Sunscreen', 'Mask','Lip Balm','Foundation','Primer','Concealer','Eyeshadow','Eyeliner','Mascara','Blush','Highlighter','Lipstick','Shampoo','Conditioner','Leave-in Treatment','Body Lotion');

		foreach($option_values as $key => $value) 
		{
			if($value == get_post_meta($object->ID, "product_cat", true))
			{
				?>
				<option selected><?php echo $value; ?></option>
				<?php    
			}
			else
			{
				?>
				<option><?php echo $value; ?></option>
				<?php
			}
		}
		echo '</select></p>';
		echo '<p class="wp-review-field"><label>Is HG (Y): </label><input type="text" name="is_hg" value="' . esc_attr($is_hg).'"></p>';
	}
	function pinterest_image(){
		global $post;
		$custom = get_post_custom($post-> ID);
		$pinterest_image = $custom["pinterest_image"][0];
		echo '<input name="pinterest_image" value="' . esc_attr($pinterest_image).'">';
	}
	function purchase_link(){
		global $post;
		$custom = get_post_custom($post-> ID);
		$purchase_link = $custom["purchase_link"][0];
		echo '<textarea name="purchase_link" style="width:100%;height:150px;">' . esc_attr($purchase_link).'</textarea>';
	}
	function purchase_price(){
		global $post;
		$custom = get_post_custom($post-> ID);
		$purchase_price = $custom["purchase_price"][0];
		echo '<input name="purchase_price" value="' . esc_attr($purchase_price).'">';
	}
	function disclaimer(){
		global $post;
		$custom = get_post_custom($post-> ID);
		$disclaimer = $custom["disclaimer"][0];
		echo '<textarea name="disclaimer" style="width:100%;height:150px;">' . esc_attr($disclaimer).'</textarea>';
	}
	function hide_post(){
		global $post;
		$custom = get_post_custom($post-> ID);
		$hide_home = $custom["hide_home"][0];
		$hide_rss = $custom["hide_rss"][0];
		if(esc_attr($hide_home) == "true" ){
			echo '<p><label>Hide from home page? </label> <input type="checkbox" name="hide_home" value="true" checked></p>';
		}else{
			echo '<p><label>Hide from home page? </label> <input type="checkbox" name="hide_home" value="true"></p>';
		}

		if(esc_attr($hide_rss) == "true" ){
			echo '<p><label>Hide from rss feed? </label> <input type="checkbox" name="hide_rss" value="true" checked></p>';
		}else{
			echo '<p><label>Hide from rss feed? </label> <input type="checkbox" name="hide_rss" value="true"></p>';
		}
	}
//save all the details!
	function save_details(){
		global $post;
		if(isset($_POST['is_hg'])){
			update_post_meta($post -> ID, "is_hg", $_POST["is_hg"]); 
		} 
		if(isset($_POST['product_name'])){
			update_post_meta($post -> ID, "product_name", $_POST["product_name"]);
		}
		if(isset($_POST['product_cat'])){

			update_post_meta($post -> ID, "product_cat", $_POST["product_cat"]);
		}
		if(isset($_POST['brand_name'])){
			update_post_meta($post -> ID, "brand_name", $_POST["brand_name"]);
		}
		if(isset($_POST['pinterest_image'])){
			update_post_meta($post -> ID, "pinterest_image", $_POST["pinterest_image"]);
		}
		if(isset($_POST['purchase_link'])){
			update_post_meta($post -> ID, "purchase_link", $_POST["purchase_link"]);
		}
		if(isset($_POST['purchase_price'])){
			update_post_meta($post -> ID, "purchase_price", $_POST["purchase_price"]);
		}
		if(isset($_POST['disclaimer'])){
			update_post_meta($post -> ID, "disclaimer", $_POST["disclaimer"]);
		}
		if(isset($_POST["hide_home"])){
			update_post_meta($post -> ID, "hide_home", $_POST["hide_home"]);
		}
		if(isset($_POST["hide_rss"])){
			update_post_meta($post -> ID, "hide_rss", $_POST["hide_rss"]);
		}
	}
//add shop product post type
	function products_register() {
		$args = array(
			'label'  => 'Products',
			'public' => true,
			'has_archive' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => null,
			'menu_icon' => 'dashicons-cart',
			'menu_position' => 5,
			'supports' => array('title','editor','thumbnail','comments'),
			//'rewrite' => array( 'slug' => 'shop' ),
			); 
		register_post_type( 'products' , $args );
	}

//add sales alert post type
	function alerts_register() {
		$args = array(
			'label'  => 'Sales Alerts',
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => null,
			'menu_icon' => 'dashicons-megaphone',
			'menu_position' => 5,
			'supports' => array('title','editor','thumbnail','custom-fields')
			); 
		register_post_type( 'alerts' , $args );
	}

//cleaning up things I don't need...use at your own risk
	function jquery_cleanup() {
		wp_deregister_script('jquery');
		wp_deregister_script('wp-embed');
		wp_register_script('jquery', '//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js',false, null,true);
		wp_enqueue_script('jquery');
	}
	//adding my own stuff
	function gp_enqueue_scripts() {
	//wp_register_script( 'cj', '//www.yceml.net/am_gen/7606162/include/allCj/am.js',false, null, true);
		wp_register_script( 'slider', '/js/lightslider.min.js', array ('jquery'), null, true);
		wp_register_style( 'gp-default-style', get_template_directory_uri() . '/style.css',false, false);
		wp_enqueue_style( 'gp-default-style' );
		wp_enqueue_script('slider');
	}
	function gp_cleanup_scripts(){
		if(!is_admin()){

			remove_action('wp_head', 'wp_print_scripts'); 
			remove_action('wp_head', 'wp_print_head_scripts', 9); 
			remove_action('wp_head', 'wp_enqueue_scripts', 1);
			add_action('wp_footer', 'wp_print_scripts', 5);
			add_action('wp_footer', 'wp_enqueue_scripts', 5);
			add_action('wp_footer', 'wp_print_head_scripts', 5); 		
		}
		/* so much crap to remove... */
	wp_deregister_style( 'AtD_style' ); // After the Deadline
    wp_deregister_style( 'jetpack_likes' ); // Likes
    wp_deregister_style( 'jetpack_related-posts' ); //Related Posts
    wp_deregister_style( 'jetpack-carousel' ); // Carousel
    wp_deregister_style( 'the-neverending-homepage' ); // Infinite Scroll
    wp_deregister_style( 'infinity-twentyten' ); // Infinite Scroll - Twentyten Theme
    wp_deregister_style( 'infinity-twentyeleven' ); // Infinite Scroll - Twentyeleven Theme
    wp_deregister_style( 'infinity-twentytwelve' ); // Infinite Scroll - Twentytwelve Theme
    wp_deregister_style( 'noticons' ); // Notes
    wp_deregister_style( 'post-by-email' ); // Post by Email
    wp_deregister_style( 'publicize' ); // Publicize
    wp_deregister_style( 'sharedaddy' ); // Sharedaddy
    wp_deregister_style( 'sharing' ); // Sharedaddy Sharing
    wp_deregister_style( 'stats_reports_css' ); // Stats
    wp_deregister_style( 'jetpack-widgets' ); // Widgets
    wp_deregister_style( 'jetpack-slideshow' ); // Slideshows
    wp_deregister_style( 'presentations' ); // Presentation shortcode
    wp_deregister_style( 'jetpack-subscriptions' ); // Subscriptions
    wp_deregister_style( 'tiled-gallery' ); // Tiled Galleries
    wp_deregister_style( 'widget-conditions' ); // Widget Visibility
    wp_deregister_style( 'jetpack_display_posts_widget' ); // Display Posts Widget
    wp_deregister_style( 'gravatar-profile-widget' ); // Gravatar Widget
    wp_deregister_style( 'widget-grid-and-list' ); // Top Posts widget
    wp_deregister_style( 'jetpack-widgets' ); // Widgets
    wp_dequeue_style('sharing');
    wp_dequeue_script('devicepx');
    //wp_dequeue_script('jetpack_resize');
    if(!is_singular()){
		//these shouldn't get loaded unless it's on a single page
    	//wp_dequeue_script('postmatic-social-login');
    	//wp_dequeue_style('postmatic-social-login');
    	wp_dequeue_script('visibility');
    	//wp_dequeue_script('epoch-handlebars');
    	//wp_dequeue_script('epoch');
    }
}


function reinsert_rss_feed() {
	echo '<link rel="alternate" type="application/rss+xml" title="' . get_bloginfo('sitename') . ' &raquo; RSS Feed" href="' . get_bloginfo('rss2_url') . '" />';
}
//make sure the featured image gets added to the feed
function featuredtoRSS($content) {
	global $post;
	if ( has_post_thumbnail( $post->ID ) ){
		$content = '<div>' . get_the_post_thumbnail( $post->ID, 'large', array( 'style' => 'margin-bottom: 15px;' ) ) . '</div>' . $content;
	}
	return $content;
}

function new_excerpt_length($length) {
	return 100;
}

function parallelize_hostnames($url, $id) {
	$url =  str_replace(parse_url(get_bloginfo('url'), PHP_URL_HOST), 'cdn.geekyposh.com', $url);
	return $url;
}

function fix_multisite_srcset( $sources ){
	foreach ( $sources as &$source ) {
		$sources[ $source['value'] ][ 'url' ] = str_replace('//www.geekyposh.com', '//cdn.geekyposh.com', $sources[ $source['value'] ][ 'url' ]);
	}
	return $sources;
}

function add_itemprop_image_markup($content){
    //Replace the instance with the itemprop image markup.
	$string = '<img';
	$replace = '<img itemprop="image"';
	$content = str_replace( $string, $replace, $content );
	return $content;
}

//the default image caption shortcode output was annoying
function fixed_img_caption_shortcode($attr, $content = null) {
	if ( ! isset( $attr['caption'] ) ) {
		if ( preg_match( '#((?:<a [^>]+>s*)?<img [^>]+>(?:s*</a>)?)(.*)#is', $content, $matches ) ) {
			$content = $matches[1];
			$attr['caption'] = trim( $matches[2] );
		}
	}
	$output = apply_filters( 'img_caption_shortcode', '', $attr, $content );
	if ( $output != '' )
		return $output;
	extract( shortcode_atts(array(
		'id'      => '',
		'align'   => 'text-center',
		'width'   => '',
		'caption' => ''
		), $attr));
	if ( 1 > (int) $width || empty($caption) )
		return $content;
	if ( $id ) $id = 'id="' . esc_attr($id) . '" ';
	return '<div ' . $id . 'class="wp-caption ' . esc_attr($align) . '" >'
	. do_shortcode( $content ) . '<p class="wp-caption-text">' . $caption . '</p></div>';
}

function ingredients_init() {
	// create a new taxonomy
	$labels = array(
		'name'                       => 'Ingredients',
		'singular_name'              => 'Ingredient',
		'search_items'               => 'Search Ingredients',
		'popular_items'              => 'Popular Ingredients',
		'all_items'                  => 'All Ingredients',
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => 'Edit Ingredient',
		'update_item'                => 'Update Ingredient',
		'add_new_item'               => 'Add New Ingredient',
		'new_item_name'              => 'New Ingredient Name',
		'separate_items_with_commas' => 'Separate ingredients with commas',
		'add_or_remove_items'        => 'Add or remove ingredients',
		'choose_from_most_used'      => 'Choose from the most used ingredients',
		'not_found'                  => 'No ingredients found.',
		'menu_name'                  => 'Ingredients'
		);
	register_taxonomy(
		'ingredients',
		'post',
		array(
			'labels' => $labels,
			'update_count_callback' => '_update_post_term_count',
			'rewrite' => array( 'slug' => 'ingredients', 'with_front' => true)
			)
		);
}
function shop_cat_init() {
	// create a new taxonomy
	$labels = array(
		'name'                       => 'Product Categories',
		'singular_name'              => 'Product Category',
		'search_items'               => 'Search Product Categories',
		'popular_items'              => 'Popular Product Categories',
		'all_items'                  => 'All Product Categories',
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => 'Edit Product Category',
		'update_item'                => 'Update Product Category',
		'add_new_item'               => 'Add New Product Category',
		'new_item_name'              => 'New Product Category Name',
		'separate_items_with_commas' => 'Separate product categories with commas',
		'add_or_remove_items'        => 'Add or remove product categories',
		'choose_from_most_used'      => 'Choose from the most used product categories',
		'not_found'                  => 'No product categories found.',
		'menu_name'                  => 'Product Categories'
		);
	register_taxonomy(
		'prod_cat',
		'products',
		array(
			'labels' => $labels,
			'update_count_callback' => '_update_post_term_count',
			'hierarchical' => true,
			'show_admin_column' => true
			)
		);
}

function gp_custom_query( $query ) {
	if ($query->is_main_query() ) {
		if($query->is_home()){
			$query->set( 'cat', '-62550');
			$query->set( 'meta_key', 'hide_home');
			$query->set( 'meta_compare', 'NOT EXISTS');
		}
		if($query->is_archive()){
			$query->set( 'cat', '-62550');
			$query->set( 'posts_per_page', 21);
		}
		if($query->is_feed()){
			$query->set( 'cat', '-62550,-53');
			$query->set( 'meta_key', 'hide_rss');
			$query->set( 'meta_compare', 'NOT EXISTS');
		}
		if($query->is_search()){
			$query->set( 'cat', '-62550,-53');
			$query->set( 'posts_per_page', 21);
			$query->set( 'post_type', 'post');
		}
	}
}
function my_nofollow($content) {
	return preg_replace_callback('/<a[^>]+/', 'my_nofollow_callback', $content);
}
function my_nofollow_callback($matches) {
	$link = $matches[0];
	$site_link = get_bloginfo('url');
	if (strpos($link, 'rel') === false) {
		$link = preg_replace("%(href=\S(?!$site_link))%i", 'rel="nofollow" $1', $link);
	} elseif (preg_match("%href=\S(?!$site_link)%i", $link)) {
		$link = preg_replace('/rel=\S(?!nofollow)\S*/i', 'rel="nofollow"', $link);
	}
	return $link;
}
//removes query strings at the end of js and css files
function rtp_rssv_scripts() {
	global $wp_scripts;
	if (!is_a($wp_scripts, 'WP_Scripts'))
		return;
	foreach ($wp_scripts->registered as $handle => $script)
		$wp_scripts->registered[$handle]->ver = null;
}

function rtp_rssv_styles() {
	global $wp_styles;
	if (!is_a($wp_styles, 'WP_Styles'))
		return;
	foreach ($wp_styles->registered as $handle => $style)
		$wp_styles->registered[$handle]->ver = null;
}
function gp_rss_media(){
	echo 'xmlns:media="http://search.yahoo.com/mrss/"
	xmlns:georss="http://www.georss.org/georss"';
}
function gp_attached_images(){
	global $post;
	?>
	<media:content url="<?php echo wp_get_attachment_url(get_post_thumbnail_id($post->ID)); ?>" type="image/jpeg" medium="image">
	<media:description type="plain"><![CDATA[<?php the_title_rss() ?>]]></media:description>
	<media:copyright><?php echo get_the_author_firstname() . " " . get_the_author_lastname(); ?></media:copyright>
</media:content>
<content:encoded><![CDATA[<?php the_excerpt_rss() ?>]]></content:encoded>
<?php
}
function stop_heartbeat(){
	global $pagenow;
	if ($pagenow != 'post.php' && $pagenow != 'post-new.php') wp_deregister_script('heartbeat');
}
function disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );	
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );	
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
}
function disable_emojis_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}


//load the following scripts asyncronously
/*function gp_defer_scripts( $tag, $handle, $src ) {
	$defer_scripts = array( 
		'slider',
		'Shopbop-widget-carouselFred',
		'Shopbop-widget-customjs',
		'jetpack_related-posts',
		'wp_review-js',
	);
    if ( in_array( $handle, $defer_scripts ) ) {
        return '<script type="text/javascript" src="' . $src . '" async="async"></script>' . "\n";
    }
    return $tag;
} 
add_filter( 'script_loader_tag', 'gp_defer_scripts', 10, 3 );*/
/** hook it up~ **/
remove_action('wp_head', 'wp_generator'); 
remove_action('wp_head', 'feed_links_extra', 3 );
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'index_rel_link');
//remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0 );
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'start_post_rel_link', 10, 0 );
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
add_action('admin_enqueue_scripts', 'gp_tc_css');
add_action('admin_head', 'admin_init');
//add_action( 'init', 'disable_emojis' );
add_action('init', 'alerts_register');
add_action('init', 'products_register');
add_action('init', 'ingredients_init' );
add_action('init', 'shop_cat_init');
add_action('init', 'stop_heartbeat', 1);
add_action( 'pre_get_posts', 'gp_custom_query', 1 );
add_action('save_post', 'save_details');
add_action( 'wp_enqueue_scripts', 'gp_enqueue_scripts', 1);
add_action( 'wp_enqueue_scripts', 'gp_cleanup_scripts', 99);
add_action( 'wp_head', 'reinsert_rss_feed');
add_action('wp_print_scripts', 'rtp_rssv_scripts', 99);
add_action('wp_print_footer_scripts', 'rtp_rssv_scripts', 99);
add_action('admin_print_styles', 'rtp_rssv_styles', 99);
add_action('wp_print_styles', 'rtp_rssv_styles', 99);
    // remove json_api
remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
//remove_action( 'rest_api_init', 'wp_oembed_register_route' );
add_filter( 'embed_oembed_discover', '__return_false' );
remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
remove_action( 'wp_head', 'wp_oembed_add_host_js' );
remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );
    // disable json_api
add_filter('json_enabled', '__return_false');
add_filter('json_jsonp_enabled', '__return_false');
//add_filter('rest_enabled', '__return_false');
//add_filter('rest_jsonp_enabled', '__return_false');

add_filter('excerpt_length', 'new_excerpt_length');
add_filter('login_errors',create_function('$a', "return null;"));
add_filter( 'jetpack_implode_frontend_css', '__return_false' );
add_filter( 'jetpack_relatedposts_filter_filters', 'jetpackme_filter_exclude_category' );
add_filter( 'jetpack_relatedposts_filter_post_context', '__return_empty_string' );
add_filter( 'jetpack_allow_per_post_subscriptions', '__return_true' );
add_filter('jpeg_quality', function($arg){return 100;});
add_filter('mce_buttons_2', 'my_mce_buttons_2');
add_filter( 'pre_option_link_manager_enabled', '__return_true' );
add_filter( 'rss2_ns', 'gp_rss_media' );
add_filter( 'rss2_item', 'gp_attached_images' );
add_filter( 'show_admin_bar', '__return_false');
add_filter('the_content', 'add_itemprop_image_markup', 2);
add_filter('the_content', 'my_nofollow');
add_filter('the_excerpt', 'my_nofollow');
add_filter('the_content_feed', 'featuredtoRSS');
add_filter('the_excerpt_rss', 'featuredtoRSS');
add_filter( 'tiny_mce_before_init', 'my_mce_before_init_insert_formats' ); 
add_filter( 'wp', 'jetpackme_remove_rp', 20 );
add_filter( 'jetpack_remove_login_form', '__return_true' ); //always use wordpress.com login form
add_filter('the_category', 'remove_category_rel_from_category_list'); // Remove invalid rel attribute
add_filter('style_loader_tag', 'html5_style_remove'); // Remove 'text/css' from enqueued stylesheet
//add_filter('script_loader_tag', 'gp_add_async'); // Add async to enqueued scripts
add_filter('the_seo_framework_indicator', '__return_false');
add_filter( 'the_seo_framework_og_image_after_featured', 'my_after_featured_fallback_image' );
function my_after_featured_fallback_image() {
   // No need to escape
   return '/wp-content/uploads/2016/05/skincare-empties-5.jpg';
}
//add_filter( 'wp_calculate_image_srcset', 'fix_multisite_srcset' );
//add_filter('wp_get_attachment_url', 'parallelize_hostnames', 10, 2);

add_shortcode( 'wp_caption', 'fixed_img_caption_shortcode' );
add_shortcode( 'caption', 'fixed_img_caption_shortcode' );
if (!is_admin()){ 
	add_action('init', 'jquery_cleanup'); 
	
}

?>