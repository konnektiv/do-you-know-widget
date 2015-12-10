<?php
/**
 * Helper class for keeping record of avatar change & providing list of the users
 * It is implemented as a singleton class
 */

class BP_Members_With_Avatar_Helper {

    private  static $instance;

    private function __construct() {
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

	public function filter_default_gravatar($default_grav) {
		return 404;
	}

	public function filter_default_avatar($default_avatar) {
		return false;
	}

	public function setRandomQueryOrder(&$query) {
		if($query->query_vars["orderby"] == 'random') {
			$query->query_orderby = 'ORDER by RAND()';
		}
	}

    /**
     *
     * @param type $exclude
     * @return type return a random user object which has an attached avatar
     */
    public function get_random_user_with_avatar( $exclude = null ) {

		if ($exclude)
			$exclude = array($exclude);

        //Find all users with uploaded avatar

		$qusers = new WP_User_Query( array(
				'fields'	=> array('ID', 'user_email'),
				'exclude'	=> $exclude,
			)
		);

		$users = $qusers->get_results();
		$user = null;

		add_filter('bp_core_mysteryman_src', array($this, 'filter_default_gravatar'));
		add_filter('bp_core_default_avatar_user', array($this, 'filter_default_avatar'));

		while (!empty($users)) {
			// grab random user
			$index = rand(0, count($users)-1);
			$user = $users[$index];
			array_splice($users, $index, 1);
			$avatar = null;

			// first try w/o gravatar
			foreach(array(true, false) as $no_grav){
				$avatar = bp_core_fetch_avatar(array(
					'item_id'	=> $user->ID,
					'object'	=> 'user',
					'email'		=> $user->user_email,
					'html'		=> false,
					'no_grav'	=> $no_grav,
				));

				if ($avatar && $no_grav) break;
				$avatar = html_entity_decode($avatar);

				// check if gravatar exists
				if (!$no_grav) {
					$headers = get_headers((is_ssl()?'https:':'http:') . $avatar);
					if (substr($headers[0], 9, 3) === "404")
						$avatar = null;
				}
			}

			if ($avatar)
				break;
		}

		remove_filter('bp_core_mysteryman_src', array($this, 'filter_default_gravatar'));
		remove_filter('bp_core_default_avatar_user', array($this, 'filter_default_avatar'));

        return $user;
    }

   	 public function get_random_users( $max, $exclude = null ) {

		add_filter('pre_user_query', array($this, setRandomQueryOrder));

		$qusers = new WP_User_Query( array(
				'orderby'	=> 'random',
				'number'	=> $max,
				'fields'	=> 'ID',
				'exclude'	=> $exclude,
			)
		);

		remove_filter('pre_user_query', array($this, setRandomQueryOrder));

		return $qusers->get_results();
	 }

}

BP_Members_With_Avatar_Helper::get_instance();