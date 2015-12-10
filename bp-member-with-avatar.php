<?php
/**
 * Helper class for keeping record of avatar change & providing list of the users
 * It is implemented as a singleton class
 */

class BP_Members_With_Avatar_Helper {

    private  static $instance;

    private function __construct() {
		//record on new avatar upload
		add_action( 'xprofile_avatar_uploaded', array( $this, 'log_uploaded' ) );
		//on avatar delete
		add_action( 'bp_core_delete_existing_avatar', array( $this, 'log_deleted' ) );
		//show entry
		add_action( 'bp_members_with_uploaded_avatar_entry', array( $this, 'member_entry' ), 10, 2 );//remove this function from the action and use your own to customize the entry
    }
    /**
     * Get the singleton object
     * @return BP_Members_With_Avatar_Helper object
     */
    public static function get_instance(){

        if( ! isset( self::$instance ) )
            self::$instance = new self();

        return self::$instance;
    }

    //on new avatar upload, record it to user meta
    public function log_uploaded($user_id) {

		error_log("User avatar added:" . $user_id);
        bp_update_user_meta( $user_id, 'has_avatar', 1 );
    }

    //on delete avatar, delete it from user meta
    public function log_deleted( $args ) {

        if( $args['object'] != 'user' )
			return;
        //we are sure it was user avatar delete

        //remove the log from user meta
        bp_delete_user_meta( $args['item_id'], 'has_avatar' );
    }

	public function setRandomQueryOrder(&$query) {
	   if($query->query_vars["orderby"] == 'random') {
		   $query->query_orderby = 'ORDER by RAND()';
	   }
	}

    /**
     *
     * @param type $type
     * @return type Return an array of Users object with the specifie
     */
    public function get_users_with_avatar( $max, $orderby = 'random', $exclude = null ) {

		add_filter('pre_user_query', array($this, setRandomQueryOrder));

        //Find all users with uploaded avatar

		$qusers = new WP_User_Query( array(
				'orderby'	=> $orderby,
				'number'	=> $max,
				'exclude'	=> $exclude,
				'meta_key'	=> 'has_avatar',
			)
		);

		remove_filter('pre_user_query', array($this, setRandomQueryOrder));

		$users = array_values( $qusers->results );

        return $users;

    }

    public function member_entry( $user, $args ) {

        extract( $args );
        ?>

		<a href="<?php echo bp_core_get_user_domain( $user->id ) ?>">
		   <?php echo bp_core_fetch_avatar( array(
					   'type'		=> $size,
					   'width'		=> $width,
					   'height'	=> $height,
					   'item_id'	=> $user->id
				   )
			   ) ?>
		</a>


    <?php

	}

}

BP_Members_With_Avatar_Helper::get_instance();