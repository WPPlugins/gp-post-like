<?php
/*
Plugin Name: Gp post Like
Plugin URI: 
Version: 1.0
Author: Ganesh Paygude
Description: Allow user add post like button above or below post content.
*/

/* Setup the plugin. */
add_action( 'plugins_loaded', 'gppl_plugin_setup' );

/* Register plugin activation hook. */
register_activation_hook( __FILE__, 'gppl_plugin_activation' );

/* Register plugin activation hook. */
register_deactivation_hook( __FILE__, 'gppl_plugin_deactivation' );
/**
 * Do things on plugin activation.
 *
 */
function gppl_plugin_activation() {
	/* Flush permalinks. */
    flush_rewrite_rules();
}
/**
 * Flush permalinks on plugin deactivation.
 */
function gppl_plugin_deactivation() {
    flush_rewrite_rules();
}
function gppl_plugin_setup() {
// create custom plugin settings menu
/* Get the plugin directory URI. */
	define( 'GP_POST_LIKE_PLUGIN_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
	add_action('admin_menu', 'gppl_plugin_create_menu');

}

function gppl_plugin_create_menu() {

	//create new top-level menu
	add_menu_page('GP post Like button', 'GP post Like button  Settings', 'administrator', __FILE__, 'gppl_plugin_settings_page' , plugins_url('/images/liked-icon.png', __FILE__) );

	//call register settings function
	add_action( 'admin_init', 'gppl_register_plugin_settings' );
}

function eol_wp_enqueue_script() {    
	
	wp_enqueue_script( 'gppl-script-handle', GP_POST_LIKE_PLUGIN_URI . 'js/gppl-script.js', array( 'jquery' ), 0.1, true );
	wp_enqueue_style('gppl-like-button-style', GP_POST_LIKE_PLUGIN_URI.'css/gppl-style.css', false, '1.0', 'all');
	?>
	<script type="text/javascript">
	var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
	var nonce = '<?php echo wp_create_nonce('ajax-nonce'); ?>';
	</script>
	<?php
}
add_action( 'wp_head', 'eol_wp_enqueue_script' );


function gppl_register_plugin_settings() {
	//register our settings	
	register_setting( 'gppl-plugin-settings-group', 'gppl_add_post_like_button' );
	register_setting( 'gppl-plugin-settings-group', 'gppl_beforecontent_like_button' );
	register_setting( 'gppl-plugin-settings-group', 'gppl_aftercontent_like_button' );
}

function gppl_plugin_settings_page() {
?>
<div class="wrap">
<h2>Add Post like button on post:</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'gppl-plugin-settings-group' ); ?>
    <?php do_settings_sections( 'gppl-plugin-settings-group' ); ?>
    <table class="form-table">
	<?php 	$gppl_add_post_like_button = get_option( 'gppl_add_post_like_button' );	?>	
	
	<tr valign="top">
        <th scope="row">Add Post like button on post:</th>
        <td><input type='checkbox' id='gppl_add_post_like_button' name='gppl_add_post_like_button' value='1' <?php echo checked( $gppl_add_post_like_button, 1, false );?> /></td>
    </tr>
	
	<?php 	$gppl_beforecontent_like_button = get_option( 'gppl_beforecontent_like_button' );	?>	
	
	<tr valign="top">
        <th scope="row">Add Like button Before content:</th>
        <td><input type='checkbox' id='gppl_beforecontent_like_button' name='gppl_beforecontent_like_button' value='1' <?php echo checked( $gppl_beforecontent_like_button, 1, false );?> /></td>
    </tr>
	
	<?php 	$gppl_aftercontent_like_button = get_option( 'gppl_aftercontent_like_button' );	?>	
	
	<tr valign="top">
        <th scope="row">Add Like button After content:</th>
        <td><input type='checkbox' id='gppl_aftercontent_like_button' name='gppl_aftercontent_like_button' value='1' <?php echo checked( $gppl_aftercontent_like_button, 1, false );?> /></td>
    </tr>
			
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php }

$gppl_add_post_like_button = get_option('gppl_add_post_like_button');

if(checked( $gppl_add_post_like_button, 1, false )){	

	add_action('wp_ajax_nopriv_gppl_post_like', 'gppl_post_like');
	add_action('wp_ajax_gppl_post_like', 'gppl_post_like');
	
	
	function gppl_post_like()
{
    // Check for nonce security
    $nonce = $_POST['nonce'];
  
    if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
        die ( 'Busted!');
     
    if(isset($_POST['post_like']))
    {
        // Retrieve user IP address
        $ip = $_SERVER['REMOTE_ADDR'];
        $post_id = $_POST['post_id'];

         
        // Get voters'IPs for the current post
        $meta_IP = get_post_meta($post_id, "voted_IP");
        $voted_IP = $meta_IP[0];
 
        if(!is_array($voted_IP))
            $voted_IP = array();         
        
        $gp_post_like_count = get_post_meta($post_id, "votes_count", true);
 
        // Use has already voted ?
        if(!gp_post_like_already($post_id))
        {
            $voted_IP[$ip] = time();
 
            // Save IP and increase votes count
           update_post_meta($post_id, "voted_IP", $voted_IP);
           update_post_meta($post_id, "votes_count", ++$gp_post_like_count);             
           
            echo $gp_post_like_count;

        }
        else
            echo "already";
    }
    exit;
}

function gp_post_like_already($post_id)
{
    global $timebeforerevote;
 
    // Retrieve post votes IPs
    $meta_IP = get_post_meta($post_id, "voted_IP");
    $voted_IP = $meta_IP[0];
     
    if(!is_array($voted_IP))
        $voted_IP = array();
         
    // Retrieve current user IP
    $ip = $_SERVER['REMOTE_ADDR'];
     
    // If user has already voted
    if(in_array($ip, array_keys($voted_IP)))
    {
     return true;             
      
    }else{
		return false;
	}
     
    
}
	function gppl_post_like_button_html($atts)
	{
			
		$theme_name = get_current_theme();
		$post_atts = shortcode_atts( array(
        'post_id' => '',
		), $atts );
		$post_id =  $post_atts[ 'post_id' ];
	 
		$gp_post_like_count = get_post_meta($post_id, "votes_count", true);
	 
		$output = '<p class="post-like">';
		if(gp_post_like_already($post_id))
			$output .= ' <span title="'.__('I like this article', $theme_name).'" class="like alreadyvoted"></span>';
		else
			$output .= '<a href="#" data-post_id="'.$post_id.'">
						<span  title="'.__('I like this article', $theme_name).'"class="qtip like"></span>						
					</a>';
		$output .= '<span class="count">'.$gp_post_like_count.'</span><span  class="liked_msg"></span></p>';
		 
		return $output;
	}
	
add_shortcode( 'gppostlike', 'gppl_post_like_button_html' );

	function gppl_post_like_button_add_in_content($content) {
		$gppl_beforecontent_like_button = get_option('gppl_beforecontent_like_button');
		$gppl_aftercontent_like_button = get_option('gppl_aftercontent_like_button');	
		$aftercontent = $beforecontent = '[gppostlike post_id="'.get_the_id().'"]';	
		
		$fullcontent = $content;
		if(checked( $gppl_beforecontent_like_button, 1, false ))
		{	
			$fullcontent = $beforecontent . $content;
		}
		
		if(checked( $gppl_aftercontent_like_button, 1, false ))
		{	
			$fullcontent = $content . $aftercontent;
		}
		
		if(checked( $gppl_aftercontent_like_button, 1, false ) && checked( $gppl_beforecontent_like_button, 1, false ) )
		{	
			$fullcontent = $beforecontent . $content . $aftercontent;
		}
		
		return $fullcontent;
	}
add_filter('the_content', 'gppl_post_like_button_add_in_content');

}
 ?>