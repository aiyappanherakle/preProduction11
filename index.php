<?php
// phpinfo(); exit;

$isNginx = (stristr($_SERVER['SERVER_SOFTWARE'],'nginx') !== False);		// set if nginx server
$isMark = ($_SERVER['SERVER_SOFTWARE'] == 'Microsoft-IIS/7.5' && $_SERVER['APPL_PHYSICAL_PATH'] == 'C:\\GC\\preProduction\\');	// set if Mark devel server

if ($isNginx)							// if nginx server
{
	$uri = $_SERVER['REQUEST_URI'];		// get path and CGI parameters from where expected
	// Sample of a remapped URL, that still contains old school CGI params: CoinPrices/4/Small-Cents?denomination=4&page=6&pp=50
} else if ($isMark)						// if Mark devel server
{
	$uri = $_SERVER['REQUEST_URI'];		// get path and CGI parameters from where expected
	$basename = dirname($_SERVER['SCRIPT_NAME']);
	$uri = str_replace($basename, '', $uri);	// rid of web root prefix 
	// phpinfo(); exit;
	// echo 'uri='.$uri; exit;
} else {		// if apache server
	$uri = @$_REQUEST['p'];				// for apache via our special .htaccess, "p" is set to the uri
}
$uri = preg_replace("!^/+!",'',$uri);	// rid of any preceding slashes
// echo $uri; exit;

if (!$uri)	// if no parameter, go to home
{
	require "main.php";
	exit;
}

$new_list[] = array( 'url'=>'/^kb\/(.+)-t([0-9]+)-([0-9]+)\.html/',  'file'=>'./kb/index.php',   'line'=>__LINE__, 'parameters'=>array('cmd'=>'$3','id'=>'$2') );
$new_list[] = array( 'url'=>'/^kb\/(.+)-([0-9]+)-([0-9]+)\.html$/',   'file'=>'./kb/index.php',   'line'=>__LINE__, 'parameters'=>array('cmd'=>'$3','catid'=>'$2') );
$new_list[] = array( 'url'=>'/^ask-a-question-(.*)\.html$/',  'file'=>'./kb/index.php', 'line'=>__LINE__, 'parameters'=>array('cmd'=>'$1') );
$new_list[] = array( 'url'=>'/^kb\/saved-articles-(.*)\.html$/',  'file'=>'./kb/index.php', 'line'=>__LINE__, 'parameters'=>array('cmd'=>'$1') );
$new_list[] = array( 'url'=>'/^kb\/save-article-t([0-9]+)-([0-9]+)\.html$/',  'file'=>'./kb/index.php',   'line'=>__LINE__, 'parameters'=>array('cmd'=>'$2','id'=>'$1') );
$new_list[] = array( 'url'=>'/^kb\/email-article-t([0-9]+)-([0-9]+)\.html$/', 'file'=>'./kb/index.php',   'line'=>__LINE__, 'parameters'=>array('cmd'=>'$2','id'=>'$1') );
$new_list[] = array( 'url'=>'/^kb\/discuss-article-t([0-9]+)-([0-9]+)\.html$/',   'file'=>'./kb/index.php',   'line'=>__LINE__, 'parameters'=>array('cmd'=>'$2','id'=>'$1') );
$new_list[] = array( 'url'=>'/^kb\/print-article-t([0-9]+)\.html$/',  'file'=>'./kb/printarticle.php', 'line'=>__LINE__, 'parameters'=>array('id'=>'$1') ); 
$new_list[] = array( 'url'=>'/^kb\/save-article-t([0-9]+)-([0-9]+)\.html$/',  'file'=>'./kb/index.php',   'line'=>__LINE__, 'parameters'=>array('cmd'=>'$2','id'=>'$1') );
$new_list[] = array( 'url'=>'/^kb\/search-([0-9]+)\.html$/',  'file'=>'./kb/index.php', 'line'=>__LINE__, 'parameters'=>array('cmd'=>'$1') );
$new_list[] = array( 'url'=>'/^kb\/top-10-viewed\.html$/',    'file'=>'./kb/index.php',    'line'=>__LINE__, 'parameters'=>array('cmd'=>'3','pop'=>'1') );
$new_list[] = array( 'url'=>'/^kb\/top-10-emailed\.html$/',   'file'=>'./kb/index.php',    'line'=>__LINE__, 'parameters'=>array('cmd'=>'3','pop'=>'2') );
$new_list[] = array( 'url'=>'/^kb\/top-10-printed\.html$/',   'file'=>'./kb/index.php',    'line'=>__LINE__, 'parameters'=>array('cmd'=>'3','pop'=>'4') );

$new_list[] = array( 'url'=>'/^image\/([0-9]+)\/([0-9]+)/',
	'file'=>'/image/image.php',
	'line'=>__LINE__ );


$new_list[] = array( 'url'=>'/^([Bb]+)uy$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'buying',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Bb]+)uy\/([Pp]+)roducts$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'buying',
		'mode'=>'product',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)ell$/',
	'line'=>__LINE__,
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'sell',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)ell\/([Bb]+)ulk$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'selling',
		'mode'=>'bulk',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)ell\/([Ss]+)ervices$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'selling',
		'mode'=>'service',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)ignin$/',
	'file'=>'login.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)ignout$/',
	'file'=>'login.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'_logout',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Nn]+)ews$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'news',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)ategories$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'categories',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ll]+)istings$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^mygc$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'cp',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^main-rss$/',
	'file'=>'rss.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^main-converter$/',
	'file'=>'accounting.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'currency-converter',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Dd]+)iscountgrading$/',
	'file'=>'discount_grading.php' );
$new_list[] = array( 'url'=>'/^([Mm]+)ain-shows$/',
	'file'=>'coin_shows.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^main-([A-Za-z0-9\-]+)$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'$1',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Tt]+)([Ee]+)([Rr]+)([Mm]+)([Ss]+)\/$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'terms',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Tt]+)([Ee]+)([Rr]+)([Mm]+)([Ss]+)$/',
	'file'=>'main-terms' );
$new_list[] = array( 'url'=>'/^([Ww]+)([Aa]+)([Nn]+)([Tt]+)([Ll]+)([Ii]+)([Ss]+)([Tt]+)$/',
	'file'=>'coin_wantlist.php' );
$new_list[] = array( 'url'=>'/^([Ww]+)([Aa]+)([Nn]+)([Tt]+)([Ll]+)([Ii]+)([Ss]+)([Tt]+)\/$/',
	'file'=>'coin_wantlist.php' );
$new_list[] = array( 'url'=>'/^([Nn]+)onprofits$/',
	'file'=>'nonprofits.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Gg]+)rading$/',
	'file'=>'main-grading' );
$new_list[] = array( 'url'=>'/^([Ss]+)hippingfees$/',
	'file'=>'kb\/Shipping-Fees-and-Methods-t86-4.html',
	'line'=>__LINE__,
	'parameters'=>array(   'se'=>'Shipping',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)hipping$/',
	'file'=>'kb\/Shipping-Fees-and-Methods-t86-4.html',
	'line'=>__LINE__,
	'parameters'=>array(   'se'=>'Shipping',
	) );
$new_list[] = array( 'url'=>'/^([Tt]+)([Ii]+)([Mm]+)([Ee]+)([Ll]+)([Ii]+)([Nn]+)([Ee]+)$/',
	'file'=>'main-timeline.php' );
$new_list[] = array( 'url'=>'/^([Tt]+)([Ii]+)([Mm]+)([Ee]+)([Ll]+)([Ii]+)([Nn]+)([Ee]+)\/$/',
	'file'=>'main-timeline.php' );
$new_list[] = array( 'url'=>'/^([Aa]+)([Mm]+)([Aa]+)([Dd]+)([Ee]+)([Uu]+)([Ss]+)$/',
	'file'=>'amadeus.php' );
$new_list[] = array( 'url'=>'/^([Aa]+)([Mm]+)([Aa]+)([Dd]+)([Ee]+)([Uu]+)([Ss]+)\/$/',
	'file'=>'amadeus.php' );
$new_list[] = array( 'url'=>'/^([Cc]+)onsign-now$/',
	'file'=>'consignnow.php' );
$new_list[] = array( 'url'=>'/^([Cc]+)([Oo]+)([Nn]+)([Ss]+)([Ii]+)([Gg]+)([Nn]+)\/$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'sell',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)([Oo]+)([Nn]+)([Ss]+)([Ii]+)([Gg]+)([Nn]+)$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'sell',
	) );
$new_list[] = array( 'url'=>'/^newsell_coin.php$/',
	'file'=>'coin_appraisal.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)([Ii]+)([Ll]+)([Vv]+)([Ee]+)([Rr]+)-([Ee])([Aa]+)([Gg]+)([Ll]+)([Ee]+)([Ss]+)$/',
	'file'=>'Series\/89\/1-Silver-Eagles' );
$new_list[] = array( 'url'=>'/^cce.jpg$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)([Ee]+)([Ll]+)([Ll]+)([Ii]+)([Nn]+)([Gg]+)\/$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'sell',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)([Ee]+)([Ll]+)([Ll]+)([Ii]+)([Nn]+)([Gg]+)$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'sell',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)([Ee]+)([Ll]+)([Ll]+)([Cc]+)([Oo]+)([Ii]+)([Nn]+)([Ss]+)$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'sell',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)([Oo]+)([Ii]+)([Nn]+)$/',
	'file'=>'main.php' ,
	'line'=>__LINE__,);
$new_list[] = array( 'url'=>'/^([Cc]+)([Oo]+)([Ii]+)([Nn]+)\/$/',
	'file'=>'main.php',
	'line'=>__LINE__, );
$new_list[] = array( 'url'=>'/^([Cc]+)([Oo]+)([Ii]+)([Nn]+)([Ss]+)$^/',
	'file'=>'main.php' ,
	'line'=>__LINE__,);
$new_list[] = array( 'url'=>'/^([Cc]+)([Oo]+)([Ii]+)([Nn]+)([Ss]+)\/$/',
	'file'=>'main.php' ,
	'line'=>__LINE__,);
$new_list[] = array( 'url'=>'/^([Nn]+)([Gg]+)([Cc]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'q'=>'NGC',
		'mode'=>'product',
		'sort'=>'01',
	) );
$new_list[] = array( 'url'=>'/^([Nn]+)([Gg]+)([Cc]+)-([Cc]+)([Oo]+)([Ii]+)([Nn]+)([Ss]+)$/',
	'file'=>'main-NGC' );
$new_list[] = array( 'url'=>'/^([Nn]+)([Gg]+)([Cc]+)-([Cc]+)([Oo]+)([Ii]+)([Nn]+)([Ss]+)\/$/',
	'file'=>'main-NGC' );
$new_list[] = array( 'url'=>'/^([Ff]+)([Oo]+)([Rr]+)([Mm]+)\/$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'sell',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)([Oo]+)([Rr]+)([Mm]+)$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'sell',
	) );
$new_list[] = array( 'url'=>'/^([Ii]+)ndex.php$/',
	'file'=>'main.php' ,
	'line'=>__LINE__,);
$new_list[] = array( 'url'=>'/^([Ii]+)ndex.html$/',
	'file'=>'main.php' ,
	'line'=>__LINE__,);
$new_list[] = array( 'url'=>'/^([Ii]+)([Nn]+)([Dd]+)([Ee]+)([Xx]+)$/',
	'file'=>'main.php' ,
	'line'=>__LINE__,);
$new_list[] = array( 'url'=>'/^([Pp]+)([Cc]+)([Gg]+)([Ss]+)$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'pcgs-raw',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)([Cc]+)([Gg]+)([Ss]+)\/$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'pcgs-raw',
	) );
$new_list[] = array( 'url'=>'/^([Mm]+)([Aa]+)([Ii]+)([Nn]+)-([Pp]+)([Cc]+)([Gg]+)([Ss]+)-([Rr]+)([Aa]+)([Ww]+)$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'pcgs-raw',
	) );
$new_list[] = array( 'url'=>'/^([Mm]+)([Aa]+)([Ii]+)([Nn]+)-([Pp]+)([Cc]+)([Gg]+)([Ss]+)-([Rr]+)([Aa]+)([Ww]+)\/$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'pcgs-raw',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)([Cc]+)([Gg]+)([Ss]+)-([Cc]+)([Oo]+)([Ii]+)([Nn]+)([Ss]+)$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'pcgs-raw',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)([Cc]+)([Gg]+)([Ss]+)-([Cc]+)([Oo]+)([Ii]+)([Nn]+)([Ss]+)\/$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'pcgs-raw',
	) );
$new_list[] = array( 'url'=>'/^([Bb]+)idding$/',
	'file'=>'denomination.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
	) );
$new_list[] = array( 'url'=>'/^([Bb]+)idding\/$/',
	'file'=>'denomination.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
	) );
$new_list[] = array( 'url'=>'/^([Aa]+)uction$/',
	'file'=>'main.php' );
$new_list[] = array( 'url'=>'/^([Aa]+)uction\/$/',
	'file'=>'main.php' );
$new_list[] = array( 'url'=>'/^([Aa]+)uctions$/',
	'file'=>'main.php' );
$new_list[] = array( 'url'=>'/^([Ll]+)ove$/',
	'file'=>'love_campaign.php' );
$new_list[] = array( 'url'=>'/^([Ll]+)ove\/$/',
	'file'=>'love_campaign.php' );
$new_list[] = array( 'url'=>'/^([Bb]+)id\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'rfp.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'bid',
		'id'=>'$2',
		'state'=>'product',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)oinAuctions$/',
	'file'=>'CoinAuctions.php' ,
	'line'=>__LINE__,);
$new_list[] = array( 'url'=>'/^([Cc]+)oinAuctions\/([A-Za-z0-9_\-]+)$/',
	'file'=>'CoinAuctions.php',
	'line'=>__LINE__, );
$new_list[] = array( 'url'=>'/^([Pp]+)rivacy.php$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'privacy',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)ompany\/contact.php$/',
	'file'=>'main-contact',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)oin_auction_agent\/$/',
	'file'=>'CoinPrices',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Bb]+)ullion_coins\/$/',
	'file'=>'Denominations',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)oin_links.php$/',
	'file'=>'CoinPrices',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)AC-Coins$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'q'=>'CAC',
		'mode'=>'product',
		'sort'=>'01',
	) );
$new_list[] = array( 'url'=>'/^([Aa]+)NACS-Coins$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'q'=>'ANACS',
		'mode'=>'product',
		'sort'=>'01',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)hinese-Coins$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'q'=>'China',
		'mode'=>'product',
		'sort'=>'01',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)-([Rr]+)([Ff]+)([Pp]+)([0-9]+)$/',
	'file'=>'rfp.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$5',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)-([Ii]+)tem([0-9]+)$/',
	'file'=>'merch.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$3',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)roject\/([0-9]+)\/([Rr]+)etracted$/',
	'file'=>'rfp.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'view'=>'retracted',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)roject\/([0-9]+)\/([Dd]+)eclined$/',
	'file'=>'rfp.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'view'=>'declined',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)roject\/([0-9]+)\/([Ss]+)hortlist$/',
	'file'=>'rfp.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'view'=>'shortlist',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)roject\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'rfp.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)roject\/([0-9]+)\/$/',
	'file'=>'rfp.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)oin\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'merch.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)oin\/([0-9]+)$/',
	'file'=>'merch.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)oinnew\/([0-9]+)$/',
	'file'=>'coin.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)ell\/([Cc]+)oin\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'merch.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$3',
		'sef'=>'2',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)oins\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'product',
		'cid'=>'$2',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)eries\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'product',
		'series'=>'$2',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)eries\/([0-9]+)\/([A-Za-z0-9_\-]+)\/([0-9_\-]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'product',
		'series'=>'$2',
		'date_end'=>'$4',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)oinPrices\/SeriesCoin\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'product',
		'series'=>'$2',
		'ended'=>'1',
		'listing_type'=>'4',
		'sold'=>'1',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Dd]+)enomination\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'denomination'=>'$2',
		'dEnom_search'=>'1',
		'mode'=>'product',
		'sort'=>'01',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Dd]+)enominations$/',
	'file'=>'denomination.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
	) );
$new_list[] = array( 'url'=>'/^([Dd]+)enominations\/([0-9_\-]+)$/',
	'file'=>'denomination.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'date_end'=>'$2',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Dd]+)enomination\/([0-9]+)\/([A-Za-z0-9_\-]+)\/([0-9_\-]+)$/',
	'file'=>'denomination.php',
	'line'=>__LINE__,
	'parameters'=>array(   'denomination'=>'$2',
		'date_end'=>'$4',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Dd]+)enominations\/([Dd]+)ailyDeal$/',
	'file'=>'denomination.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'deal'=>'dailydeal',
	) );
$new_list[] = array( 'url'=>'/^([Dd]+)enominations\/([Nn]+)ext$/',
	'file'=>'denomination.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'auction'=>'next',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)oin([Pp]+)rices$/',
	'file'=>'denomination.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'CoinPrices',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)oin([Pp]+)rices\/$/',
	'file'=>'denomination.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'CoinPrices',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)oin([Pp]+)rice$/',
	'file'=>'denomination.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'CoinPrices',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)oin([Pp]+)rice\/$/',
	'file'=>'denomination.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'CoinPrices',
	) );
$new_list[] = array( 'url'=>'/^([Tt]+)est([Pp]+)rices$/',
	'file'=>'denomination_4567.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'CoinPrices',
	) );
$new_list[] = array( 'url'=>'/^([Tt]+)est([Pp]+)rices\/$/',
	'file'=>'denomination_4567.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'CoinPrices',
	) );
$new_list[] = array( 'url'=>'/^([Tt]+)est([Pp]+)rice$/',
	'file'=>'denomination_4567.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'CoinPrices',
	) );
$new_list[] = array( 'url'=>'/^([Tt]+)est([Pp]+)rice\/$/',
	'file'=>'denomination_4567.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'CoinPrices',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)earch-([Ww]+)on$/',
	'file'=>'search_won.php',
	'line'=>__LINE__,
	'parameters'=>array(   'q'=>'',
		'mode'=>'product',
		'sort'=>'01',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)oinPrices\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'denomination.php',
	'line'=>__LINE__,
	'parameters'=>array(   'denomination'=>'$2',
		'ended'=>'1',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Tt]+)estPrices\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'denomination_4567.php',
	'line'=>__LINE__,
	'parameters'=>array(   'denomination'=>'$2',
		'ended'=>'1',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Dd]+)ailyDeal$/',
	'file'=>'24hours.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Dd]+)ailydeal$/',
	'file'=>'24hours.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([24]+)hours$/',
	'file'=>'24hours.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ww]+)atchlist\/([Mm]+)anagement$/',
	'file'=>'watchlist.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'management',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)references\/([Ff]+)avorites$/',
	'file'=>'preferences.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'favorites',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Bb]+)uy\/([Aa]+)ctive$/',
	'file'=>'buy.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'active',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Bb]+)uy\/([Ww]+)on$/',
	'file'=>'buy.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'won',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Bb]+)uy\/([Nn]+)otwon$/',
	'file'=>'buy.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'notwon',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Bb]+)uy\/([Bb]+)uynow$/',
	'file'=>'buy.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'buynow',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Bb]+)uyerinvoice$/',
	'file'=>'buyer_invoice.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Aa]+)ccounting\/([Cc]+)om-transactions$/',
	'file'=>'accounting.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'com-transactions',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Aa]+)ccounting\/([Ss]+)ch-transactions$/',
	'file'=>'accounting.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'sch-transactions',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)ell\/([Cc]+)urrent$/',
	'file'=>'sell.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'current',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)ell\/([Ss]+)old$/',
	'file'=>'sell.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'sold',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)ell\/([Uu]+)nsold$/',
	'file'=>'sell.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'unsold',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)ell\/([Pp]+)ending$/',
	'file'=>'sell.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'pending',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)ell\/([Rr]+)eturned$/',
	'file'=>'sell.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'returned',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)ell\/([Bb]+)uynowsold$/',
	'file'=>'sell.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'buynowsold',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)ell\/([Bb]+)uynowunsold$/',
	'file'=>'sell.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'buynowunsold',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)onsignorstatement$/',
	'file'=>'consignor_statement.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Tt]+)rack$/',
	'file'=>'track.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'track',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)onsignmentsreceived$/',
	'file'=>'track.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'received',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)onsignmentsreturned$/',
	'file'=>'track.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'returned',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)references\/([M]+)anagement$/',
	'file'=>'preferences.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'management',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)references\/([E]+)mail$/',
	'file'=>'preferences.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'email',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)references\/([Pr]+)rofile$/',
	'file'=>'preferences.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'profile',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)references\/([Nn]+)otifications$/',
	'file'=>'preferences.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'notifications',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)references\/([Ll]+)ogin$/',
	'file'=>'preferences.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'login',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)references\/([Pp]+)asswordchange$/',
	'file'=>'preferences.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'password-change',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)-([Pp]+)ortfolio([0-9]+)$/',
	'file'=>'portfolio.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$3',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)-([Ss]+)ervice-([Cc]+)ategory-([Mm]+)ap-([0-9]+)$/',
	'file'=>'rfp.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'cid'=>'$5',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)ategories\/([Pp]+)rojects\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'rfp.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'cid'=>'$3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)ategories\/([Pp]+)rojects\/([0-9]+)\/$/',
	'file'=>'rfp.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'cid'=>'$3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)-([Pp]+)roduct-([Cc]+)ategory-([Mm]+)ap-([0-9]+)$/',
	'file'=>'merch.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'cid'=>'$5',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)ategories\/([Ii]+)tems\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'merch.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'cid'=>'$3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)ategories\/([Ii]+)tems\/([0-9]+)\/$/',
	'file'=>'merch.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'cid'=>'$3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)_([A-Za-z0-9_\-]+)-([Ss]+)ervice-([Cc]+)ategory-([Mm]+)ap-([0-9]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'service',
		'cid'=>'$6',
		'q'=>'$1',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)ategories\/([Pp]+)rojects\/([0-9]+)\/([A-Za-z0-9_\-]+)_([A-Za-z0-9_\-]+)$/',
	'file'=>'rfp.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'cid'=>'$3',
		'q'=>'$4',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)_([A-Za-z0-9_\-]+)-([Pp]+)roduct-([Cc]+)ategory-([Mm]+)ap-([0-9]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'product',
		'cid'=>'$6',
		'q'=>'$1',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)ategories\/([Ii]+)tems\/([0-9]+)\/([A-Za-z0-9_\-]+)_([A-Za-z0-9_\-]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'product',
		'cid'=>'$3',
		'q'=>'$4',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)-([Pp]+)rojects-([0-9]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'service',
		'cid'=>'$3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)rojects\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'service',
		'cid'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)rojects\/([0-9]+)\/$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'service',
		'cid'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)-([Ii]+)tems-([0-9]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'product',
		'cid'=>'$3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ii]+)tems\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'product',
		'cid'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ii]+)tems\/([0-9]+)\/$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'product',
		'cid'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)-([Ee]+)xperts-([0-9]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'experts',
		'cid'=>'$3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ee]+)xperts\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'experts',
		'cid'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ee]+)xperts\/([0-9]+)\/$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'experts',
		'cid'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)-([Ss]+)kills-([0-9]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'experts',
		'sid'=>'$3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)kills\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'experts',
		'sid'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)kills\/([0-9]+)\/$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'experts',
		'sid'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)_([A-Za-z0-9_\-]+)-([Pp]+)rojects-([0-9]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'service',
		'cid'=>'$4',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)rojects\/([0-9]+)\/([A-Za-z0-9_\-]+)_([A-Za-z0-9_\-]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'service',
		'cid'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)_([A-Za-z0-9_\-]+)-([Ii]+)tems-([0-9]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'product',
		'cid'=>'$4',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ii]+)tems\/([0-9]+)\/([A-Za-z0-9_\-]+)_([A-Za-z0-9_\-]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'product',
		'cid'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)_([A-Za-z0-9_\-]+)-([Ee]+)xperts-([0-9]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'experts',
		'cid'=>'$4',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ee]+)xperts\/([0-9]+)\/([A-Za-z0-9_\-]+)_([A-Za-z0-9_\-]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'experts',
		'cid'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)_([A-Za-z0-9_\-]+)-([Ss]+)kills-([0-9]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'experts',
		'sid'=>'$4',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)kills\/([0-9]+)\/([A-Za-z0-9_\-]+)_([A-Za-z0-9_\-]+)$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'experts',
		'sid'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Mm]+)embers\/([A-Za-z0-9_\-]+)-([Ff]+)eedback-([2-4]+)$/',
	'file'=>'members.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'feedback'=>'$4',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Mm]+)embers\/([A-Za-z0-9_\-]+)\/([Pp]+)rofile$/',
	'file'=>'members.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'profile'=>'1',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Mm]+)embers\/([A-Za-z0-9_\-]+)\/([Jj]+)ob-([Hh]+)istory$/',
	'file'=>'members.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'jobhistory'=>'1',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Mm]+)embers\/([A-Za-z0-9_\-]+)$/',
	'file'=>'members.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Aa]+)ll-([Ss]+)ervice-([Cc]+)ategories$/',
	'file'=>'rfp.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Aa]+)ll-([Pp]+)roduct-([Cc]+)ategories$/',
	'file'=>'merch.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)ervice-([Cc]+)ategory-([Mm]+)ap$/',
	'file'=>'rfp.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)ategories\/([Pp]+)rojects$/',
	'file'=>'rfp.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)roduct-([Cc]+)ategory-([Mm]+)ap$/',
	'file'=>'merch.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)ategories\/([Ii]+)tems$/',
	'file'=>'merch.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'listings',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)ortfolios\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'portfolio.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cid'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)_([Pp]+)ortfolios\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'portfolio.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cid'=>'$3',
		'q'=>'$1',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Bb]+)uying$/',
	'file'=>'buying.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Bb]+)uying\/([Mm]+)anagement\/rfps\/([A-Za-z\-]+)$/',
	'file'=>'buying.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'management',
		'sub'=>'$3',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Bb]+)uying\/([Mm]+)anagement\/bids\/([A-Za-z\-]+)$/',
	'file'=>'buying.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'management',
		'bidsub'=>'$3',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)ortfolios$/',
	'file'=>'portfolio.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)earch$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)earch-([Ss]+)ervices$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'tab'=>'0',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)earch-([Pp]+)roducts$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'tab'=>'1',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)earch-([Ee]+)xperts$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'tab'=>'2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)earch-([Oo]+)ptions$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'tab'=>'3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)earch-([Hh]+)elp$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'help',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Rr]+)egister$/',
	'file'=>'registration.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)roviders$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'experts',
		'sort'=>'52',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)ervices$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'service',
		'sort'=>'01',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)roducts$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'mode'=>'product',
		'sort'=>'01',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Dd]+)imevar$/',
	'file'=>'search.php',
	'line'=>__LINE__,
	'parameters'=>array(   'fromyear'=>'',
		'toyear'=>'2015',
		'grade_range_1'=>'1',
		'grade_range_2'=>'70',
		'fromprice'=>'',
		'toprice'=>'',
		'mode'=>'product',
		'sort'=>'01',
		'series'=>'0',
		'q'=>'Roosevelt FS',
		'frombid'=>'1',
		'tobid'=>'500',
	) );
$new_list[] = array( 'url'=>'/^([Ww]+)atchlist$/',
	'file'=>'watchlist.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ww]+)atchlist\/([Aa]+)ctive$/',
	'file'=>'watchlist.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'active',
		'sef'=>'1[L,QSA]',
	) );
$new_list[] = array( 'url'=>'/^([Ww]+)atchlist\/([Rr]+)ecentlyended$/',
	'file'=>'watchlist.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'recently_ended',
		'sef'=>'1[L,QSA]',
	) );
$new_list[] = array( 'url'=>'/^([Ww]+)atchlist\/([Ee]+)nded$/',
	'file'=>'watchlist.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'ended',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Aa]+)ccounting$/',
	'file'=>'accounting.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)references$/',
	'file'=>'preferences.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Mm]+)essages$/',
	'file'=>'messages.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)ampaign$/',
	'file'=>'campaign.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)ampaign\/([Nn]+)ew$/',
	'file'=>'campaign.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'create',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)ampaign\/([Nn]+)ew\/ppc$/',
	'file'=>'campaign.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'create',
		'mode'=>'ppc',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum\/([Ss]+)earch$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'search',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum\/([Ss]+)earch\/$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'search',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum\/([Ss]+)earch\/([Tt]+)oday$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'search',
		'view'=>'today',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum\/([Ss]+)earch\/([Nn]+)ew$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'search',
		'view'=>'new',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum\/([Ss]+)earch\/([Uu]+)pdated$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'search',
		'view'=>'updated',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum\/([Ss]+)earch\/([Uu]+)nanswered$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'search',
		'view'=>'unanswered',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum\/([Mm]+)arkforumsread$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'markforumsread',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum\/([0-9]+)\/([Nn]+)ewtopic$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'newtopic',
		'cid'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum\/([0-9]+)\/([Ll]+)astpost$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'tid'=>'$2',
		'view'=>'lastpost',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum\/([Tt]+)opic\/([0-9]+)\/([Rr]+)eply$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'reply',
		'tid'=>'$3',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum\/([Tt]+)opic\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'tid'=>'$3',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum\/([Tt]+)opic\/([0-9]+)\/$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'tid'=>'$3',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum\/([Cc]+)ategories\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cid'=>'$3',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum\/([Cc]+)ategories\/([0-9]+)\/$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cid'=>'$3',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'fid'=>'$2',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Ff]+)orum\/([0-9]+)\/$/',
	'file'=>'forum.php',
	'line'=>__LINE__,
	'parameters'=>array(   'fid'=>'$2',
		'sef'=>'1',
		'%{QUERY_STRING}'=>'',
	) );
$new_list[] = array( 'url'=>'/^([Ww]+)antads$/',
	'file'=>'wantads.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)-([Ww]+)antad-([0-9]+)$/',
	'file'=>'wantads.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([A-Za-z0-9_\-]+)-([Ww]+)antads-([0-9]+)$/',
	'file'=>'wantads.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cid'=>'$3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ww]+)antads\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'wantads.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ww]+)antads\/([Cc]+)ategories\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'wantads.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cid'=>'$3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ww]+)antads\/([Nn]+)ew$/',
	'file'=>'wantads.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'post',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ww]+)antads\/([Pp]+)ostoffer\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'wantads.php',
	'line'=>__LINE__,
	'parameters'=>array(   'subcmd'=>'postoffer',
		'id'=>'$3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ww]+)antads\/([Pp]+)ostoffer\/([0-9]+)$/',
	'file'=>'wantads.php',
	'line'=>__LINE__,
	'parameters'=>array(   'subcmd'=>'postoffer',
		'id'=>'$3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ww]+)antads\/([Mm]+)yoffers$/',
	'file'=>'wantads.php',
	'line'=>__LINE__,
	'parameters'=>array(   'subcmd'=>'myoffers',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ww]+)antads\/([Rr]+)emove\/([0-9]+)$/',
	'file'=>'wantads.php',
	'line'=>__LINE__,
	'parameters'=>array(   'subcmd'=>'remove-wantad',
		'id'=>'$3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ww]+)antads\/([Rr]+)emove\/([Oo]+)ffer\/([0-9]+)$/',
	'file'=>'wantads.php',
	'line'=>__LINE__,
	'parameters'=>array(   'subcmd'=>'remove-offer',
		'id'=>'$4',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([0-9]+)\/([A-Za-z0-9_\-]+)\/viewcart$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'viewcart',
		'id'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([0-9]+)\/([A-Za-z0-9_\-]+)\/trackorder$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'trackorder',
		'id'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([0-9]+)\/([A-Za-z0-9_\-]+)\/home$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'homepage',
		'id'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([0-9]+)\/([A-Za-z0-9_\-]+)\/about$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'about',
		'id'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([0-9]+)\/([A-Za-z0-9_\-]+)\/paymentmethods$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'methods',
		'id'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([0-9]+)\/([A-Za-z0-9_\-]+)\/contact$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'contact',
		'id'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([0-9]+)\/$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^stores\/([Ii]tem+)\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'viewitem',
		'itemid'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^stores\/([Ii]tem+)\/([0-9]+)\/$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'viewitem',
		'itemid'=>'$2',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Nn]+)ew$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'create-store',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Mm]+)anagement$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'management',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Mm]+)anagement\/([Ii]+)nventory$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'management',
		'subcmd'=>'inventory',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Mm]+)anagement\/([Ii]+)nventory\/([Aa]+)dd$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'management',
		'subcmd'=>'inventory',
		'action'=>'new-item',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Mm]+)anagement\/([Cc]+)ategories$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'management',
		'subcmd'=>'categories',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Mm]+)anagement\/([Oo]+)rders$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'management',
		'subcmd'=>'orders',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Mm]+)anagement\/([Ss]+)hipping$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'management',
		'subcmd'=>'shipping',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Mm]+)anagement\/([Tt]+)axzones$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'management',
		'subcmd'=>'taxzones',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Mm]+)anagement\/([Ss]+)ubscription$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'management',
		'subcmd'=>'upgrade',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Mm]+)anagement\/([Ss]+)ubscription\/([Cc]+)ancel$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'subscription',
		'subcmd'=>'cancel',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Mm]+)anagement\/([Ss]+)ubscription\/([Cc]+)lose$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'subscription',
		'subcmd'=>'close',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Mm]+)anagement\/([Hh]+)omepage$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'management',
		'subcmd'=>'homepage',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Mm]+)anagement\/([Pp]+)aymethods$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'management',
		'subcmd'=>'paymentmethods',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Mm]+)anagement\/([Ee]+)mails$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'management',
		'subcmd'=>'emails',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Mm]+)anagement\/([Ii]+)nventory\/([Uu]+)pdate\/([0-9]+)$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'management',
		'subcmd'=>'inventory',
		'action'=>'update-item',
		'itemid'=>'$5',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([0-9]+)\/([A-Za-z0-9_\-]+)\/([Cc]+)ategories\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'cid'=>'$5sef=1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([0-9]+)\/([A-Za-z0-9_\-]+)\/([Cc]+)ategories\/([0-9]+)\/$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'id'=>'$2',
		'cid'=>'$5sef=1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Cc]+)ategories\/([0-9]+)\/([A-Za-z0-9_\-]+)$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cid'=>'$3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)tores\/([Cc]+)ategories\/([0-9]+)\/$/',
	'file'=>'stores.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cid'=>'$3',
		'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Ww]+)arnings$/',
	'file'=>'warnings.php',
	'line'=>__LINE__,
	'parameters'=>array(   'sef'=>'1',
	) );
$new_list[] = array( 'url'=>'/^([Vv]+)arieties([-]+)([Cc]+)ollection$/',
	'file'=>'collections.php',
	'line'=>__LINE__,
	'parameters'=>array(   'q'=>'',
		'mode'=>'product',
		'sort'=>'01',
		'key_cmd'=>'varieties_flag',
	) );
$new_list[] = array( 'url'=>'/^([Vv]+)([aa]+)([Rr]+)([Ii]+)([Ee]+)([Tt]+)([Ii]+)([Ee]+)([Ss]+)$/',
	'file'=>'collections.php',
	'line'=>__LINE__,
	'parameters'=>array(   'q'=>'',
		'mode'=>'product',
		'sort'=>'01',
		'key_cmd'=>'varieties_flag',
	) );
$new_list[] = array( 'url'=>'/^([Yy]+)oung([-]+)([Cc]+)ollection$/',
	'file'=>'collections.php',
	'line'=>__LINE__,
	'parameters'=>array(   'q'=>'',
		'mode'=>'product',
		'sort'=>'01',
		'listing_type'=>'4',
		'key_cmd'=>'young_collection',
		'grading_service'=>array('ALL'),
		'grade_range_1'=>'1',
		'grade_range_2'=>'70',
		'frombid'=>'0',
		'tobid'=>'500',
	) );
$new_list[] = array( 'url'=>'/^([Yy]+)([Oo]+)([Uu]+)([Nn]+)([Gg]+)$/',
	'file'=>'collections.php',
	'line'=>__LINE__,
	'parameters'=>array(   'q'=>'',
		'mode'=>'product',
		'sort'=>'01',
		'listing_type'=>'4',
		'key_cmd'=>'young_collection',
		'grading_service'=>array('ALL'),
		'grade_range_1'=>'1',
		'grade_range_2'=>'70',
		'frombid'=>'0',
		'tobid'=>'500',
	) );
$new_list[] = array( 'url'=>'/^([Cc]+)ollection$/',
	'file'=>'collections.php',
	'line'=>__LINE__,
	'parameters'=>array(   'q'=>'',
		'mode'=>'product',
		'sort'=>'01',
		'listing_type'=>'4',
		'key_cmd'=>'young_collection',
		'grading_service'=>array('ALL'),
		'grade_range_1'=>'1',
		'grade_range_2'=>'70',
		'frombid'=>'0',
		'tobid'=>'500',
	) );
$new_list[] = array( 'url'=>'/^die([_]+)variety([_]+)auction$/',
	'file'=>'die_variety_auction.php',
	'line'=>__LINE__,
	'parameters'=>array(   'key_cmd'=>'die_variety',
		'mode'=>'product',
		'sort'=>'01',
		'series'=>'0',
	) );
$new_list[] = array( 'url'=>'/^die([_]+)varieties$/',
	'file'=>'die_variety_auction.php',
	'line'=>__LINE__,
	'parameters'=>array(   'key_cmd'=>'die_variety_all',
		'mode'=>'product',
		'sort'=>'01',
		'series'=>'0',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)([Cc]+)([Gg]+)([Ss]+)([-]+)70([-]+)([Cc]+)([Oo]+)([Ii]+)([Nn]+)([Ss]+)$/',
	'file'=>'collections.php',
	'line'=>__LINE__,
	'parameters'=>array(   'q'=>'',
		'mode'=>'product',
		'sort'=>'01',
		'grade_range_1'=>'70',
		'grade_range_2'=>'70',
		'grading_service'=>array('PCGS'),
		'toyear'=>'2013',
	) );
$new_list[] = array( 'url'=>'/^([Nn]+)([Gg]+)([Cc]+)([-]+)70([-]+)([Cc]+)([Oo]+)([Ii]+)([Nn]+)([Ss]+)$/',
	'file'=>'collections.php',
	'line'=>__LINE__,
	'parameters'=>array(   'q'=>'',
		'mode'=>'product',
		'sort'=>'01',
		'grade_range_1'=>'70',
		'grade_range_2'=>'70',
		'grading_service'=>array('NGC'),
		'toyear'=>'2013',
	) );
$new_list[] = array( 'url'=>'/^([Mm]+)([Ss]+)([-]+)67([-]+)([Cc]+)([Oo]+)([Ii]+)([Nn]+)([Ss]+)$/',
	'file'=>'collections.php',
	'line'=>__LINE__,
	'parameters'=>array('q'=>'',
		'mode'=>'product',
		'sort'=>'01',
		'grade_range_1'=>'67',
		'grade_range_2'=>'67',
		'grading_service'=>array('PCGS','NGC'),
		'toyear'=>'2013',
	) );
$new_list[] = array( 'url'=>'/^([Hh]+)([Oo]+)([Tt]+)([Ll]+)([Ii]+)([Ss]+)([Tt]+)$/',
	'file'=>'hotlist.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'hotlist',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)([Pp]+)([Ll]+)([Tt]+)([Oo]+)([Nn]+)([Ee]+)([Dd]+)$/',
	'file'=>'spl_toned.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'spl_toned',
	) );
$new_list[] = array( 'url'=>'/^([Gg]+)reat([Cc]+)ollections([-]+)([Vv]+)([Ss]+)([-]+)([Ee]+)([Bb]+)ay$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'greatcollections-vs-ebay',
	) );
$new_list[] = array( 'url'=>'/^([Ss]+)elling([-]+)([Ii]+)nstructions$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'selling-instructions',
	) );
$new_list[] = array( 'url'=>'/^([Ll]+)([Aa]+)([Rr]+)([Rr]+)([Yy]+)([Kk]+)([Ii]+)([Nn]+)([Gg]+)$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'larryking',
	) );
$new_list[] = array( 'url'=>'/^([Ll]+)([Aa]+)([Rr]+)([Rr]+)([Yy]+)([Kk]+)([Ii]+)([Nn]+)([Gg]+).$/',
	'file'=>'main.php',
	'line'=>__LINE__,
	'parameters'=>array(   'cmd'=>'larryking',
	) );
$new_list[] = array( 'url'=>'/^([Vv]+)([Aa]+)([Mm]+)([Ss]+)$/',
	'file'=>'collections.php',
	'line'=>__LINE__,
	'parameters'=>array(   'key_cmd'=>'VAM',
		'fromyear'=>'',
		'toyear'=>'2013',
		'grade_range_1'=>'1',
		'grade_range_2'=>'70',
		'fromprice'=>'',
		'toprice'=>'',
		'mode'=>'product',
		'sort'=>'01',
		'series'=>'0',
		'q'=>'',
		'denom_all'=>'1',
		'frombid'=>'0',
		'tobid'=>'500',
	) );
$new_list[] = array( 'url'=>'/^([Pp]+)([Aa]+)([Tt]+)([Ee]+)([Rr]+)([Ss]+)([Oo]+)([Nn]+)$/',
	'file'=>'collectionn.php',
	'line'=>__LINE__,
	'parameters'=>array(   'key_cmd'=>'paterson',
		'fromyear'=>'',
		'toyear'=>'2014',
		'grade_range_1'=>'1',
		'grade_range_2'=>'70',
		'frombid'=>'0',
		'tobid'=>'500',
		'q'=>'paterson',
		'denom_all'=>'1',
		'fromprice'=>'',
		'toprice'=>'',
		'mode'=>'product',
		'sort'=>'01',
		'series'=>'0',
	) );
// Auction Archive 2017
$new_list[] = array( 'url'=>'#^Auction-Archive(/([^/]+))?(/([0-9]+)/[^/]+)?(/([0-9]+)/[^/]+)?(/([0-9]+)/[^/]+)?/?$#i',
	'file'=>'auction_archive.php',
	'line'=>__LINE__,
	'parameters'=>array(
		'universe'=>'$2',
		'denomination'=>'$4',
		'coin_series'=>'$6',
		'coin'=>'$8',
	));
// Knowledgebase 2017
$new_list[] = array( 'url'=>'#^Knowledge/?([0-9]*)/?(.*)$#i',
	'file'=>'kb.php',
	'line'=>__LINE__,
	'parameters'=>array(
		'id'=>'$1',
		'description'=>'$2',
	));
$new_list[] = array( 'url'=>'#^Knowledge/search/?(.*)/?$#i',
	'file'=>'kb.php',
	'line'=>__LINE__,
	'parameters'=>array(
		'search'=>'1',
		'query'=>'$1',
	));
// echo '<pre>'; print_r( $new_list); echo '</pre>'; exit;

if (($pos = strpos($uri, '?')) !== False)		// if there are CGI style parameters in the URL
{
	$CGIparams = substr($uri, $pos+1); 			// get just the parameters
	$uri = substr($uri, 0, $pos);				// and remove them from the $uri
	parse_str($CGIparams, $CGIparams);		// convert to array
	// $CGIparams = preg_split("!&!", $CGIparams);	// convert to array
} else
	$CGIparams = array();						// otherwise no CGI parameters

foreach ($new_list as $path)	// for each remapping possibility
{
	if ( preg_match( $path['url'], $uri, $r, PREG_OFFSET_CAPTURE, 0 ) )		// if there's a match
	{
		// echo $path['url']; exit;
		foreach ($path['parameters'] as $key=>$get )						// get the parameters
		{
			if(!is_array($get))
				$_GET[$key]=( strpos( $get, '$' )!==false )?@$r[substr( $get, strpos( $get, '$' )+1 )][0]:$get;		// McB 2017-04-04: added "@" so no warning if parameter not provided
			else
				$_GET[$key]=$get;
					// perform substition and assign to $_GET to be fed into included iLance PHP script
		}
		// echo '<pre>'; print_r($path); echo '</pre>'; exit;

		unset($_GET['%{QUERY_STRING}']);		// McB removed, probable unimplemented Suku CGI parameter logic

		$_GET = array_merge($_GET, $CGIparams);	// add any CGI params that were already in the URL
		// echo '<pre>'; print_r($_GET); echo '</pre>'; echo $path['file']; echo $path['line'];exit;

		require $path['file'];
		exit;	// we're done, since the included script does everything
	}
}
// drop through to error 404

$_GET['cmd']='404';
// $_GET['p']=$_REQUEST['p'];	// seems unused in "main.php" (McB)
require "main.php";
?>
