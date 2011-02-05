=== bSuite ===
Contributors: misterbisson
Donate link: http://MaisonBisson.com/
Tags: cms, content management, tags, stats, statistics, formatting, pages, widgets, related posts, keyword searching, post, posts, page, pages, admin, related content
Requires at least: 2.7.0
Tested up to: 2.8.4
Stable tag: 4.0.7


A suite of tools used to help surface interesting and popular stories as well as improve WordPress' CMS capabilities as an application platform.

== Description ==

bSuite is a set of tools that help surface interesting and popular stories as well as improve WordPress' CMS capabilities and usefulness as an application platform. <a href="http://maisonbisson.com/bsuite/widgets/">Available widgets</a> can show most popular posts, recently commented posts, or related posts (can also add a listof related posts to the bottom of the post content). <a href="http://maisonbisson.com/bsuite/shortcodes/">Added shortcodes</a> help organize content, allowing you to list all sub-pages of the current page or list all headings within the page. Another shortcode will render RSS feeds into a page or post. See <a href="http://wordpress.org/extend/plugins/bsuite/screenshots/">the screenshots</a> for more.

= Stats =

1. Tracks page loads across your WordPress site
1. Works with caching plugins
1. Shows you what's popular, what's growing in popularity, and what's declining
1. Shows you what categories and tags are most popular
1. Tracks the search terms readers use to find your site (and highlights those terms on the page)

= Widgets =

<a href="http://maisonbisson.com/bsuite/widgets/">bSuite widgets</a> include: 

1. Post Loop allows you to build your own, um, post loop, and put it anywhere a widget will fit.
1. Pages replaces WP's built in Pages widget and adds the option to expand the page list to include the parents and immediate children of the page your currently viewing.
1. bSuite Popular Posts
1. bSuite Related Posts
1. bSuite Recently Commented Posts

= Shortcodes =

<a href="http://maisonbisson.com/bsuite/shortcodes/">Built-in shortcodes</a> allow you to:

1. Embed an RSS or Atom feed into a post or page
1. Include the content or excerpt from one post or page in another.
1. Automatically build an index of headings on a page and create a table of contents to it (with links to anchors on the page)
1. Embed a list of pages or sub-pages on a page or post
1. Embed Slideshare items
1. Embed Wufoo forms

= Language Translations =

1. RU translation by FatCow (<a href="http://www.fatcow.com/">www.fatcow.com</a>).

== Installation ==

1. Place the plugin folder in your `wp-content/plugins/` directory and activate it.
1. Set any options you want in Settings -> bSuite.
1. bSuite will begin collecting new stats immediately, but it will take at least a day before you'll see much of interest in Dashboard -> bStat Reports.


== Frequently Asked Questions ==

= Why are my stats different than in Google Analytics or product x? =

Lies, damn lies, and statistics. I have yet to see any two stats gathering mechanisms report the same numbers. The code is GPL'd and I welcome suggestions for improvements.

bStat's primary features are to offer information to your readers about what stories are popular and help illustrate those trends to blog authors. I leave it up to blog administrators to decide if the mechanism bSuite uses to get that data is sufficient to the purpose, though I will add that I run a number of stats applications in addition to bStat (Google Analytics and AWstats).

= Why are my stats different than with the old version? =

Previous versions of bSuite and bStat counted hits using a different mechanism than bSuite 4. The old mechanism simply counted the number of times WordPress generated a page, reporting falsely high results because of search crawlers, or falsely low results because of caching. bSuite 4 uses a javascript to report back that your pages were actually rendered in a browser. This should lead to more accurate counting of your readership, rather than reporting on which of your posts is getting most indexed by web crawlers.

= How do I...? =

Full documentation and usage examples are available at <a href="http://maisonbisson.com/bsuite/">MaisonBisson.com</a>.

== Screenshots ==

1. Search word highlighting is back! Seen here in <a href="http://borkweb.com/">Matthew Batchelder</a>'s post on <a href="http://borkweb.com/story/faster-page-loads-with-image-concatenation">improving page load times with concatenated images</a> (found via a search for <a href="http://www.google.com/search?q=image+concatenation+borkweb">image concatenation</a>).
2. bSuite quick stats.
3. <a href="http://borkweb.com/">Matthew Batchelder</a>'s <a href="http://borkweb.com/story/ajax-templating-and-the-separation-of-layout-and-logic">use of</a> the `[innerindex]` shortcode. <a href="http://maisonbisson.com/bsuite/shortcodes/">Innerindex</a> automatically generates a list of headings in the page, with links to jump to them.
4. <a href="http://www.plymouth.edu/library/">Lamson Library</a>'s <a href="http://www.plymouth.edu/library/by-subject/art-history">use of</a> the `[list_pages]` shortcode. <a href="http://maisonbisson.com/bsuite/shortcodes/">list_pages</a> automatically generates a list of child pages for a given page.
5. The sharethis feature, as implemented at <a href="http://library.plymouth.edu/read/222334">Lamson Library</a>.
6. <a href="http://spiralbound.net/">Cliff Pearson</a>'s page loads report.
7. <a href="http://spiralbound.net/">Cliff Pearson</a>'s use of the bSuite bStat popular posts widget.
8. <a href="http://spiralbound.net/">Cliff Pearson</a>'s use of the bSuite Recently Commented widget. This widget differs from the built in Recent Comments widget in that it only shows posts, not individual comments.
9. The <a href="http://maisonbisson.com/bsuite/machine-tags">machine tag</a> input field in the edit post and edit page screen. 
10. The bSuite options panel.
