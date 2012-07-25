<?php
/*
Plugin Name: Matt's Most Commented Posts
Plugin URI: http://www.matthewaprice.com/
Description: Drives Most Commented Posts
Version: 1.1
Author: Matthew Price
Author URI: http://www.matthewaprice.com
License: GPL2
*/

function mapcp_most_popular_posts_register_widget() {
	register_widget( 'MAPCPMostCommentedPosts' );
}

add_action( 'widgets_init', 'mapcp_most_popular_posts_register_widget' );

class MAPCPMostCommentedPosts extends WP_Widget {

	function mapcp_get_most_commented_posts( $limit) {
	
		global $wpdb;
		$q  = "SELECT COUNT(*) as comment_count, comment_post_ID ";
		$q .= "FROM {$wpdb->prefix}comments ";
		$q .= "WHERE comment_approved = 1 ";
		$q .= "GROUP BY comment_post_ID ";
		$q .= "ORDER BY comment_count ";
		$q .= "DESC LIMIT %d ";
		
		$comments = $wpdb->get_results( $wpdb->prepare( $q, $limit ) );

		return $comments;										
	
	}

	function MAPCPMostCommentedPosts() {
		// Instantiate the parent object
		parent::__construct( false, 'Popular Posts by Comment Count' );
	}

	function widget( $args, $instance ) {
	    extract( $args );

		echo $before_widget;
		echo $before_title
		   . $instance['title']
		   . $after_title;

				$comments = $this->mapcp_get_most_commented_posts( $instance['limit'] );
				$top_count = $comments[0]->comment_count;
				$tier_1 = $top_count * $instance['tier_1'];
				$tier_2 = $top_count * $instance['tier_2'];
				$tier_3 = $top_count * $instance['tier_3'];
					echo "<ul>";
					foreach ( $comments as $comment ) {		
						$title_class = '';					
						if ($comment->comment_count >= $tier_1) { $title_class = "fire"; }
						elseif ( ($comment->comment_count <= $tier_1) && ($comment->comment_count >= $tier_2) ) { $title_class = "hot"; }
						elseif ( ($comment->comment_count < $tier_2) && ($comment->comment_count >= $tier_3) ) { $title_class = "medium"; }
						elseif ($comment->comment_count < $tier_3) { $title_class = "mild"; }	
						$post_title = get_the_title($comment->comment_post_ID);
						$permalink = get_permalink($comment->comment_post_ID);
						echo "<li class=\"" . $title_class . "\"><a href=\"" . $permalink . "\">" . $post_title . "</a></li>";							
					}			
					echo "</ul>";														
			?>
		<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['tier_1'] = strip_tags( $new_instance['tier_1'] );
		$instance['tier_2'] = strip_tags( $new_instance['tier_2'] );
		$instance['tier_3'] = strip_tags( $new_instance['tier_3'] );
		$instance['limit'] = strip_tags( $new_instance['limit'] );
		return $instance;
	}

	function form( $instance ) {
		$defaults = array(
			'title' => 'Popular Posts by Comment Count',
			'tier_1' => 0.60,
			'tier_2' => 0.30,
			'tier_3' => 0.10,						
			'limit' => 5						
		);
		$instance = wp_parse_args( (array) $instance, $defaults );

		$title = strip_tags( $instance['title'] );
		$tier_1 = strip_tags( $instance['tier_1'] );
		$tier_2 = strip_tags( $instance['tier_2'] );
		$tier_3 = strip_tags( $instance['tier_3'] );
		$limit = strip_tags( $instance['limit'] );
		?>

		<p><label for="bp-core-widget-title">Title<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 100%" /></label></p>
		<p><label for="bp-core-widget-members-max">1st Tier Threshold<input class="widefat" id="<?php echo $this->get_field_id( 'tier_1' ); ?>" name="<?php echo $this->get_field_name( 'tier_1' ); ?>" type="text" value="<?php echo esc_attr( $tier_1 ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-core-widget-members-max">2nd Tier Threshold<input class="widefat" id="<?php echo $this->get_field_id( 'tier_2' ); ?>" name="<?php echo $this->get_field_name( 'tier_2' ); ?>" type="text" value="<?php echo esc_attr( $tier_2 ); ?>" style="width: 30%" /></label></p>		
		<p><label for="bp-core-widget-members-max">3rd Tier Threshold<input class="widefat" id="<?php echo $this->get_field_id( 'tier_3' ); ?>" name="<?php echo $this->get_field_name( 'tier_3' ); ?>" type="text" value="<?php echo esc_attr( $tier_3 ); ?>" style="width: 30%" /></label></p>		
		<p><label for="bp-core-widget-members-max">Number of Posts to Display<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>" style="width: 30%" /></label></p>				
		<p>This widget works based on the number of comments on your posts.  It takes the most commented post and gives it a value of 1.  You can choose the thresholds above for the three tiers. It applies css classes to the tiers in the following manner: fire, hot, medium, and mild. You can use the classes to color the post titles, background etc to show "heat map."  Depending on how many posts, you may not see all of the classes.</p>		
		<?php		
	}
}
?>