<?php

/*  inc_social.php

	This file defines the social bookmarking and sharing services used by bSuite


	Copyright 2004 - 2008  Casey Bisson

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

$services_feed = array(
	'bloglines' => array(
		'name' => 'Bloglines'
		, 'url' => 'http://www.bloglines.com/sub/{url_raw}'
	)
	, 'google' => array(
		'name' => 'Google'
		, 'url' => 'http://fusion.google.com/add?feedurl={url}'
	)
	, 'rssfwd' => array(
		'name' => 'RSS:FWD Email'
		, 'url' => 'http://www.rssfwd.com/rssfwd/preview?url={url}'
	)
);

$services_translate = array(
	'french' => array(
		'name' => 'French'
		, 'url' => 'http://translate.google.com/translate?u={url}&langpair=en%7Cfr'
	)
	, 'spanish' => array(
		'name' => 'Spanish'
		, 'url' => 'http://translate.google.com/translate?u={url}&langpair=en%7Ces'
	)
	, 'german' => array(
		'name' => 'German'
		, 'url' => 'http://translate.google.com/translate?u={url}&langpair=en%7Cde'
	)
	, 'japanese' => array(
		'name' => 'Japanese'
		, 'url' => 'http://translate.google.com/translate?u={url}&langpair=en%7Cja'
	)
	, 'korean' => array(
		'name' => 'Korean'
		, 'url' => 'http://translate.google.com/translate?u={url}&langpair=en%7Cko'
	)
	, 'chineses' => array(
		'name' => 'Chinese (simplified)'
		, 'url' => 'http://translate.google.com/translate?u={url}&langpair=en%7Czh-CN'
	)
	, 'chineset' => array(
		'name' => 'Chinese (traditional)'
		, 'url' => 'http://translate.google.com/translate?u={url}&langpair=en%7Czh-TW'
	)
	, 'russian' => array(
		'name' => 'Russian'
		, 'url' => 'http://translate.google.com/translate?u={url}&langpair=en%7Cru'
	)
);

$services_bookmark = array(
	'delicious' => array(
		'name' => 'del.icio.us'
		, 'url' => 'http://del.icio.us/post?url={url}&title={title}'
	)
	, 'facebook' => array(
		'name' => 'Facebook'
		, 'url' => 'http://www.facebook.com/share.php?u={url}'
	)
	, 'digg' => array(
		'name' => 'Digg'
		, 'url' => 'http://digg.com/submit?phase=2&url={url}&title={title}'
	)
	, 'stumbleupon' => array(
		'name' => 'StumbleUpon'
		, 'url' => 'http://www.stumbleupon.com/submit?url={url}&title={title}'
	)
	, 'reddit' => array(
		'name' => 'reddit'
		, 'url' => 'http://reddit.com/submit?url={url}&title={title}'
	)
	, 'blinklist' => array(
		'name' => 'BlinkList'
		, 'url' => 'http://blinklist.com/index.php?Action=Blink/addblink.php&Url={url}&Title={title}'
	)
	, 'newsvine' => array(
		'name' => 'Newsvine'
		, 'url' => 'http://www.newsvine.com/_tools/seed&save?popoff=0&u={url}&h={title}'
	)
	, 'furl' => array(
		'name' => 'Furl'
		, 'url' => 'http://furl.net/storeIt.jsp?u={url}&t={title}'
	)
	, 'tailrank' => array(
		'name' => 'Tailrank'
		, 'url' => 'http://tailrank.com/share/?link_href={url}&title={title}'
	)
	, 'magnolia' => array(
		'name' => 'Ma.gnolia'
		, 'url' => 'http://ma.gnolia.com/bookmarklet/add?url={url}&title={title}'
	)
	, 'netscape' => array(
		'name' => 'Netscape'
		, 'url' => ' http://www.netscape.com/submit/?U={url}&T={title}'
	)
	, 'yahoo_myweb' => array(
		'name' => 'Yahoo! My Web'
		, 'url' => 'http://myweb2.search.yahoo.com/myresults/bookmarklet?u={url}&t={title}'
	)
	, 'google_bmarks' => array(
		'name' => 'Google Bookmarks'
		, 'url' => '  http://www.google.com/bookmarks/mark?op=edit&bkmk={url}&title={title}'
	)
	, 'technorati' => array(
		'name' => 'Technorati'
		, 'url' => 'http://www.technorati.com/faves?add={url}'
	)
	, 'blinklist' => array(
		'name' => 'BlinkList'
		, 'url' => 'http://blinklist.com/index.php?Action=Blink/addblink.php&Url={url}&Title={title}'
	)
	, 'windows_live' => array(
		'name' => 'Windows Live'
		, 'url' => 'https://favorites.live.com/quickadd.aspx?marklet=1&mkt=en-us&url={url}&title={title}&top=1'
	)
);
?>