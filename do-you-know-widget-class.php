<?php
/**
 * DoYouKnow Widget Class
 */
class DoYouKnow_widget extends WP_Widget {


    /** constructor -- name this the same as the class above */
    function DoYouKnow_widget() {
        parent::WP_Widget(false, $name = 'Do You Know Widget');
    }

	function get_game($seconds) {
		$num_choices = 3;
		$game_start = get_user_meta(get_current_user_id(), '_dyk_game_start', true);

		// start new game when its time
		if (!$game_start || (time() - $game_start ) > $seconds ) {
			$game_start = time();
			$current_user = null;
			update_user_meta(get_current_user_id(), '_dyk_game_start', $game_start);

			// set current user
			$current_user = BP_Members_With_Avatar_Helper::get_instance()->get_random_user_with_avatar(get_current_user_id());

			if (!$current_user) return false;


			$current_user = $current_user->ID;
			update_user_meta(get_current_user_id(), '_dyk_current_user', $current_user);

			// set answers
			$answers = BP_Members_With_Avatar_Helper::get_instance()->get_random_users($num_choices - 1, array(get_current_user_id(), $current_user));

			// set random correct position
			if (count($answers) < ($num_choices - 1))
				return false;

			$correct = rand(0, $num_choices - 1);
			array_splice($answers, $correct, 0, $current_user);

			update_user_meta(get_current_user_id(), '_dyk_current_answers', $answers);
		}

		$current_user 	= get_user_meta(get_current_user_id(), '_dyk_current_user', true);
		$answers 		= get_user_meta(get_current_user_id(), '_dyk_current_answers', true);

		return array(
			'user' 		=> $current_user,
			'answers'	=> $answers,
		);
	}


    /** @see WP_Widget::widget -- do not rename this */
    function widget($args, $instance) {
        extract( $args );
        $title 		= apply_filters('widget_title', $instance['title']);
        $text 		= $instance['text'];
		$game 		= $this->get_game($instance['seconds']);

		if (!$game) {
			echo $before_widget . $after_widget;
			return;
		}

        ?>
			<?php echo $before_widget; ?>
			<?php if ( $title )
				echo $before_title . $title . $after_title; ?>

			<?php echo get_avatar( $game['user'] ); ?>

			<?php 	if ( $text ) ?>
				<p><?php echo $text; ?></p>

			<form method="post">
				<fieldset>
				<?php foreach($game['answers'] as $key => $user_id){  ?>
					<input type="radio" id="answer<?php echo $key; ?>" name="_dyk_answer" value="<?php echo $key; ?>" required>
					<label for="answer<?php echo $key; ?>"><?php echo get_the_author_meta( 'display_name', $user_id ); ?></label><br>
				<?php } ?>
				</fieldset>
				<input type="submit" value="Submit">
			</form>

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