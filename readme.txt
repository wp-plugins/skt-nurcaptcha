=== Skt NURCaptha ===
Contributors: Sanskritforum
Donate link: http://skt-nurcaptcha.sanskritstore.com/donate/
Tags: security, login form, new user, captcha, spambots, reCAPTCHA, register
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 1.13

NURCaptcha inserts a reCAPTCHA on the Register Form of your site to protect it against spambots. 

== Description ==

NURCaptcha stands for *New User Register Captcha*. It has been designed for anyone who wishes to allow 
free registration of new users for a WP site, but want to keep bots outside. It uses Google's reCAPTCHA 
tools to give your site an extra protection against those spammer bots, adding security to the WP Register Form.

Skt NURCaptcha is easy to install and doesn't slow down your site. It is called into action 
just at the moment the "Register for This Site" form is requested. It creates a challenge and 
checks the response given. If it is not valid, new user registration fails. If the response is 
valid, NURCaptcha leaves the scene and your site runs as if it was not there.

NURCaptcha also counts each blocked attemptive. To this moment it just displays how many attemptives 
have been blocked by the plugin's action. We plan to add a log archive so you can check out usernames 
and e-mail addresses of those who failed registration and were kept outside. Please note that attemptives in 
which the spambot suspends its attack when confronted by the reCAPTCHA challenge are not accounted.

== Installation ==

1. Upload 'skt-nurcaptcha' folder to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Don't forget to get the free Public and Private reCAPTCHA keys at [reCAPTCHA API Signup Page](https://www.google.com/recaptcha/admin/create "Get your free keys")
1. Go to the 'Settings' menu, locate Skt NURCaptcha Settings Page and drop your keys there.
1. That's it.

== Frequently Asked Questions ==

= The number of blocked attemptives is growing too slowly. Does that mean that the plugin is not working?

If you was facing a greater number of spambot registrations than the figures you see now in the counter, that means 
the plugin is doing its job perfectly. Many of the spambots give up their attack when confronted by the reCaptcha challenge, 
so they come and go silently. These bots may not trigger the counter, but they will not get inside your site walls, either.

= Does NURCaptcha blocks spambots only? =

That is the main target of any captcha plugin: to catch bots and allow human beings to pass by. So it 
wont prevent you against badly mooded human intruders, as they can read and respond perfectly the 
challenge presented by the plugin.

= Why there are only four style options for the reCAPTCHA box? =

The reCAPTCHA form that NURCaptcha inserts in your site is built up on the fly at Google`s reCAPTCHA server. 
NURCaptcha works, to this moment, with the standard ready-to-use four basic styles. We plan to add 
customization tools on a future version of the plugin.

== Screenshots ==

1. Settings Page
2. This is how the Register Form will look like

== Changelog ==

= 1.13 = 
* Register Form Page upgraded with javascript. No critical changes.
= 1.1 = 
* Admin Page upgraded with javascript. No critical changes.
= 1.0 =
* First version.

== Upgrade Notice ==

= 1.13 =
* Added js shake effect to the error message when form fill fails.
= 1.1 =
* Some Javascript added to admin page. Visual help to change options improved.

