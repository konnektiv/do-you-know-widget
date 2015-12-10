<?php
/**
 * DoYouKnow Widget Class
 */
class DoYouKnow_widget extends WP_Widget {


    /** constructor -- name this the same as the class above */
    function DoYouKnow_widget() {
        parent::WP_Widget(false, $name = 'Do You Know Widget');
    }

	function get_random_user($seconds) {
		// check if this is a new game
		$current_user = get_user_meta(get_current_user_id(), '_dyk_current_user', true);
		$game_start = get_user_meta(get_current_user_id(), '_dyk_game_start', true);

		// start new game when its time
		if (!$game_start || (time() - $game_start ) > $seconds ) {
			$game_start = time();
			$current_user = null;
			update_user_meta(get_current_user_id(), '_dyk_game_start', $game_start);
		}

		if (!$current_user) {
			$current_user = BP_Members_With_Avatar_Helper::get_instance()->get_random_user_with_avatar(get_current_user_id());

			if ($current_user) {
				$current_user = $current_user->ID;
				update_user_meta(get_current_user_id(), '_dyk_current_user', $current_user);
			}
		}

		return $current_user;
	}

    /** @see WP_Widget::widget -- do not rename this */
    function widget($args, $instance) {
        extract( $args );
        $title 		= apply_filters('widget_title', $instance['title']);
        $text 		= $instance['text'];
		$user 		= $this->get_random_user($instance['seconds']);

        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
                        	echo $before_title . $title . $after_title; ?>

				 <?php 	if ( $text ) ?>
							<p><?php echo $text; ?></p>

				 <?php 	if ( $user )
							echo get_avatar( $user ); ?>
              <?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['text'] = strip_tags($new_instance['text']);
		$instance['seconds'] = strip_tags($new_instance['seconds']);
        return $instance;
    }

    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {

        $title 		= esc_attr($instance['title']);
        $text		= esc_attr($instance['text']);
		$seconds	= esc_attr($instance['seconds']);
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" type="text" value="<?php echo $text; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('seconds'); ?>"><?php _e('Seconds per game'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('seconds'); ?>" name="<?php echo $this->get_field_name('seconds'); ?>" type="number" value="<?php echo $seconds; ?>" />
        </p>
        <?php
    }


} // end class example_widget