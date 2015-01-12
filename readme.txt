=== Advanced AJAX Product Filters ===
Plugin Name: Advanced AJAX Product Filters
Contributors: dholovnia, berocket
Donate link: http://berocket.com
Tags: filters, product filters, ajax product filters, advanced product filters, woocommerce filters, woocommerce product filters, woocommerce ajax product filters
Requires at least: 3.9
Tested up to: 4.1
Stable tag: 1.0.3.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WooCommerce AJAX Filters - advanced AJAX product filters plugin for WooCommerce.

== Description ==

WooCommerce AJAX Filters - advanced AJAX product filters plugin for WooCommerce. Add unlimited filters with one widget.

= Features: =

* No reloading, only ajax
* Slider, radio or checkbox
* No spamming with widgets in admin, 1 widget - multiple instances
* Filter visibility by product category. Different categories pages = different ( + global ) filters. One shop for everything
* Filter box height limit with scroll themes
* Working great with custom widget area
* Unlimited filters by product attributes



= How It Works: =

= Step 1: =
* First you need to add attributes to the products ( WooCommerce plugin should be installed and activated already )
* Go to Admin area -> Products -> Attributes and add attributes your products will have, add them all
* Click attribute's name where type is select and add values to it. Predefine product options
* Go to your products and add attributes to each of them

= Step 2: =
* Install and activate plugin
* Go to Admin area -> Appearance -> Widgets
* In Available Widgets ( left side of the screen ) find AJAX Product Filters
* Drag it to Sidebar you choose for it
* Enter title, choose attribute that will be used for filtering products, choose filter type,
 choose operator( whether product should have all selected values (AND) or one of them (OR) ),
* Click save and go to your shop to check how it work.
* That's it =)




= Advanced Settings (Widget area): =

* Product Category - if you want to pin your filter to category of the product this is good place to do it.
 Eg. You selling Phones and Cases for them. If user choose Category "Phones" filter "Have Wi-Fi" will appear
 but if user will choose "Cases" it will not be there as Admin set that "Have Wi-Fi" filter will be visible only on
 "Phones" category.
* Filter Box Height - if your filter have too much options it is nice to limit height of the filter to not prolong
 the page too much. Scroll will appear.
* Scroll theme - if "Filter Box Height" is set and box length is more than "Filter Box Height" scroll appear and
 how it looks depends on the theme you choose.




= Next Release ( 1.1.0 ) =

= 1.0.3.5 =
* Enhancement/Fix - Option to set selector for products holder is added. Now you control where data will returned to

= Features: =
* New type Image. Admin can upload image to select and deselect before/instead of text
* Option to add icons before/after option text
* Option to take control over (default) sorting function, make it AJAXy and work with filters
* Add filters and actions for other developers to make it easier extending plugin. Please don't edit plugin directly
  I'm updating it almost daily =)


== Installation ==

1. Install WooCommerce AJAX Filters either via the WordPress.org plugin directory, or by uploading the files to your server
2. Activating WooCommerce AJAX Filters and you're ready to go!


== Frequently Asked Questions ==

---

== Screenshots ==

---

== Changelog ==

= 1.0.3.3 =
* Enhancement/Fix - Showing products and options now depending on woocommerce_hide_out_of_stock_items option
* Enhancement/Fix - If not enough data available( quantity of options < 2 ) filters will not be shown.
* Fix - If in category, only products/options from this category will be shown

= 1.0.3.2 =
* Fix - wrong path was committed in previous version that killed plugin

= 1.0.3 =
* Enhancement - CSS and JavaScript files minimized
* Enhancement - Settings page added
* Enhancement - "No Products" message and it's class can be changed through admin
* Enhancement - Option added that can enable control over sorting( if visible )
* Enhancement - User can select several categories instead of one. Now you don't need to create several same filters
  for different categories.
* Enhancement - Added option "include subcats?". if selected filter will be shown in selected categories and their
  subcategories
* Fix - Adding support to themes that require product div to have "product" class
* Fix - Slider in categories wasn't initialized
* Fix - Subcategories wasn't working. Only Main categories were showing filters
* Templating - return woocommerce/theme default structure for product
* Templating - html parts moved to separate files in templates folder. You can overwrite them by creating folder
  "woocommerce-filters" and file with same name as in plugin templates folder.

= 1.0.2 =
* Fix - better support for older PHP versions

= 1.0.1 =
* First public version