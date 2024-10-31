=== Search Results Optimizer ===
Contributors: chrisgorvan
Tags: search, results, optimizer, optimize, optimiser, optimise, admin, visibility, relevance, relevant, highlight, filter
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ZP7MH98QJRD9A
Requires at least: 3.5
Tested up to: 3.8.1
Stable tag: 1.0.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Search Results Optimizer learns which results users find useful and automatically prioritizes them in future searches.

== Description ==

Change the way your WordPress website prioritizes search results, letting your users automatically train your website, teaching it which results are the most relevant for each search.

= Features =

= Result Pinning =
For each search you can set one result to be pinned to the top of the results list, so that regardless of result popularity or custom ordering, that result will be the first one displayed.

= Search Words Highlighting =
Choose a highlight colour and highlight the words users have searched for on the search results page. If a highlighted word does not appear in the excerpt it will attempt to create a better excerpt with the first searched word.

= Custom Result Ordering =
As well as ordering the first five results by popularity, you can choose to have results ordered by either:

* Date created
* Date modified
* Title

= Advanced Search Form and Result Filtering =
Display an advanced search form on the search results page to let users filter results by post type, post categories and tags.

= Related Searches =
If the current search has zero results you can display links to searches that look similar, which other users have tried.

== Installation ==

= Minimum Requirements =

* WordPress 3.5 or greater
* PHP version 5.3.0 or greater
* MySQL version 5.0 or greater

= Automatic installation =

The easiest way to install Search Results Optimizer is to log into your WordPress dashboard, go to the Plugins page and click "Add New". In the search field type "Search Results Optimizer" and click the search button. Once you've found the plugin click "Install Now".

= Manual installation =

To install Search Results Optimizer manually you need to download the newest version and transfer it to your website via FTP. For more information on this see the WordPress guide to [Managing Plugins](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

If on the off-chance you do encounter issues with the shop/category pages after an update you simply need to flush the permalinks by going to WordPress > Settings > Permalinks and hitting 'save'. That should return things to normal.

= Activation =

Activate the plugin through the 'Plugins' menu in the WordPress dashboard.

== Frequently Asked Questions ==

= How do I add a search? =
To add a search you just need to search your site like a user would. You'll then find your new search in the dashboard.

= What are similar searches? =
When a search is stored it has some common filler words removed, so to Search Results Optimizer "example" looks the same as "another example".

= What is search result popularity? =
It's the click-through rate for a specific search, calculated by dividing the number of times a result has been clicked by the number of times the result has been displayed.

= What are themes? =
It's a way to manually group related searches and visualize result popularity across various search phrases.

= Can I contribute code? =
Yes, you can submit a pull request via [GitHub](http://github.com/chrisgorvan/search-results-optimizer/)

== Screenshots ==

1. The search results stats page.
2. A top result can be pinned so it is always listed first.
3. The search theme results page.
4. The advanced search form without filters, search term highlighted.
5. You can change the highlight color.
6. The advanced search form with all filters.
7. Quick summary stats for the week.

== Changelog ==

= 1.0.3 - 13/04/2014 =
* Tweak - Restricted query logging to "/" to prevent predictive searches from being logged
= 1.0.2 - 12/04/2014 =
* Fix - Reverted to default sort order on non-searches
= 1.0.1 - 10/04/2014 =
* Tweak - Added unicode support for international character sets

== Upgrade Notice ==

= 1.0.3 =
Filters out predictive searches from being logged