<?php
/*
Plugin Name: Stay Home, Stay Safe Notice
Description: Plugin to display Stay Safe, Stay Home notices for COVID-19.
Version:0.2
Author: Matt Lovett / WOW Media
Author URI: https://www.mattlovett.co.uk
*/
global $stayhome_affiliate;

function stayhome_get_ad_code( $options ){
	global $stayhome_affiliate;
	$banner_position = $options['banner_position'];
	$show_referral_link = $options['show_referral_link'];
	
	$referral_link = '';
	if($show_referral_link){
		$referral_link .= '<div class="hide-desktop"><div id="referral-link-box"><span id="referral-link">Display this Stay Home notice on your Wordpress site, <a href="https://www.mattlovett.co.uk/stay-home-plugin/" rel="nofollow noopener">click here</a>.</span></div></div>';
	}

		$html .= '<style>
		@media only screen and (min-width: 601px) {
.hide-desktop {
display: none !important;
}
}
    #sh-box {
        background-color: #0060bd;
        color: #FFFFFF;
        text-align: center;
        padding-top: 10px;
        padding-bottom: 10px;
        padding-right: 5px;
        padding-left: 5px;
        font-size: 20px;
        font-family: Arial, Helvetica, sans-serif;
        border-radius: 2px;
    }
    #small-font {
        padding-top: 15px;
        font-size: 12px;
    }
    #referral-link-box {
        text-align: center;
        margin-bottom: 15px;
    }
    #referral-link {
        font-size: 10px;
        color: #323332;
    }
		 
</style>
<div class="hide-desktop">
<div id="sh-box">
Stay Home. Stay Safe. Save Lives.<br>
   <span id="small-font">#COVID19</span>
</div>
</div>
'. $referral_link .' ';

	return $html;
}
    


/* This registers their ad settings as a Shortcode so instead of Auto placement they can choose where to insert, or as well as. */
    
    function stayhomeshortcode ( $atts = [], $tag = '' ){
        
        $options = get_option('stayhome-affiliate-options',$defaults);

          // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
 
    // override default attributes with user attributes
    $wporg_atts = shortcode_atts([
                                 'referral' => false,
                                 ], $atts, $tag);
 
            $show_referral_link = $wporg_atts['referral'];
            
     
         	$referral_link = '';
	if($show_referral_link){
		$referral_link .= '<div class="hide-desktop"><div id="referral-link-box"><span id="referral-link">Display this Stay Home notice on your Wordpress site, <a href="https://www.mattlovett.co.uk/stay-home-plugin/" rel="nofollow noopener">click here</a>.</span></div></div>';
	}

                
          
                $html .= '<style>
                	@media only screen and (min-width: 601px) {
.hide-desktop {
display: none !important;
}
}
    #sh-box {
        background-color: #0060bd;
        color: #FFFFFF;
        text-align: center;
        padding-top: 10px;
        padding-bottom: 10px;
        font-size: 20px;
        font-family: Arial, Helvetica, sans-serif;
        border-radius: 2px;
    }
    #small-font {
        padding-top: 15px;
        font-size: 12px;
    }
</style>

<div class="hide-desktop">
<div id="sh-box">
Stay Home. Stay Safe. Save Lives.<br>
   <span id="small-font">#COVID19</span>
</div>
</div>
'. $referral_link .' ';
      
          
            return $html;
    }
add_shortcode('stayhome', 'stayhomeshortcode');



class stayhome_affiliate{

	private $options = array();
	private $js = '';
	
	function stayhome_affiliate(){
		$this->options = get_option('stayhome-affiliate-options',$defaults);
		
		add_action( 'wp_footer', array($this, 'footer_scripts') );
		add_action( 'widgets_init', array($this, 'register_widgets') );
	
		add_filter( 'the_content', array($this,'content_filter') );
		
		if(is_admin())
			$this->admin_features();
	}
	
	function content_filter( $content ){
		if(!is_single() && !is_page()) return $content;
	
		
		switch( get_post_type() ){
			case'post':
				if(!$this->options['show_on_posts']) return $content;
				break;
			case'page':
				if(!$this->options['show_on_pages']) return $content;
				break;
			default:
				return $content;
				break;
		}

		$html = stayhome_get_ad_code( $this->options );
		
		if($this->options['banner_position'] == 'above_content')
			$content = $html . $content;
		else
			$content .= $html;
		return $content;
	}
	
	function admin_features(){
		add_action('admin_menu', array($this, 'admin_menu') );
		
	// 	if(!$this->options['affiliate_id'])
		// 	add_action('admin_notices', array($this, 'admin_notice'));
	}
	
	function admin_menu(){
		add_options_page( 'StayHome StaySafe Plugin: Settings', 'StayHome StaySafe', 'administrator', 'stayhome-options', array($this, 'stayhome_options') );
	}
	
	function admin_notice(){
		if(isset($_GET['page']) && $_GET['page'] == 'stayhome-options') return;
		?>
		<div class="updated">
			<p><?php _e( 'You need to configure the StayHome StaySafe plugin to decide where you wish to display notices. <a href="'.admin_url('options-general.php?page=stayhome-options').'">Click Here</a> to configure it now.', 'stayhome' ); ?></p>
		</div>
		<?php
	}
	
	function register_widgets(){
		register_widget( 'stayhome_Widget' );
	}
	
	function footer_scripts(){
		echo $this->js;
	}
	
	function add_script( $script ){
		$this->js .= $script;
	}
	
	function stayhome_options(){
		$defaults = array(
		    'banner_position' => 'below_content',
			'show_on_posts' => false,
			'show_on_pages' => false,
			'show_referral_link' => false,
			'show_notice_everywhere' => false
		);
		$updated = false;
		
		if(isset($_POST['stayhome_action']) && $_POST['stayhome_action'] == 'save_options'){
		    			$banner_position = (in_array($_POST['banner_position'],array('below_content','above_content'))) ? $_POST['banner_position'] : 'below_content';
			$show_on_posts = (isset($_POST['show_on_posts']) && $_POST['show_on_posts'] == 1) ? true : false;
			$show_on_pages = (isset($_POST['show_on_pages']) && $_POST['show_on_pages'] == 1) ? true : false;
			$show_referral_link = (isset($_POST['show_referral_link']) && $_POST['show_referral_link'] == 1) ? true : false;
		$show_notice_everywhere = (isset($_POST['show_notice_everywhere']) && $_POST['show_notice_everywhere'] == 1) ? true : false;
			$options = array(
			    'banner_position' => $banner_position,
				'show_on_posts' => $show_on_posts,
				'show_on_pages' => $show_on_pages,
				'show_referral_link' => $show_referral_link,
				'show_notice_everywhere' => $show_notice_everywhere
			);
			update_option('stayhome-affiliate-options',$options);
			
			$updated = true;
		}
		$options = get_option('stayhome-affiliate-options',$defaults);
		echo '<h1>StayHome StaySafe - Settings</h1>
Configure the plugin to automatically show a banner on your websites content below or above the content using the settings below. You can also configure Widgets for your website. Notices are automatically set to display on mobile sized screens only as a user is more likely to not be at home then.
<hr>';
		if($updated)
			echo '<div class="updated">Settings Updated!</div>';
		echo'<form method="post" action="'.admin_url('options-general.php?page=stayhome-options').'">
		<table class="form-table">
        
    <tr><th scope="row">Display on</th>
        <td>
            <label><input type="checkbox" name="show_on_posts" value="1" '.(($options['show_on_posts'])?'checked="checked"':'').'> Posts</label><br/>
            <label><input type="checkbox" name="show_on_pages" value="1" '.(($options['show_on_pages'])?'checked="checked"':'').'> Pages</label>
        </td>
    </tr>
    
    		
    <tr><th scope="row">Display at</th>
        <td>
            <select name="banner_position">';
                foreach(array('below_content','above_content') as $option)
                    echo '<option value="'.$option.'" '.(($option == $options['banner_position']) ? 'selected="selected"' : '').'>'.ucfirst(str_replace('_',' ',$option)).'</option>';
            echo'</select>
        </td>
    </tr>
    
    <tr><th scope="row">Show Download Plugin Link</th>
        <td>
            <label><input type="checkbox" name="show_referral_link" value="1" '.(($options['show_referral_link'])?'checked="checked"':'').'> Display a link below the Stay Home notice to download the plugin.</label><br/>
        </td>
    </tr>
    
        <tr><th scope="row">Show Notice</th>
        <td>
            <label><input type="checkbox" name="show_notice_everywhere" value="1" '.(($options['show_notice_everywhere'])?'checked="checked"':'').'> Display a notice at the bottom of your website which stays there as a user scrolls. Similar to a "cookie notice" seen on a lot of websites.</label><br/>
        </td>
    </tr>
            

                
		
		<tr><th scope="row"></th><td><input type="hidden" name="stayhome_action" value="save_options"><input type="submit" value="Save Changes"></td></tr>
        
		</table>
		</form>

<BR><BR>
Please help spread the word for this plugin. Please Tweet, Facebook Share etc this plugin to encourage other sites to display notices.	';
	}
}

/* This adds the StayHome notice if its enabled */
    $options = get_option('stayhome-affiliate-options',$defaults);
	$show_notice_everywhere = $options['show_notice_everywhere'];
	
if ($show_notice_everywhere) {
add_action('wp_footer', 'stayhome_notice');
}

function stayhome_notice(){
?>
<style>
	@media only screen and (min-width: 601px) {
.hide-desktop {
display: none !important;
}
}
    .notification-bottom {
  position: fixed;
  width: 100%;
  bottom: 0;
  left: 0;
  background-color: #0060bd;
  color: #FFFFFF;
  font-size: 20px;
  text-align: center;
  padding-top: 15px;
  padding-bottom: 0px;
  margin-bottom: 0px;
}

.notification-close {
  position: absolute;
  right: 5px;
  top: 5px;
  /* This are approximated values, and the rest of your styling goes here */
}

.sh-notice-text {
    padding-bottom: 10px;
    margin-bottom: 0px;
}

    #small-font {
        font-size: 12px;
    }
</style>
<div class="hide-desktop"><div class="notification-bottom">
  <p class="sh-notice-text">Stay Home. Stay Safe. Save Lives.<br><span id="small-font">#COVID19</span></p>
</div></div>
<?php
};

class stayhome_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'stayhome_affiliate_widget', // Base ID
			'StayHome Notice', // Name
			array( 'description' => __( 'A StayHome StaySafe Notice', 'stayhome' ), ) // Args
		);
	}

	public function widget( $args, $instance ) {
		extract( $args );

		$defaults = array(
			'show_referral_link' => true
		);
		
		$options = get_option('stayhome-affiliate-options',$defaults);
		
		$instance['show_referral_link'] = $options['show_referral_link'];
		
		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		
		echo stayhome_get_ad_code( $instance );
		
		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	public function form( $instance ) {
		$title = (isset($instance[ 'title' ])) ? $instance['title'] : '';


		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title (optional):' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}

}

$stayhome_affiliate = new stayhome_affiliate();



?>
