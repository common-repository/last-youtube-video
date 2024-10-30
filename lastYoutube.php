<?php
/*
Plugin Name: Last YouTube Video
Plugin URI: http://www.107designs.com/blog/
Description: Creates a widget that shows the last YouTube video from a given channel. Custom fields allow you to easily change the height and width of the embedded video to suit your theme, making it easy to use in any of your sidebars or widgetized footers. Very lightweight and simple to implement.
Version: 1.1
Author: 107 Designs
Author URI: http://www.107designs.com

     This plugin is based on the hugely helpful article found at go41 WebDesign 
     (http://forum.go41.de/topic/add-a-youtube-user-channel-video-to-wordpress)
     
     We needed to make the installation and configuration a little more user-friendly, 
     so we wrapped it up into a widget, complete with user-configurable settings 
     from within the widget panel.



    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( file_exists(ABSPATH.WPINC.'/class-simplepie.php') ) {
	@require_once (ABSPATH.WPINC.'/class-simplepie.php');
} else {
	die('Error: SimplePie could not be loaded.');
}

add_action( 'widgets_init', 'youtube_load_widgets' );

function youtube_load_widgets() {
	register_widget( 'Youtube_Widget' );
}

class Youtube_Widget extends WP_Widget {

	function Youtube_Widget() {
		$yt_widget_options = array( 'classname' => 'youtubeWidget', 'description' => __('Shows the last YouTube video from a given channel.', 'youtubeWidget') );
		$yt_config_options = array( 'width' => 100, 'height' => 250, 'id_base' => 'youtube-widget' );
		$this->WP_Widget( 'youtube-widget', __('Last YouTube Video', 'youtubeWidget'), $yt_widget_options, $yt_config_options );
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', $instance['title'] );
		$youtubeId = $instance['youtubeId'];
		$height = $instance['height'];
		$width = $instance['width'];

		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;

		if (class_exists('SimplePie')) {
		
		$feed = new SimplePie(); // setup the feed
		$feed->set_feed_url("http://gdata.youtube.com/feeds/api/users/".$youtubeId."/uploads");
		$feed->enable_cache(false); //  disable caching
		$feed->set_timeout(5);
		$success = $feed->init();
		$feed->handle_content_type();
		
		if ($success): foreach ($feed->get_items(0, 1) as $item):
		if ($item) {
			$YT_VideoID = substr(strstr($item->get_permalink(), 'v='), 2, 11); //get just ID of latest clip
			?><script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2/swfobject.js"></script>
			<a name="ytplayer"></a>
			<div id="ytplayer_div">You need Flash player 8+ and JavaScript enabled to view this video.</div>
			<script type="text/javascript">
			  swfobject.embedSWF(
			    'http://www.youtube.com/v/<?php echo $YT_VideoID; ?>&enablejsapi=1&rel=0&fs=1',
			    'ytplayer_div',
			    '<?php echo $width; ?>',
			    '<?php echo $height; ?>',
			    '8',
			    null,
			    null,
			    {
			      allowScriptAccess: 'always',
			      allowFullScreen: 'true'
			    },
			    {
			      id: 'ytplayer_object'
			    }
			  );
			</script>
			<?php } // end if there is an item
		endforeach;
		endif; //success
		$feed->__destruct(); unset($feed);
		 } // end Simplepie	

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['youtubeId'] = strip_tags($new_instance['youtubeId']);
		$instance['height'] = strip_tags($new_instance['height']);
		$instance['width'] = strip_tags($new_instance['width']);
		return $instance;
	}

	function form( $instance ) {

		$defaults = array('title' => 'On YouTube', 'youtubeId' => '107designs', 'height' => '200', 'width' => '250');
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p><label for="<?php echo $this->get_field_id('title'); ?>">Title:</label><input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('youtubeId'); ?>">YouTube ID:</label><input class="widefat" id="<?php echo $this->get_field_id('youtubeId'); ?>" name="<?php echo $this->get_field_name('youtubeId'); ?>" value="<?php echo $instance['youtubeId']; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('height'); ?>">Height:</label><input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" value="<?php echo $instance['height']; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('width'); ?>">Width:</label><input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" value="<?php echo $instance['width']; ?>" /></p>

	<?php
	}
}
?>