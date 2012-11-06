=== Skt NURCaptcha ===
Contributors: Sanskritforum
Donate link: http://skt-nurcaptcha.sanskritstore.com/donate/
Tags: security, login form, new user, captcha, spambots, reCAPTCHA, register form, buddypress, wpmu, multisites, bots, spam, form, protection, response, safe, register
Requires at least: 3.0.1
Tested up to: 3.4.2
Stable tag: 3.0.0

NURCaptcha inserts a reCAPTCHA on the Register Form of your site to protect it against spambots. 

== Description ==

NURCaptcha stands for *New User Register Captcha*. It has been designed for anyone who wishes to allow 
free registration of new users for a WP site, but want to keep bots outside. It uses Google's reCAPTCHA 
tools to give your site an extra protection against those spammer bots, adding security to the WP Register Form.

Skt NURCaptcha is easy to install and doesn't slow down your site. It is called into action 
just at the moment the "Register for This Site" form is requested. It creates a challenge and 
checks the response given. If it is not valid, new user registration fails. If the response is 
valid, NURCaptcha leaves the scene and your site runs as if it was not there.

Skt NURCaptcha adds (form Version 3 on) extra security by querying trustable databases for known ip, username and email of spammers, so you get rid of them even if they break the reCaptcha challenge by solving it as real persons.

NURCaptcha also shows you each blocked attemptive. A Log file may be toggled for you to see data of the 
last attemptives blocked by the plugin. It shows date/time of trials, as well as usernames and e-mail addresses 
of those who failed registration and were kept outside. Please note that attemptives in which the spambot 
suspends its attack when confronted by the reCAPTCHA challenge can not be logged. So logfile figures will 
never reflect the whole achievements of the plugin in securing your site.

Works smoothly with **WP Multisites** (Network) and **BuddyPress**.

**Languages included**.

* Brazilian Portuguese pt_BR - by [the author](http://profiles.wordpress.org/users/Sanskritforum/ "Carlos E. G. Barbosa").
* Dutch nl_NL - by  [SalviJansen](http://wordpress.org/support/profile/salvijansen/ "SalviJansen").

== Installation ==

1. Upload 'skt-nurcaptcha' folder to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Don't forget to get the free Public and Private reCAPTCHA keys at [reCAPTCHA API Signup Page](https://www.google.com/recaptcha/admin/create "Get your free keys")
1. Go to the 'Settings' menu, locate Skt NURCaptcha Settings Page and drop your keys there.
1. That's it.

== Frequently Asked Questions ==

= I've installed and activated the plugin, but at the new user register form the reCAPTCHA doesn't show up. =

A defective version of a file called 'skt-nurc-recaptcha-locales.php' was mistakenly uploaded on march, 31st, 2012, 
by around 4:00 PM GMT. If by chance you have downloaded the plugin during the few minutes when this file was in the WP Plugins repository,
you may experiment that kind of problem. If that is your case, just deactivate and delete the plugin and then 
proceed to a new, clean, installation.

= The number of blocked attemptives is growing too slowly on the log. Does that mean that the plugin is not working? =

If you was facing a greater number of spambot registrations than the figures you see now in the log counter, that means 
the plugin is doing its job perfectly. Almost all the spambots give up their attack when confronted by the reCaptcha challenge, 
so they come and go silently. These bots may not trigger the counter, but they will not get inside your site walls, either. 

= Does NURCaptcha blocks spambots only? =

That is the main target of any captcha plugin: to catch bots and allow human beings to pass by. So it 
wont prevent you against badly mooded human intruders, as they can read and respond perfectly the 
challenge presented by the plugin.

= How can I help people who for any reason were not able to fill the captcha challenge? =

These people will leave their track on the log file. So check the log file regularly to see 
if there is someone you'd like to bring into your subscribers database. The log will give you 
that person's username, email and date/time when he tried to register and failed. Then 
it's up to you to add that user manually or send him an email with directions for an effective registration. 
The most common problem that keeps good people outside is not due to the reCaptcha, but 
illegal (non ASCII) characters in usernames. That is why we added a help box to the register form.

= Why there are only four style options for the reCAPTCHA box? =

The reCAPTCHA form that NURCaptcha inserts in your site is built up on the fly at Google`s reCAPTCHA server. 
NURCaptcha works, to this moment, with the standard ready-to-use four basic styles. We plan to add 
customization tools on a future version of the plugin.

== Screenshots ==

1. Settings Page
2. This is how the Register Form will look like

== Changelog ==

= 3.0.0 =
* Added: StopForumSpam and BotScout databases assessment for extra security.
= 2.4.7 =
* Added: user customization of reCAPTCHA strings.
* Improved: better UI to choose native language for reCAPTCHA.
= 2.4.6 =
* Added: customized text for submit button on register form.
= 2.4.5 =
* Fixed: excluded ubused help button from BuddyPress and WPMU versions.
= 2.4.4 =
* Added: help on register form; 
* Fixed: logfile deletion drops log count to zero.
= 2.4.3 =
* Fixed: wrong data writing on logfile
= 2.4.2 =
* Added: improvements on javascript.
* Fixed: small glitch on textdomain call
= 2.4 =
* Added: support to BuddyPress.
* Fixed: double counting on WPMU
= 2.0 = 
* Added support to Multisites. 
= 1.3 = 
* Included Log File to show data of blocked attemptives, and a few other minor code improvements.
= 1.13 = 
* Register Form Page upgraded with javascript. No critical changes.
= 1.1 = 
* Admin Page upgraded with javascript. No critical changes.
= 1.0 =
* First version.

== Upgrade Notice ==

= 3.0.0 =
* Added: StopForumSpam and BotScout databases assessment for extra security.
* Alert: we strongly recommend users to upgrade to version 3, as there are some evidences of spammers making a way through reCAPTCHA challenges - probably by tricking someone else to solve it for their bots.
= 2.4.7 =
* Added: user customization of reCAPTCHA strings.
* Improved: better UI to choose native language for reCAPTCHA.
= 2.4.6 =
* Added: customized text for submit button on register form.
= 2.4.5 =
* Fixed: excluded unused help button from BuddyPress and WPMU versions.
= 2.4.4 =
* Added: help on register form; Fixed: logfile deletion drops log count to zero.
= 2.4.3 =
* Fixed: wrong data writing on logfile
= 2.4.2 =
* Fixed: small glitch on textdomain call
= 2.4 =
* Fixed double counting in WPMU. Added BuddyPress support
= 2.0 =
* Added: support to Multisites; settings link (at Plugins List); Improvements on warnings.
= 1.3 =
* Added: Log data.
= 1.13 =
* Added: js shake effect to the error message when form fill fails.
= 1.1 =
* Some Javascript added to admin page. Visual help to change options improved.