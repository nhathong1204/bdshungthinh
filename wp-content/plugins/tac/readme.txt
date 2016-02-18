=== Theme Authenticity Checker (TAC) ===
Contributors: builtBackwards, blobaugh
Donate link: http://builtbackwards.com/donate
Tags: themes, security, javascript, admin, theme authentication, verification
Requires at least: 3.0
Tested up to: 3.9.2
Stable tag: trunk

*Scan all of your theme files for potentially malicious or unwanted code.*

== Description ==
Scan all of your theme files for potentially malicious or unwanted code.

**What TAC Does**

TAC stands for Theme Authenticity Checker. TAC searches the source files of every installed theme for signs of malicious code. If such code is found, TAC displays the path to the theme file, the line number, and a small snippet of the suspect code. As of **v1.3** *TAC* also searches for and displays static links.

Then what do you do? Just because the code is there doesn't mean it's not supposed to be or even qualifies as a threat, but most theme authors don't include code outside of the WordPress scope and have no reason to obfuscate the code they make freely available to the web. We recommend contacting the theme author with the code that the script finds, as well as where you downloaded the theme. 
The real value of this plugin is that you can quickly determine where code cleanup is needed in order to safely enjoy your theme.

**History**

TAC got its start when we repeatedly found obfuscated malicious code in free WordPress themes available throughout the web. A quick way to scan a theme for undesirable code was needed, so we put together this plugin.

After Googling and exploring on our own we came upon the [article by Derek](http://5thirtyone.com/archives/870 "article by Derek") from 5thiryOne regarding this very subject. The deal is that many 3rd party websites are providing free WordPress themes with encoded script slipped in - some even going as far as to claim that decoding the gibberish constitutes breaking copyright law. The encoded script may contain a variety of undesirable payloads, such as promoting third party sites or even hijack attempts.


== Installation ==

After downloading and extracting the latest version of TAC:

1. Upload `tac.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Appearance -> TAC in the WordPress Admin
4. The results of the scan will be displayed for each theme with the filename and line number of any threats.
5. You can click on the path to the theme file to edit in the WordPress Theme Editor

== Changelog ==

**Version 1.5.2**
* Compatible with WP 3.9.3

**Version 1.5.1**
* Compatible with 3.8

**Version 1.5**

* Compatible with 3.4
* Updated deprecated function calls to current
* Updated visible display names for sanity
* Capitalized all 'P's in WordPress :)
* Fixed PHP warning messages from uninitiated variables


**Version 1.4.1**

* Compatible with WordPress 2.9
* Added alt tags to theme screenshots

**Version 1.4**

* Compatible with WordPress 2.8!
* Tested in Firefox 3.0.11 and Internet Explorer 8
* JavaScript hiding/showing of theme details

**Version 1.3 (Fixes + New Feature)**

* Changed title to "Theme Authenticity Checker", same acronym, makes more sense
* Compatible with WordPress 2.2 - 2.6.1
* NEW! Checks for embedded Static Links
* NEW! Direct links for editing suspicious files in the WordPress Theme Editor
* Improved the CSS
* Uses its own function to get theme file paths

**Version 1.2 (Fixes)**

* Band-aid fixes to theme file paths that were altered by the update to get_themes() in WordPress 2.6
* This release is only compatible with WordPress 2.6

**Version 1.1 (Fixes)**

* Style sheet doesn't explode any more when certain threats are detected
* Modified code snippet output to prevent interfering with page structure
* Improved styling for slightly more appealing output

**Version 1.0 (First Release)**

* This is the initial release of TAC.



== Frequently Asked Questions ==

= What if I find something? =

Contact the theme's original author to double check if that section of code is supposed to be in the theme in the first place - chances are it shouldn't as there isn't a logical reason have obfuscated code in a theme.

If something is malicious or simply unwanted, *TAC* tells you what file to edit, you can even just click on the file path to be taken straight to the WordPress Theme Editor.

= Why does TAC list static links? =

First of all, static links aren't necessarily bad, *TAC* just lists them so you can quickly see where your theme is linking to.

= What about future vulnerabilities? =

As we find them we will add them to *TAC*. If you find one, PLEASE let us know:
[Contact builtBackwards](http://builtbackwards.com/contact/ "Contact builtBackwards") or post in the [WordPress.org Forum](http://wordpress.org/tags/tac "WordPress.org Forum")

== Screenshots ==

1. TAC Report Page

= Closing Thoughts =

Do your part by developing clean GPL compatible themes!

*builtBackwards*
