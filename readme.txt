=== Plugin Name ===
Contributors: shazahm1@hotmail.com
Donate link: http://connections-pro.com/
Tags: addresses, address book, addressbook, bio, bios, biographies, business, businesses, business directory, business-directory, church, contact, contacts, connect, connections, directory, directories, hcalendar, hcard, ical, icalendar, image, images, list, lists, listings, microformat, microformats, page, pages, people, profile, profiles, post, posts, plugin, shortcode, staff, user, users, vcard
Requires at least: 3.0
Tested up to: 3.1.3
Stable tag: 0.7.1.6
Connections is a simple to use directory manager for an addressbook, business, staff or church directory.

== Description ==
Test
Connections is a simple to use and versatile directory manager. You can use it for an address book, create a church directory, business directory, staff directory and even profiles pages by using templates. If none of the default templates suit your needs, you can easily create your own custom template. Want to show a list of upcoming birthdays and anniversaries, no problem, you can do that too. Take a look at the screen shots and the samples to see all Connections can do.

= Requirements =
* **WordPress version:** >= 3.0
* **PHP version:** >= 5.1.0
* Upgrading from version 0.6.1 and newer is supported. Previous version must upgrade to 0.6.1 before upgrading to the current version.

= Admin Features =
* Built-in help page
* Selectable entry type; Individual / Organization / Family 
* Each entry has selectable visibility. You can choose between, public, private and unlisted.
* Image support with the ability to set custom images sizes and how they should be scaled and cropped.
* Copy entries.
* Bulk actions that include setting the visibility on deleting entries.
* Filters that are persistent per user between sessions and browsers.
* Extensive role support.
* Category Support. Categories can be hierarchical and entries can be assigned to any number of categoies.

= Frontend Features =
* xHTML Transition output
* Custom template support
* Shortcode filter attributes for the entry list that include being able to filter by last name, title, organization, department, city, state, zip, country and category.
* Shortcode attributes for choosing the supplied templates or custom templates which include; single entry, multiple entry (default) and profile view templates.
* Shortcode attribute to repeat the alpha index and the beginning of each character change. [User requested.](http://wordpress.org/support/topic/266754)
* Shortcode attribute to show the current character at the beginning of each character group. [User requested.](http://wordpress.org/support/topic/266754)
* Entries output in [hCard](http://microformats.org/wiki/hcard) compatible format.
* Download the vCard of an individual entry that can be imported into you email application.

= New features this version: =
* Added Contact First and Last name fields the the Organization entry type.
* Added honorable prefix and honorable suffix fields.
* Added a template manager to the admin.
* Templates now support stylesheets.
* Templates now support javascript.
* Added RSS option under social media.
* Added Podcast option under social media.
* Added SoundCloud option under social media.
* Improved the rich text editor by using the bundled tinyMCE.
* Added three additional order_by attributes; date_added, date_modified and title.
* Added Skype option under the messenger IDs.
* Added the option to upload a logo in addition to an image.
* Latitude and longitude can now be set with each address.
* Add exclude_category shortcode attribute.
* Convert the add/edit entry form to use the WP metaboxes and 'Screen Options' feature to allow some form customization.

= New features coming in the next version: =
* Add in_category_name shortcode attribute. Define multiple categories by name that an entry must be assigned in order to be displayed. [Operational AND]
* Add exclude_category_name shortcode attribute to allow multiple categories by name entries to be excluded from the output.

= Upcoming features: =
* Pagination
* Search
* Localization
* Make the Connection Group relations in the front end entry list clickable to bring up the entry's specific details.
* Integration with WP users to permit registered users to maintain their own info with optional moderation.
* Gravatar support
* Backup
* ...and any suggestion that I may receive...

= Credits: =
* This plugin was based off LBB, ["Little Black Book"](http://wordpress.org/extend/plugins/lbb-little-black-book/); which was based on [Addressbook](http://wordpress.org/extend/plugins/addressbook/), both of which can be found in the Plugin Directory.
* vCard class is a modified version by [Troy Wolf](http://www.troywolf.com/articles/php/class_vcard/)
* Image uploading and processing done by the class.upload.php by [Colin Verot](http://www.verot.net/php_class_upload.htm)
* Counter class from O'Reilly's [Intro to PHP Objects](http://www.onlamp.com/pub/a/php/2002/07/18/php_foundations.html?page=2)
* Update Notice in plugin admin inspired by Changelogger 1.2.8 by [Oliver Schlöbe](http://wordpress.org/extend/plugins/changelogger/).
* class.upload.php by [Colin Verot](http://www.verot.net).
* TimThumb by [Ben Gillbanks and Mark Maunder](http://code.google.com/p/timthumb/).
* vCard class is a modified version by [Troy Wolf](http://www.troywolf.com/articles/php/class_vcard/).
* Screen Options class by [Janis Elsts](http://w-shadow.com/blog/2010/06/29/adding-stuff-to-wordpress-screen-options/).
* spin.js by [Felix Gnass](http://fgnass.github.com/spin.js/).

== Screenshots ==
[Samples and screenshots can be found here](http://connections-pro.com/?page_id=52)


== Installation ==
1. Upload the `connections` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. If you wish to create and use custom template be sure to create the `./wp-content/connections_templates` directory/folder. This is where you will copy any custom templates you might create.
4. Add the shortcode `[connections_list]` to display the directory on a page or post.

== Frequently Asked Questions ==
[FAQs can be found here](http://connections-pro.com/?page_id=56)

== Changelog ==

= 0.7.2.0 XX/XX/XX =
* FEATURE: Search.
* FEATURE: Added a Dashboard page in the admin.
* FEATURE: Added a Today's Birthdays admin dashboard widget.
* FEATURE: Added a Upcoming Birthdays admin dashboard widget.
* FEATURE: Added a Today's Anniversaries admin dashboard widget.
* FEATURE: Added a Upcoming Anniversaries admin dashboard widget.
* FEATURE: Added a Recently Added dashboard widget.
* FEATURE: Added a Recently Modified admin dashboard widget.
* FEATURE: Added a Connections News admin dashboard widget.
* FEATURE: Added a Template Update News admin dashboard widget.
* FEATURE: Added a Pro Module New admin dashboard widget.
* FEATURE: Added a System Info / Configuration admin dashboard widget.
* FEATURE: New role capabilities that allow add and edit moderation.
* FEATURE: Added more actions/filters to hook into.
* FEATURE: Added hooks to allow templates o be configured via the shortcode so the template's funcions.php file does not need edited.
* FEATURE: Collapsible and sortable addresses and ect., when adding or editing an address and ect.
* BUG: The view_public capability was being removed when role capabilities were being reset.
* BUG: Fix many/most/all  warnings and such reported when WP_DEBUG is TRUE.
* BUG: Remove the connections_view_entry_list role as it is no longer needed.
* BUG: Use gmmktime() instead of mktime() when working with the birthday and anniversary so dates are output showing the current date.
* BUG: Fixed a bug with applying the cn_list_before filter causing the template CSS to output twice.
* BUG: Fixed an issue with getAddressBlock() showing only the last input address.
* BUG: Removed the Screen Options tab from the Connections Manage page unless an action was being performed on an entry such as edit/copy.
* BUG: Fixed some minor bugs in the upgrade function.
* OTHER: Remove the meta Connections version from the head as it failed HTML5 validation. Added to the connections div as an HTML5 data attribute.
* OTHER: Disabled JavaScript compression on the Add/Edit Entry page. When enabled the RTE wouldn't load to JS errors.
* OTHER: Update Roles and Capabilities page to use the metabox visual styles.
* OTHER: Updated the help page. Fixing typos and adding more info.
* OTHER: Update the settings page to match the WordPress setting page styles for better integration.
* OTHER: Update the categories page style to match the WordPress categories page styles for better integration.
* OTHER: Update the role page style to use the post metabox style for better integration.
* OTHER: All processes are now handled by a pocess method rather than hooking into the current page for better role capability support.
* OTHER: Remove the Donate link from the meta links on the Plugins page.
* OTHER: Added a entry meta table for future support for custom entry details.
* OTHER: Tweaked the header of the Manage / Copy / Edit screen.
* OTHER: Added a cache folder during activation for future use.
* OTHER: Removed 'Select' from the address type select to force user selection.
* OTHER: Set the 'Member' template as a 'family' template type.
* OTHER: Set the 'Single Entry Card' template as a 'individual' template type.
* OTHER: Greatly improved support for using the shortcode multiple times on the same page.
* OTHER: Improved the pagination support on the Manage page. The limit is now user adjustable.
* OTHER: Completely revamped the db structure. Using the original table columns for addresses, ect., for an object to keep db queries to a minimum.
* OTHER: Added support for WP 3.3.
* OTHER: Use wp_editor when user is running WP 3.3+.
* OTHER: Moved the filters to be part of the entry retrieve query. Made possible by the new db structure.
* OTHER: Moved the order by option to be part of the entry retrieve query. Made possible by the new db structure.
* OTHER: Upgrade the class.php.upload class to .31
* OTHER: Added debug logging and display for image uploads.

= 0.7.1.6 06/15/2011 =
* Fixes security vulnerability.

= 0.7.1.5 03/06/2011 =
* FEATURE: Pagination in the admin.
* FEATURE: Added YouTube, MixCloud, ReverbNation, iTunes and CD Baby to the social media field.
* BUG: Use gmdate() instead of date() when working with the birthday and anniversary so dates are output showing the current date.
* BUG: Fixed bug where the last address/phone number/email/messenger id/social media id/ website could not be removed.
* BUG: Fix many/most/all  warning and such reported when WP_DEBUG is TRUE.
* BUG: Fix WP version check in the displayUpgrade method.
* OTHER: Added a comma in the getAddressBlock()
* OTHER: Removed the PayPal Donate buttons from the Manage and Help pages.
* OTHER: Add Extend and Template to the meta links on the plugins admin page.
* OTHER: Add a 'Get More' button to the template admin page.

= 0.7.1.4 Unreleased =
* FEATURE: Add in_category shortcode attribute. Define multiple categories by id that an entry must be assigned in order to be displayed. [Operational AND]
* BUG: Fixed bug with category recursion.
* BUG: Fixed bug where identicle categoriy names/IDs would be included in the query.
* OTHER: Moved the shortcode entry filters to be processed right after the results query.
* OTHER: Removed the copying of the download.vCard.php to the WP root during activation. Instead hooked into parse_request for vCard download. 
* OTHER: Fixed the template in the sample folder from 0.7.1.1 so it functions and renamed it to Members.
* OTHER: Registered many query variable that can be processed via the parse_request hook.

= 0.7.1.3 01/17/2011 =
* OTHER: No Changes. SVN error.

= 0.7.1.2 01/17/2011 =
* BUG: Fixed the width and height setting being swapped in the getLogoImage() method.

= 0.7.1.1 01/15/2011 =
* FEATURE: Added Contact First and Last name fields the the Organization entry type.
* FEATURE: Added honorable prefix and honorable suffix fields.
* FEATURE: Added a template manager to the admin.
* FEATURE: Templates now support stylesheets.
* FEATURE: Templates now support javascript.
* FEATURE: Added RSS option under social media.
* FEATURE: Added Podcast option under social media.
* FEATURE: Added SoundCloud option under social media.
* FEATURE: Improved the rich text editor by using the bundled tinyMCE.
* FEATURE: Added three additional order_by attributes; date_added, date_modified and title.
* FEATURE: Added Skype option under the messenger IDs.
* FEATURE: Added the option to upload a logo in addition to an image.
* FEATURE: Latitude and longitude can now be set with each address.
* FEATURE: Add exclude_category shortcode attribute.
* FEATURE: Convert the add/edit entry form to use the WP metaboxes and 'Screen Options' feature to allow some form customization.
* BUG: Fixed PHP Warning: Call-time pass-by-reference has been deprecated error in the admin.
* BUG: The last updated time and date now function correctly.
* BUG: Fix MySpace spelling typo. Any entries saved with a MySpace link will need to be updated.
* BUG: Fix the upcoming birthday/anniversary query to show today's birthdays as well as to take into account the current time zone set in the WordPress General settings.
* BUG: Fix the sort algorithm for sorting the upcoming birthday/anniversary list by date.
* BUG: Fix birthday/anniversary with date of January 1st not being saved.
* BUG: Fix Upgrade notice to be compatible with WordPress => 3.0.
* BUG: Fix entry drop down when creating a family. Drop downs would only show entries based on the current filter setting for the current user in the admin. The drop down will now show all permitted entries.
* BUG: Fix public entries "Allow unregistered vistors and users..." setting not being saved.
* BUG: Fix DB upgrade method logic.
* BUG: Strip HTML from the Note output for vCard.
* OTHER: Entry human time difference in now based the current blog time.
* OTHER: The local timestamp will be used when adding/updating an entry rather than the server timestamp.
* OTHER: The last updated time will be adjusted to the local timestamp.
* OTHER: Connections Groups entry type renamed to Families.
* OTHER: Change substr to mb_substr for better support of multibyte character sets.
* OTHER: Added total record count based on current user permissions.
* OTHER: Further reduced memory requirements.
* OTHER: Initiate plugin settings only on activation.
* OTHER: Shortcode can now be called as [connections]

= 0.7.0.4 7/26/2010 =
* FEATURE: Add the option to display the front end list in a specified order.
* FEATURE: Add the option to display the front end list in a random order on each page visit/refresh.
* BUG: Fixed typo that caused the website URLs to be invalid.
* BUG: Fixed so "Select" wouldn't be saved as the address type.

= 0.7.0.3 7/20/2010 =
* FEATURE: Set website urls to open in a new window/tab as the default.
* BUG: Set initial DB version during activation.
* BUG: Fix many errors reported when WP_DEBUG is set to true.
* BUG: Fix database table check before the table is created during activation.
* BUG: When an entry is added, the last modified by field is now correctly set.
* BUG: The intial entry category relationship would not be set correctly to uncatgorized if no categories were selected.
* BUG: Fix recursive category children when using specified categories multiple times on the same page.
* OTHER: Reduced memory requirements further by passing a few large objects by reference rather than creating new instances.
* OTHER: Add UTF-8 support in the vCard file.
* OTHER: Base64 encode image into the vCard rather than the URI.
* OTHER: When creating tables the character set and collate will honor the settings in wp-config.php or the default settings for WP.
* OTHER: Upgrade jWYSIWYG to version .92.
* OTHER: Added support for the CSV Import Pro Module

= 0.7.0.2 6/12/2010 =
* FEATURE: Add upgrade notice to the plugin admin page.
* BUG: Fix WYSIWYG <em> tag parse.
* BUG: Fix order_by bug. Would not sot sort by city, state, zipcode, country.
* OTHER: Add missing documentions for the social media template tags.

= 0.7.0.1 5/17/2010 =
* BUG: Fix fatal error when custom ordering the results.
* BUG: Fix CSS error for a gradient.

= 0.7.0.0 5/16/2010 =
* FEATURE: Add links on the plug-in management admin page for Settings, Help, Support and Donate.
* FEATURE: Added group_name shortcode attribute filter.
* FEATURE: Theme Template tag for theme developement to display the entry list. All shortcode attributes are supported.
* FEATURE: Theme Template tag for theme developement to display the upcoming list. All shortcode attributes are supported.
* FEATURE: The category shortcode attribute has been expanded to allow multiple categories to be called via a comma separated list. If the category has children entries, they will has display (recursively).
* FEATURE: Added shortcode attribute, wp_current_category. When this is set to true and the shortcode is used in a post, all entries that have the same category name will be displayed.
* FEATURE: Allow more than one website address to be entered per entry.
* FEATURE: Automatically add the http:// to the website addresses.
* FEATURE: Automatically add the http:// to the social network urls.
* FEATURE: Add vCard download link to entry actions.
* FEATURE: Add Upgrade Notice text to the readme.txt.
* FEATURE: Show the entries assigned categories in the entry list.
* FEATURE: Categories are add to the vcard div class.
* FEATURE: Add template tag to show an entries assigned categories.
* BUG: Set the db version after each db upgrade cycle.
* BUG: Add the missing social media block to the output class, default templates and help page.
* BUG: Fix typos in the help page.
* BUG: Fixed improper use of the prepare method when saving and updating entries.
* BUG: Fix issue causing the adding/editing of entries to fail when the '%' character was used.
* BUG: Fix error caused by empty $results array in the shortcode.
* BUG: Editing a Connection Group caused it to show up twice in the list and only one was editable while the other gave an error.
* BUG: Fixing saving the entry type.
* BUG: The shortcode filter attributes were not escaped. So strings with extended characters would fail to return a match.
* BUG: Fixed elements left behind after removing fields in the entry form.
* BUG: Remove entry images from the server when they are removed from the entry.
* BUG: If an image was manually deleted from the server, the image tag will not be output.
* BUG: Fixed bug that allowed duplicate categories to be created.
* BUG: Fixed issue where database would go thru the update cycle on a new install.
* OTHER: Add instructional text to the image settings.
* OTHER: Add rel="nofollow" to the vCard download link.
* OTHER: Removed the use of $_SESSION throughtout the plug-in.
* OTHER: Enhance plug-in security through the use of nonce.
* OTHER: Move the methods from cnConvert class to the cnFormatting class.
* OTHER: Move the class.upload.php to the image processing method to remove 1.3MB of plug-in overhead.
* OTHER: Reduce memory overhead.
* OTHER: Reduce database queries.
* OTHER: More efficient query to return entires.
* OTHER: Removed the 'custom_template' shortcode attribute. Instead, the custom template folder will be checked for the specified template.
* OTHER: Moved the template file checking out of the entry list loop to reduce server file system IO.
* OTHER: Add place holders in the admin entry list if no image is associated to an entry.
* OTHER: Add dependecies and versions to all wp_enqueue_script / wp_enqueue_style calls.
* OTHER: Upgrade the class.php.upload class to .29
* OTHER: Better documentation throughout.

= 0.6.2.1 2/3/2010 =
* BUG: Fixed fatal error when using the shortcode attribute repeat_alphaindex.

= 0.6.2 2/2/2010 =
* BUG: Fixed unable to remove phone numbers.
* BUG: Fixed when removing a field from an entry that the entire field is removed leaving no remnants.
* Add support for basic HTML and RTE for the Notes and Bio fields using the jwysiwyg 0.6 jOquery plug-in.
* Add order_by shortcode attribute for custom ordering of entries and updated the help page.
* Add support for the CSV premium add-on.
* Add the missing getBioBlock method to the output class.
* Enhanced the help page.

= 0.6.1 1/19/2010 =
* BUG: Fixed a bug when initializing the settings so they are not reset on activation of the plugin.
* BUG: Fixed how capabilities were created and managed for public entries.
* BUG: Fixed how the entry edit link was created which was causing a token mismatch.
* BUG: Fixed a permission error when using the shortcode override attributes.
* Updated class.upload.php to version .28
* Remove SQL class, didn't make sense to use it.
* Defined table names as constants.
* Options class now is used throughout the plug-in rather than creating new instances.
* Renamed all the classes to be more unique to help avoid conflict with other plug-ins.
* Re-worked the way method used to store the cached entry list filters to use the user_meta table.
* Capturing more meta data when added/updating entries; date added, added by, last edited by.
* Added and middle name. Support extended to hCard and vCard.
* Added a setting to disable the private override shortcode attribute.
* Added a filter class that can easily be extended. Currently supports filtering by visibility and entry type.
* Added a permission filter. All queries are run thru this filter removing any entries the current user is not permitted to view.
* Changed it so the upgrade message and version error only show on the Connections admin pages.
* Started to enable support for SSL connections.
* Changed minimum supported WP version to 2.8 [Used 2.8 only function for editable roles]
* Add check for $_SESSION save path and throw an error if it isn't found.
* Add category support.
* Add upgrade routine to support new features and some planned future features.
* All strings output from the cnEntry and related classes are now sanitized.

= 0.5.48 9/14/2009 =
* Fixed a jQuery bug in IE8.
* The alpha index in the admin is now dynamic. It will only show letters for entries in the list.

= 0.5.47 9/3/2009 =
* Updated class.upload.php to version .25
* Added extensive role support.
* Added the ability to set custom image sizes.
* Added the ability to determine how an image should be scaled and cropped.
* Extensive backend code changes.
* Focus on making sure the plug-in is secure.

= 0.5.1 - 6/21/2009 =
* Added a shortcode attribute to repeat the alpha index and the beginning of each character change. [User requested.](http://wordpress.org/support/topic/266754)
* Added a shortcode attribute to show the current character at the beginning of each character group. [User requested.](http://wordpress.org/support/topic/266754)
* Added additional filters for addresses. [User requested.](http://wordpress.org/support/topic/248568)
* Run the SQL queries through the `$wpdb->prepare()` method for security
* Change the change log so it shows up as a top level tab on the WordPress plug-in page

= 0.5.0 =
* Adding/Editing/Copying now use the class
* Added bulk delete.
* Added an entry type of Connection Group. This allows to you create relational links between entries.
* Moved the plug-in to be a top level menu item.
* Added a help sub-page.
* Added a setting page. Settings will actually be available in the next version.
* Added a donate button - a user request.
* Added a spiffy icon for the menu and page headers.

= 0.4.0 =
* Added hCard compatible markup
* xHTML Transitional valid output
* Added hCalendar compatible markup for birthdays and anniversaries.
* Birthday/anniversary will now show the next anniversary/birthday date for the entry.
* Added support for vCard download of an entry.

= 0.3.3 =
* Added shortcode filter attributes

= 0.3.2 =
* Converted the rest of the code to use OO methods
* Started to add jQuery in the admin
* Fixed the CSS to load only in the Connections page
* All the fields that can be input are shown in the output
* Added the ability to use custom output templates and a slew of template tags
* Added a default profile template and a default single entry template

= 0.2.24 =
* Converted more code to use OO methods
* Code clean-up and documentation
* Fixed the alpha index bug not correctly working with organization entry types
* Added a shortcode to allow showing all entries; individual or organizations entries

= 0.2.23 =
* Converted more code to use OO methods
* Display some of the missing fields in the output

= 0.2.22 =
* Added Org/Individual options
* Added IM fields
* Added BIO field
* Added Org/Individual filter
* Started to convert the code to OO PHP

= 0.2.11 =
* Added a nice little up arrow to both the admin and page/post entry list to return to the top of the list

= 0.2.10 =
* Added the ability to copy an entry

= 0.2.9 =
* Some more code cleanup
* Started code documentation
* Added the ability to choose whether or not a linked image in an entry is displayed when embedded in a page/post
* Added the ability to remove the linked image from an entry

= 0.2.8 =
* Fix bug that was causing IE to not filter correctly
* Code cleanup

= 0.2.7 =
* Added preliminary image support for entries

= 0.2.4 =
* Add entry ID to the admin

= 0.2.3 =
* First version in the repository

== Upgrade Notice ==

= 0.7.1.6 =
Fixes security vulnerability.