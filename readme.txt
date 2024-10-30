=== Keep Backup Daily ===
Contributors: fahadmahmood, invoicepress
Tags: regular backup, daily backup, keep backup daily, backup, back up, database security, free backup, db backup, database backup, email database backup, restore database backup
Requires at least: 3.0
Tested up to: 6.6
Stable tag: 2.0.9
Requires PHP: 7.0
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Keep Backup Daily backup your wordpress database and email to you daily, weekly, monthly and even yearly according to the settings.

== Description ==
* Author: [Fahad Mahmood](https://www.androidbubbles.com/contact)
* Project URI: <https://androidbubble.com/blog/wordpress/plugins/keep-backup-daily>
* License: GPL 3. See License below for copyright jots and tittles.

Keep Backup Daily backup your wordpress database and email to you daily, weekly, monthly and even yearly according to the settings. It is a wordpress plugin which helps you to get relax about taking regular backups. It is much better that if you are running a news website and don't want to overload your database. Keep backup daily and another plugin might be freeing up your database on weekly basis. There can be many uses of this plugin, you could have a look what activity is performing on your database now a days. Its not only a convenience of exporting mysql database but having it in secure place as well. If you have configured the email client on your PC and want to keep backup on disk so it is possible as well with convenience. I am a PHP, Wordpress developer and i faced a lot of inconvenience regarding keep an eye on wordpress DB regarding plugins and user's activity. Our debugging process demands access to the DB most of the time so developed this utility for personal use and now publishing it. I coded a no. of fixes for wordpress sites and few of the solutions are in form of articles on my blog.


Important!

1- Many of the users might be using free hosting or cheap price hosting. Especially students do that but their data can be important to them, this plugin will give a feel of relax and to restore the website on last stable version of DB.

2- Default Settings: For your convenience, we are providing cron schedule from our website androidbubbles.com to the URL https://www.androidbubbles.com/api/kbd.php. For this purpose, we keep your domain name with us to access it e.g. http://www.yourdomain.com/?kbd_cron_process=1 

[wordpress.com][androidbubbles]: <http://androidbubbles.wordpress.com/2013/02/26/how-to-get-database-backup-regularly-in-your-inbox/>

[Blog][Wordpress][]: <http://androidbubble.com/blog/category/website-development/php-frameworks/wordpress>

Keep backup daily is arranged in flexible manner for better user experience.

= Tags =
offsite, sql, online backup, full backup, complete backup, mysql export, email mysql dump, 

==Installation ==
To use Keep Backup Daily, you will need:
* 	an installed and configured copy of [WordPress][](version 3.0 or later).
*	FTP, SFTP or shell access to your web host

= New Installations =

Method-A:

1. Go to your wordpress admin "yoursite.com/wp-admin"

2. Login and then access "yoursite.com/wp-admin/plugin-install.php?tab=upload

3. Upload and activate this plugin

4. Now go to admin menu -> settings -> KBD Settings

5. Your email is by default administrator email to send backup emails, but you are required to press save changes button once (at-least)

6. That's it, now wait for the magic

Method-B:

1.	Download the Keep Backup Daily installation package and extract the files on your computer. 
2.	Create a new directory named `Keep Backup Daily` in the `wp-content/plugins` directory of your WordPress installation. Use an FTP or SFTP client to	upload the contents of your Keep Backup Daily archive to the new directory that you just created on your web host.
3.	Log in to the WordPress Dashboard and activate the Keep Backup Daily plugin.
4.	Once the plugin is activated, a new **KBD Settings** sub-menu will appear in your Wordpress admin -> settings menu.

[Keep Backup Daily Quick Start]: <http://androidbubble.com/blog/website-development/php-frameworks/wordpress/plugins/wordpress-plugin-keep-backup-daily/1046>

== Frequently Asked Questions ==

= How to setup backup schedule? =
Go to settings page. Select daily / weekly / monthly / yearly backup. Enter recipient email address. Then select Cron Job Settings and click "Save Changes". (In Premium version you have to click on "Show me basic features". See screenshot-5)

= What is defualt settings? =
For your convenience, we are providing Cron schedule from our website androidbubbles.com to the URL https://www.androidbubbles.com/api/kbd.php. For this purpose, we keep your domain name with us to access it e.g. http://www.yourdomain.com/?kbd_cron_process=1

= How to download backup? =
Click on "Download Backup Now" under backup required on settings page. It will lead you to available backup page.

= How to restore backup files? =
On settings page there is a link provided "How to restore backup files?" But in premium version in backup archives under actions you can restore (files only, not database) with one click.

= Can I edit backup title? =
Yes, on available backup page you can edit backup files title. You can also delete / download backup files. In Premium version before creating backup you can choose a title for the backup file.

= Does this plugin provide downloadable backup file? =
YES

= Is it secure? If yes, how? =
It immediately removes the temporary backup file and never reveals the temporary backup file path.

= What if i am not getting backup email? =
Immediately report to the plugin author via support tab or on mentioned plugin URL.

= I have some other queries, other than this plugin, may i ask to the plugin author? =
YES, if the queries are about WordPress and data security then you are welcome.

= What best method is to contact plugin author? =
It is good if you use support tab or plugin's author blog. If you want to reach the author immediately then use contact form on his blog.

= My website database is really big and this plugin is not handling it, what should i do? =
Contact plugin author, he might will suggest you to exclude some tables and will suggest you to backup only important ones regularly.

= What about the files backup? =
Files backup feature is available in premium version.

= Is there any premium addon for this plugin or any feature which is not in this version? =
As large databases can not be emailed so these all exceptional cases are handled in premium version.



== Screenshots ==
1. Settings page (Free version).
2. Settings saved and success message appeared.
3. Recommended links and requirements list.
4. Instantly email / download backup. Edit back up title and download / delete backup and latest backup.
5. Settings page (Pro version).
6. Backed up files detail and backup archives.
7. Find and replace.
8. Download Media Library folders.


== Changelog ==
= 2.0.9 =
* Fix: The issue at hand is the fact the file name is guessable (or enumerable) because you are relying on a time-based filename. [17/10/2024][Thanks to Joshua Chan | patchstack & WordPress Plugin Review Team]
= 2.0.8 =
* Fix: This plugin has been closed as of October 8, 2024 and is not available for download. This closure was temporary, pending a full review. [17/10/2024][Thanks to Joshua Chan | patchstack & WordPress Plugin Review Team]
= 2.0.7 =
* Fix: Some unknown critical error reported. [25/01/2024][Thanks to Rob Wolthuizen]
= 2.0.6 =
* Updated for WordPress version. [24/05/2023]
= 2.0.5 =
* wp_kses_post implemented on the settings page for the success message. [14/09/2022][Thanks to grandpadavid]
= 2.0.4 =
* Improved the security measures. [07/07/2022][Thanks to Plugin Vulnerabilities]
= 2.0.3 =
* Improved the security measures. [21/05/2022][Thanks to Eduardo Azevedo]
= 2.0.2 =
* Assets updated.
= 2.0.1 =
* Media Library export feature added. [Thanks to Team Ibulb Work]
= 2.0.0 =
* Plugins page broken string issue resolved. [Thanks to @bkacat]
= 1.9.9 =
* Backup filename duplication issue - fixed. [Thanks to Abu Usman]
= 1.9.8 =
* Find and replace feature added. [Thanks to Ibulb Work Team]
= 1.9.7 =
* Languages updated. [Thanks to Abu Usman]
= 1.9.6 =
* A few important updates.
= 1.9.5 =
* Improved version with switchable Premium features. [Thanks to Ibulb Work Team]
= 1.9.4 =
* Fatal error: Cannot declare class "CompressNone" fixed. [Thanks to nowliveit]
= 1.9.3 =
* Installation fatal error reported and fixed. [Thanks to nowliveit]
= 1.9.2 =
* Languages refined. [Thanks to Abu Usman]
= 1.9.1 =
* Languages added. [Thanks to Abu Usman]
= 1.9.0 =
* Fixed: WordPress Plugin Security Vulnerability / Missing Validation on TLS Connections
= 1.8.9 =
* Backup size display against the row.
= 1.8.8 =
* Backup now option has been refined.
= 1.8.7 =
* Sanitized input and fixed direct file access issues.
= 1.8.6 =
* Improved version. [Childhood Champions]
= 1.8.5 =
* Improved version. [Dedicated to KZ]
= 1.8.4 =
* Backup now option improved.
= 1.8.3 =
* Download now option improved.
= 1.8.1 =
* A few important improvements.
= 1.8.0 =
* A few important improvements.
= 1.7.1 =
* Speed Optimization Fix
= 1.7.0 =
* mysql functions are replaced with $wpdb
= 1.6.2 =
* A minor issue is fixed.
= 1.6.1 =
* A minor layout issue is fixed.
= 1.6 =
* Settings form improved for better user experience.
= 1.5.5 =
* Settings won't be deleted on version update from next time.
= 1.5.4 =
* Old backups will be removed if couldn't sent to you because of email address field was empty. And by default you will get backups on admin email.
= 1.5.3 =
* Code Precision Process: Email Validation Function Updated.
= 1.5.2 =
* Recommended Links are added and backup now feature is updated.
= 1.5.1 =
* Requirements console updated.
= 1.5 =
* Requirements console added.
* On upgrade, settings won't be wasted.
* Admin email will not be stored in settings file. (Security Fix)
* wp_enqueue_style related fix. (Thanks to jelnet)
= 1.4.9 =
* Now you will get a proper HTML email instead of plain text one.
* Log file was calculating size of .zip file only. Now it will also calculate if .sql file is not zipped.
* Download backup now option is visible now. It was mistakenly hidden before. Functionality was there but never been asked so i forgot to make it visible.
= 1.4.8 =
* If zip library will not be available on your hosting, still you will get backup as .sql file. Cheers!
= 1.4.7 =
* Zip archive will not reveal your directory structure on unzip action. (Credit goes to Bilal TAS)
= 1.4.6 =
* Error: undefined function mcrypt_create_iv() fixed.
= 1.4.5 =
* Layout fixes
= 1.4.4 =
* Donate section added...
= 1.4.3 =
* Exception Hanlded: session_start() was having problem with rest of the plugins files with headers already started message. It is fixed.
= 1.4.2 =
* Bug Fixed: Default email is now your administrator email instead of info@yoursite.com because most of the bloggers don't use info email address. So admin email will be filled automatically.
= 1.4.1 =
* Bug Fixed: Output buffer bug fixed. ob_start() was required to move forward with other plugins compatibility.
= 1.4 =
* New Feature: Database size will be available in log.
= 1.3 =
* New Feature: Click here to backup now
= 1.2.1 =
* Expected backup email time bug is fixed.
= 1.2 =
* Scheduled time for database backup is displayed
* Maximum execution time input field removed for convenience of the users. Now it will manage all kind of databases automatically.

== Upgrade Notice ==
= 2.0.9 =
Fix: The issue at hand is the fact the file name is guessable (or enumerable) because you are relying on a time-based filename.
= 2.0.8 =
Fix: This plugin has been closed as of October 8, 2024 and is not available for download. This closure was temporary, pending a full review.
= 2.0.7 =
Fix: Some unknown critical error reported.
= 2.0.6 =
Updated for WordPress version.
= 2.0.5 =
wp_kses_post implemented on the settings page for the success message.
= 2.0.4 =
Improved the security measures.
= 2.0.3 =
Improved the security measures.
= 2.0.2 =
Assets updated.
= 2.0.1 =
Media Library export feature added.
= 2.0.0 =
Plugins page broken string issue resolved.
= 1.9.9 =
Backup filename duplication issue - fixed.
= 1.9.8 =
Find and replace feature added.
= 1.9.7 =
Languages updated.
= 1.9.6 =
A few important updates.
= 1.9.5 =
Improved version with switchable Premium features.
= 1.9.4 =
Fatal error: Cannot declare class "CompressNone" fixed.
= 1.9.3 =
Installation fatal error reported and fixed.
= 1.9.2 =
Languages refined.
= 1.9.1 =
Languages added.
= 1.9.0 =
Fixed: WordPress Plugin Security Vulnerability / Missing Validation on TLS Connections
= 1.8.9 =
Backup size display against the row.
= 1.8.8 =
Backup now option has been refined.
= 1.8.7 =
Sanitized input and fixed direct file access issues.
= 1.8.6 =
Improved version.
= 1.8.5 =
Improved version.
= 1.8.4 =
Backup now option improved.
= 1.8.3 =
Download now option improved.
= 1.8.1 =
A few important improvements.
= 1.8.0 =
A few important improvements.
= 1.7.1 =
Speed Optimization Fix
= 1.7.0 =
An important update is here.
= 1.6.2 =
A minor issue is fixed.
= 1.6.1 =
A minor layout issue is fixed.
= 1.6 =
Settings form improved for better user experience.
= 1.5.5 =
Settings won't be deleted on version update from next time.
= 1.5.4 =
Sometimes users forget to enter email address and backup files never sent to their emails but remain in directory. This update will clear those files. And by default you will be receiving backups on your admin email address.
= 1.5.3 =
Code Precision is in Process.
= 1.5.2 =
If you are using backup now option? Then must install this release.
= 1.5.1 =
Requirements console update for write permissions.
= 1.5 =
Requirements console added. On upgrade, settings won't be wasted.
= 1.4.9 =
HTML Email, Download backup option and database size without zip as well.
= 1.4.8 =
If you were not getting email with backup because of zip library not available on available on your hosting then must upgrade to this version.
= 1.4.7 =
Zip archive code is available with clean directory structure now.
= 1.4.6 =
An error is fixed for special cases. If you are not having any problem, no need to update the version.
= 1.4.5 =
Few layout fixes are made
= 1.4.4 =
Upgrade it to show your love.
= 1.4.3 =
@session_start(); is used to avoid warning messages.
= 1.4.2 =
Another convenience added! Now your administrator email will be entered automatically instead of info@yoursite.com.
= 1.4.1 =
ob_start() is used to move forward with other plugins compatibility.
= 1.4 =
New Feature added that Database size will be available in log.
= 1.3 =
New Feature added "Click here to backup now" for those who are careful about their databases before installing or trying anything new.
= 1.2.1 =
Expected backup email time bug is fixed.
= 1.2 =
User friendliness related improvements, will not effect anything else.= Upgrades =To *upgrade* an existing installation of Keep Backup Daily to the most recent
release:
1.	Download the Keep Backup Daily installation package and extract the files on your computer. 
2.	Upload the new PHP files to `wp-content/plugins/Keep Backup Daily`,	overwriting any existing Keep Backup Daily files that are there.
3.	Log in to your WordPress administrative interface immediately in order to see whether there are any further tasks that you need to perform to complete the upgrade.
4.	Enjoy your newer and hotter installation of Keep Backup Daily

[Keep Backup Daily project homepage]: <https://www.androidbubbles.com/extends/wordpress/plugins>

== License ==
This WordPress Plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version. This free software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this software. If not, see http://www.gnu.org/licenses/gpl-2.0.html.