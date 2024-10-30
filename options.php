<?php
// create custom plugin settings menu
add_action('admin_menu', 'monkey_create_menu');

function monkey_create_menu() {

	//create new 2nd-level menu
	add_options_page('Monkey Plugin Settings', 'Monkey Spam', 'administrator', __FILE__, 'monkey_spam_settings_page');
	

	//call register settings function
	add_action( 'admin_init', 'register_monkey_settings' );
}

function register_monkey_settings() {
	//register our settings
	register_setting( 'monkey-settings-group', 'MonkeyTrappedSpam_participate' );
	register_setting( 'monkey-settings-group', 'MonkeyTrappedSpam_update_blocklist' );
	register_setting( 'monkey-settings-group', 'MonkeyTrappedSpam_last_updated' );
	register_setting( 'monkey-settings-group', 'MonkeyTrappedSpam_notified' );
}

function monkey_spam_settings_page() {
     if(esc_attr( get_option('MonkeyTrappedSpam_update_blocklist') )){
          $monkey_file="http://monkeytrapped.com/comment-spam.txt";
                        $monkey_lines = file($monkey_file);

                        foreach ($monkey_lines as $monkey_line) {
                                if(strlen($monkey_line) > 0){
                                MonkeyTrappedSpam::add_to_blacklist(rtrim($monkey_line));
                                }
                        }
          update_option('MonkeyTrappedSpam_last_update', date('jS \of F Y h:i:s A'));
          update_option('MonkeyTrappedSpam_update_blocklist', "");
       }
update_option('MonkeyTrappedSpam_notified', 'true');
     
?>
<div class="wrap">
<h2>Monkey Trapped Spam</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'monkey-settings-group' ); ?>
    <?php do_settings_sections( 'monkey-settings-group' ); ?>
    <input type="hidden" name="MonkeyTrappedSpam_notified" value="true">
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Participate</th>
        <td><input type="checkbox" name="MonkeyTrappedSpam_participate" value="true" <?php if(esc_attr( get_option('MonkeyTrappedSpam_participate') )){echo 'checked';}; ?> /></td>
        <td>By checking participate you will be sending a copy of comments that are identified as spam to the Monkey Trapped server as they arrive. We rely on participation from WordPress site admins to build more accurate and comprehensive block lists.</td>
        </tr>
         
        <tr valign="top">
        <th scope="row">Update Blocklist</th>
        <td><input type="checkbox" name="MonkeyTrappedSpam_update_blocklist" value="true" <?php if(esc_attr( get_option('MonkeyTrappedSpam_update_blocklist') )){echo 'checked';}; ?>/></td>
        <td>By checking update block list you are requesting a download of the latest block list from the Monkey Trapped server. This block list will be merged with your local block list.</td>
        </tr>

        <tr valign="top">
        <th scope="row">Block List Last Updated:</th>
        <td></td>
        <td><?php echo esc_attr( get_option('MonkeyTrappedSpam_last_update') ); ?></td>
        </tr>
        
    </table>
    
    <?php submit_button(); ?>

</form>
<hr>
<h3>Monkey Trapped Spam Stats</h3>
Total comments marked spam by Monkey Trapped Spam - <?php echo get_option( 'MonkeyTrappedSpam_system_count' ); ?><br />
Total comments manually marked spam - <?php echo get_option( 'MonkeyTrappedSpam_manual_count' ); ?>
</div>
<?php } ?>
