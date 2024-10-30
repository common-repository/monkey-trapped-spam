<?php
/*
Plugin Name: Monkey Trapped Spam
Plugin URI: http://monkeytrapped.com/monkey-trapped-spam-plugin/
Description: Monkey Trapped Spam is a WordPress plugin that automatically places the IP address of  spam comments in the local comments blacklist. Upon installation Monkey Trapped Spam will scan your current spam comments and add the author’s IP addresses to your internal WordPress comment black list as well. Optional features include retrieving a current comment black list from the Monkey Trapped Server and automatically send a copy of spam comments to the server.
Version: 1.2.1
Author: Konnun, LLC
Author URI: http://monkeytrapped.com

------------------------------------------------------------------------

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

*/
register_activation_hook( __FILE__, array('MonkeyTrappedSpam', 'installed') );// performs post install tasks
add_action( 'admin_notices', array('MonkeyTrappedSpam','my_admin_notice') );//hook admin messages for the post install lead admin to the options page


add_action('comment_post',  array('MonkeyTrappedSpam', 'sys_check'), 10, 1);//hooks comments as the system saves them to the database


add_action('spammed_comment',  array('MonkeyTrappedSpam', 'do_spam_comment'), 10, 1);//hooks comments as admin marks them spam
add_action('unspammed_comment',  array('MonkeyTrappedSpam', 'remove_blacklist_ip'), 10, 1);// hooks comments as admin marks them not spam

include_once dirname( __FILE__ ) . '/options.php';

class MonkeyTrappedSpam 
{
	public static function installed() {
	 	$installation = get_option( 'MonkeyTrappedSpam_installed' );
		
		if(strlen($installation) == 0)
		{
			/* get all spam comments */
			$comments = get_comments(array('status' => 'spam'));
                        /* loop through all spam comments and blacklist ip */
			foreach($comments as $comment)
			{
				$ip = $comment->comment_author_IP;	
				self::add_to_blacklist($ip);
			}
                       
                        /* save the setting so install tasks only happen once */
			add_option( 'MonkeyTrappedSpam_installed', 'true');

                        /* note: we don't report old messages to Monkey Trapped on install. That may change in later versions  */
			

		}
		
	}
	
       public static function my_admin_notice() {
           /* Sends admin message if the options page hasn't been visitted */
           if(!get_option( 'MonkeyTrappedSpam_notified' )){
    ?>
    <div class="updated">
        <p>Monkey Trapped can do more... See the <a href="options-general.php?page=monkey-trapped-spam/options.php">settings page</a> for more functionality.</p>
    </div>
    <?php
                 }
}

	public static function sys_check($cid){
                $comment=get_comment($cid);

		if($comment->comment_approved == 'spam')
		{
                self::increment_system_count();
                self::report_monkey_trapped((array) $comment);
                }
}


	
        public static function increment_system_count() {
             $cnt=get_option( 'MonkeyTrappedSpam_system_count' );
             if($cnt >0 ){
                 update_option( 'MonkeyTrappedSpam_system_count', $cnt+1 );
             }else{
                 add_option( 'MonkeyTrappedSpam_system_count' );
                 update_option( 'MonkeyTrappedSpam_system_count', 1 );
             }

        }

        public static function increment_manual_count() {
             $cnt=get_option( 'MonkeyTrappedSpam_manual_count' );
             if($cnt >0 ){
                 update_option( 'MonkeyTrappedSpam_manual_count', $cnt+1 );
             }else{
                 add_option( 'MonkeyTrappedSpam_manual_count' );
                 update_option( 'MonkeyTrappedSpam_manual_count', 1 );
             }

        }

public static function check_for_more($ip) {

			 /* get all pending comments */
			 $comments = get_comments(array('status' => 'hold'));

                        /* loop through all pending comments and mark spam if they match $ip */
			 foreach($comments as $comment)
			 {

				if($ip == $comment->comment_author_IP){
                                 wp_set_comment_status( $comment->comment_ID, 'spam' );
                                 $comment_array=(array) $comment;
                                 self::report_monkey_trapped($comment_array);
                                 self::increment_system_count();
                                 }	

			 }

        }




	public static function report_monkey_trapped($comment_arr) {
                /* make sure the admin has opted in for sending info to server */
	        if(esc_attr(get_option('MonkeyTrappedSpam_participate') )){
               
                $comment_arr[blog_name]=get_bloginfo('name').' - '.get_bloginfo('url');

		/* REPORT spam comment to Monkey Trapped using curl to post */
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'http://monkeytrapped.com/spam/report_comment.php'); 
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $comment_arr);
                $ret = curl_exec($ch);
                curl_close($ch); 
		}
	}

	public static function do_spam_comment($comment_id) {
		
		/* build the comment array to report */
                $comment=(array) get_comment($comment_id);
               

                /* REPORT spam comment to Monkey Trapped */
                self::report_monkey_trapped($comment);
		self::add_to_blacklist($comment[comment_author_IP]);

                self::increment_manual_count();
		
	}
	
public static function add_to_blacklist($ip_address){
                /* deal with all pending comments from that ip first */
		self::check_for_more($ip_address);

		/* pull the comments blacklist from the database */
		$comments_blacklist = get_option( 'blacklist_keys' );
		
		/* split string into array */
		$blacklist_array = explode("\n", $comments_blacklist);
		
		/* if IP not found in array then insert */
		if(!in_array($ip_address, $blacklist_array) && strlen($ip_address) > 0){
 			$blacklist_array[] = $ip_address;
 			$new_blacklist = implode("\n", $blacklist_array);
			update_option('blacklist_keys', $new_blacklist);
			return true;
		 }

		 return false;	
 	}

public static function remove_blacklist_ip($comment_id){

		/* get the spam comment author IP*/
		$comment = get_comment($comment_id);
		$author_ip = $comment->comment_author_IP;
		
		self::remove_from_blacklist($author_ip);
	}
	
public static function remove_from_blacklist($ip_address){

		/* pull the comments blacklist from the database */
		$comments_blacklist = get_option( 'blacklist_keys' );
		
		if(strlen($ip_address) > 0)
		{
			 $new_blacklist = str_replace("\n".$ip_address, '', $comments_blacklist); 
			 update_option('blacklist_keys', $new_blacklist);
                         /* report removal to Monkey Trapped not implemented in this version */
		}
		
		return true;
		 		
	}

}
?>
