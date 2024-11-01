=== Events Manager – Event Cancellation ===
Plugin Name: 		Events Manager – Event Cancellation
Contributors: 		DuisterDenHaag
Tags: 				Events Manager, cancel, status, booking, email
Donate link: 		https://useplink.com/payment/VRR7Ty32FJ5mSJe8nFSx
Requires at least: 	5.4
Tested up to: 		6.0
Requires PHP: 		7.3
Stable tag: 		trunk
License: 			GPLv2 or later
License URI: 		http://www.gnu.org/licenses/gpl-2.0.html


Adds the "Event Cancelled" status to your EM event and auto-emails a notification to your customers.


== Description ==
> Requires [Events Manager](https://wordpress.org/plugins/events-manager/) (free plugin) to be installed & activated.


Unfortunately, you sometimes have to cancel a planned event. In Events Manager it can be quite cumbersome to make sure you have done everything. This add-on makes that extremely easy!

Just change the Post Status to "Cancelled" and this add-on will automatically:

- Close the Booking Form by adjusting the Event cut-off time to the timestamp of cancellation;
- Change the Event Category to the new "Event Cancelled";
- Change the Booking Status to the new "Event Cancelled" for selected booking statuses;
- Send the new "Event Cancelled" Email to notify your customers.
- After cancelling an event, it will still be visible in the front end, but the booking form will be closed. This prevents 404-errors for visitors and search engines.
- Refunding online payments remains a manual procedure.

This add-on is fully integrated with the Events Manager Dashboard. These new options will work exactly the same as the built-in ones.

== Supports EM in Global Tables Mode ==
If you are running Events Manager on a WordPress MultiSite with Global Tables Mode enabled, you will probably have noticed that (by default) some features are missing on the sub-blogs. Unlike what the EM Dev told me, it <em>is</em> possible to filter the Admin Events List per Event Category if Global Tables Mode is enabled.

Since version 2.0 this add-on is (almost) fully compatible with that. The <em>only</em> thing still missing, is selecting Events Categories in the Quick Edit menu on sub blogs. But all other features of this add-on run smoothly.


**Compatibility with [EM - Ongoing Events](https://wordpress.org/plugins/stonehenge-em-ongoing-events/):**
If you cancel a main event, all sub events will be cancelled as well, but if you cancel a sub event, the rest of this Ongoing Series will remain unchanged.


= How to test it? =
Simply create a test event like any other. Publish it and add yourself as a participant. Then set the test event to "Cancelled" and watch the magic happen.


== Feedback ==
I am open to your suggestions and feedback!
[Please also check out my other plugins, tutorials and useful snippets for Events Manager.](https://www.stonehengecreations.nl/)


== Frequently Asked Questions ==
= Are Refunds automated? =
No, if you cancel an event refunding the online payment has to be done manually. Events Manager allows for many Payment Gateway add-ons and all work differently behind the scenes. Also, refunds might be subject to your General Terms & Conditions.

You could consider offering your customer to [Move the Booking](https://wordpress.org/plugins/stonehenge-em-move-bookings/) instead.

= Is this add-on compatible with EM Ongoing Events? =
Yes, it is. If you have [EM - Ongoing Events](https://wordpress.org/plugins/stonehenge-em-ongoing-events/) installed and cancel the Main Event, all Sub Events will be cancelled as well. If you cancel a Sub Event, only that occurrence will be cancelled, leaving the rest of the Ongoing Series as is.


= Are you part of the Events Manager team? =
**No, I am not!**
I am not associated with [Events Manager](https://wordpress.org/plugins/events-manager/) nor its developer, [Marcus Sykes](http://netweblogic.com/), in <em>**any**</em> way.


== Installation ==
1. Download and activate this plugin in your WordPress Admin Dashboard.
2. Fill out a few settings in the plugin options page.
3. All set!


== Screenshots ==
1. Clearly identifiable in the Events List.
2. Cancel your event easily in Quick Edit.
3. Cancel your event in the Event Edit Page or Quick Edit.


== Upgrade Notice ==
There several important and major changes! Please check your settings after updating.


== Changelog ==
= 2.0.1 =
- Added: TinyMCE editor to the email content box in the settings page for easier styling.
- Confirmed compatibility with WordPress 5.5.
- Minor code changes.

= 2.0.0 =
- **NEW:** Creates a new Booking Status for cancelled events.<br>&nbsp;
- **NEW:** Uses a separately defined email to notify your customers.<br>&nbsp;
- **NEW:** Option to yes/no send the automated notification email.<br>&nbsp;
- **NEW:** Select which booking status you want to include.<br>&nbsp;
- **NEW:** Option to show (and filter!) Event Categories in the Admin Events List.<br>Also on WP MultiSite and even with Global Tables mode enabled.<br>&nbsp;
- **NEW:** This add-on is now 100% compatible with [EM Ongoing Events](https://www.stonehengecreations.nl/creations/stonehenge-em-ongoing-events/). <br>If installed, and you cancel the main event, all sub events will be cancelled as well. Cancelling one sub event, will not alter the the rest of the Ongoing Series.<br>&nbsp;
- Bug fix: Changed the way bookings are being closed, to prevent fails in some cases.
- Bug fix: Better handling of the Event Categories, depending on the post status.
- Updated: The .pot file for translations.
- Updated: Dutch translation (included in the download).
- Updated: The readme.txt file.

**Note:** The first two changes free-up the "Rejected" booking status used in previous versions.

= 1.3.0 =
- Fixed: Bookings not closing when event has been cancelled.
- Fixed: Labels not showing in Publish Meta Box.
- Confirmed compatibility with WordPress 5.4.2.
- Confirmed compatibility with PHP 7.4.2.

= 1.2.2 =
- New graphics for WP Repository.
- Updated readme.txt.

= 1.2.1 =
- Minor code updates.

= 1.2 =
- Changed the way the status "Event Cancelled" is handled by Events Manager. Making it more stable with fewer lines of code.

= 1.0 =
- Initial release to the WordPress Repository.
