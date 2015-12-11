<?php
/**
 * DoYouKnow Widget Class
 */
class DoYouKnow_widget extends WP_Widget {

    /** constructor -- name this the same as the class above */
    function DoYouKnow_widget() {
        parent::WP_Widget(false, $name = 'Do You Know Widget');

		add_filter('badgeos_activity_triggers', array($this, 'badgeos_triggers'));
    }

	function badgeos_triggers($triggers) {
		$triggers['dyk_correct_answer'] = __('Correctly play the "Do you know?" game', 'do-you-know-widget');
		return $triggers;
	}

	function get_game($seconds, $num_choices) {
		$game_start 	= get_user_meta(get_current_user_id(), '_dyk_game_start', true);
		$current_user 	= get_user_meta(get_current_user_id(), '_dyk_current_user', true);
		$answers 		= get_user_meta(get_current_user_id(), '_dyk_current_answers', true);
		$result 		= get_user_meta(get_current_user_id(), '_dyk_current_result', true);

		if ($result === '')
			$result = null;
		else
			$result = (bool)$result;

		// answer was submitted
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] and isset ( $_POST['_dyk_answer'] ) && is_null($result)) {
			$result = ($answers[$_POST['_dyk_answer']] == $current_user)?'1':'0';
			add_user_meta(get_current_user_id(), '_dyk_current_result', $result, true);
			$result = (bool)$result;
			if ($result)
				do_action('dyk_correct_answer');
		}
		// start new game when its time
		else if (!$game_start || (time() - $game_start ) > $seconds ) {
			$game_start = time();
			$current_user = null;
			update_user_meta(get_current_user_id(), '_dyk_game_start', $game_start);
			$result = null;
			delete_user_meta(get_current_user_id(), '_dyk_current_result');

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

		return array(
			'user' 		=> $current_user,
			'answers'	=> $answers,
			'result'	=> $result,
			'next'		=> $seconds - (time() - $game_start),
		);
	}

    /** @see WP_Widget::widget -- do not rename this */
    function widget($args, $instance) {
        extract( $args );

		wp_enqueue_script( 'countdown', plugins_url( 'js/countdown.js', __FILE__ ), array(), '2.6.0', true );
		wp_enqueue_script( 'dyk-main', plugins_url( 'js/main.js', __FILE__ ), array('countdown', 'jquery'), '0.0.1', true );

        $title 			= apply_filters('widget_title', $instance['title']);
        $text 			= $instance['text'];
		$seconds		= $instance['seconds'];
		$num_choices	= $instance['num_choices'];
		$correct_msg 	= $instance['correct_msg'];
		$wrong_msg 		= $instance['wrong_msg'];
		$game 			= $this->get_game($seconds, $num_choices);

		if (!$game) {
			echo $before_widget . $after_widget;
			return;
		}

		$has_result = is_bool($game['result']);

        ?>
			<?php echo $before_widget; ?>
			<?php if ( $title )
				echo $before_title . $title . $after_title; ?>

			<?php if ( $has_result ) { ?>
				<?php if ( $game['result'] ) { ?>
					<p><?php echo $correct_msg; ?></p>
				<?php } else { ?>
					<p><?php echo $wrong_msg; ?></p>
				<?php } ?>
			<?php } ?>

			<?php if ( $has_result ) { ?>
				<a href="<?php echo bp_core_get_user_domain( $game['user'] ) ?>">
			<?php } ?>
			<?php echo get_avatar( $game['user'] ); ?>
			<?php if ( $has_result ) { ?>
				</a>
				<?php if ( $game['next'] > 0 ) { ?>
				<p><span class="dyk-next-text">Next game starts in <span class="dyk-next-time"><?php echo $game['next'] ?></span></span></p>
				<?php } ?>
				<a href="" class="button dyk-next-button" <?php if ( $game['next'] > 0 ) { ?>style="display: none;"<?php } ?>>Play again!</a>
			<?php } else { ?>
				<?php if ( $text ) ?>
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
			<?php } ?>

			<?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['text'] = strip_tags($new_instance['text']);
		$instance['seconds'] = strip_tags($new_instance['seconds']);
		$instance['correct_msg'] = strip_tags($new_instance['correct_msg']);
		$instance['wrong_msg'] = strip_tags($new_instance['wrong_msg']);
		$instance['num_choices'] = strip_tags($new_instance['num_choices']);
        return $instance;
    }

    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {

		$instance = wp_parse_args( (array) $instance, array(
			'title'			=> "Do You Know?",
			'text'			=> "Do you know this participant? Choose the correct name!",
			'seconds'		=> 60,
			'num_choices'	=> 4,
			'correct_msg' 	=> "Correct! You can click on the image to see the profile of this participant!",
			'wrong_msg' 	=> "Bummer! You made the wrong choice. You can get to learn this participant by clicking on the image!",
		));

        $title 			= esc_attr($instance['title']);
        $text			= esc_attr($instance['text']);
		$seconds		= esc_attr($instance['seconds']);
		$correct_msg	= esc_attr($instance['correct_msg']);
		$wrong_msg		= esc_attr($instance['wrong_msg']);
		$num_choices	= esc_attr($instance['num_choices']);

        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Game text:'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" type="text" value="<?php echo $text; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('seconds'); ?>"><?php _e('Seconds per game:'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('seconds'); ?>" name="<?php echo $this->get_field_name('seconds'); ?>" type="number" value="<?php echo $seconds; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('num_choices'); ?>"><?php _e('Number of possible choices:'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('num_choices'); ?>" name="<?php echo $this->get_field_name('num_choices'); ?>" type="number" value="<?php echo $num_choices; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('correct_msg'); ?>"><?php _e('Text for correct answer:'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('correct_msg'); ?>" name="<?php echo $this->get_field_name('correct_msg'); ?>" type="text" value="<?php echo $correct_msg; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('wrong_msg'); ?>"><?php _e('Text for wrong answer:'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('wrong_msg'); ?>" name="<?php echo $this->get_field_name('wrong_msg'); ?>" type="text" value="<?php echo $wrong_msg; ?>" />
        </p>
        <?php
    }


} // end class example_widget