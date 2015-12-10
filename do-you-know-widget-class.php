<?php
/**
 * DoYouKnow Widget Class
 */
class DoYouKnow_widget extends WP_Widget {


    /** constructor -- name this the same as the class above */
    function DoYouKnow_widget() {
        parent::WP_Widget(false, $name = 'Do You Know Widget');
    }

    /** @see WP_Widget::widget -- do not rename this */
    function widget($args, $instance) {
        extract( $args );
        $title 		= apply_filters('widget_title', $instance['title']);
        $text 		= $instance['text'];
		$user 		= BP_Members_With_Avatar_Helper::get_instance()->get_random_user_with_avatar(get_current_user_id());

        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
                        	echo $before_title . $title . $after_title; ?>

				 <?php 	if ( $text ) ?>
							<p><?php echo $text; ?></p>

				 <?php 	if ( $user )
							echo get_avatar( $user->ID ); ?>
              <?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['text'] = strip_tags($new_instance['text']);
        return $instance;
    }

    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {

        $title 	= esc_attr($instance['title']);
        $text	= esc_attr($instance['text']);
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" type="text" value="<?php echo $text; ?>" />
        </p>
        <?php
    }


} // end class example_widget