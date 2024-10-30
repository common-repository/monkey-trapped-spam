=== Monkey Trapped Spam ===
Contributors: konnun
Tags: comments, comment, spam, anti-spam, blacklist, antispam, block spam, comment spam, spam filter
Requires at least: 3.0.1
Tested up to: 4.1
Stable tag: 1.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Monkey Trapped Spam is a WordPress plugin that automatically places the IP address of  spam comments in the local comments blacklist.

== Description ==

When you mark a comment “spam” Monkey Trapped Spam will automatically add the author’s IP address to the internal WordPress comment black list. Any comment coming from that IP address in the future will go straight to spam. Monkey Trapped Spam learns and becomes more effective the longer it’s installed.

Upon installation Monkey Trapped Spam will scan your current spam comments and add the author’s IP addresses to your internal WordPress comment black list as well.

Optional Features

If you select “Participate” on the settings page Monkey Trapped Spam will send a copy of spam comments to the Monkey Trapped Spam server to help build a comprehensive comment spammer black list.

If you select “Update Blocklist” on the settings page Monkey Trapped Spam will retrieve a list of the most common IP addresses used by comment spammers from the server and add them to your  internal WordPress comment black list giving you a head start.

== Installation ==


    Download the plugin to your computer
    Log in to your WP admin area and go to Plugins > Add New
    Click upload
    Browse to the plugin .zip file on your computer and select it
    Click Install Now and the plugin will be installed shortly
    Click Activate Plugin


== Frequently Asked Questions ==


== Changelog ==
= 1.2.1 =
* moved settings page to a sub_menu under settings (where it should have been all along).

= 1.2.0 =
* hooked system marked spam comments later in the loop to allow use of the comment_ID.
* dropped json, now using curl POST to send the whole spam comment object to the server when opt in.
* added CURLOPT_RETURNTRANSFER to catch any response from the server (or errors) before it outputs.
* added simple stats to the bottom of the settings page.


= 1.1 =
* fixed bad link in admin notice.

= 1.0.0 =
* Made participation (sending data to the server) opt-in.
* Added admin notice.
* Several bug fixes.

= 0.9.1 =
* Final public beta

== Upgrade Notice ==

= 1.2.1 =
Latest stable public release.
