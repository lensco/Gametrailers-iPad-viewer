<?php
include('simple_html_dom.php');

// let's get started
$site   = 'http://www.gametrailers.com/index_ajaxfuncs.php?do=get_movie_page&type=newest&page=';
$page   = (isset($_GET['page']) ? $_GET['page'] : '1');
$output = '';


// Whaddayawant? A video or just the index?
if (isset($_GET['video']))
{
	getVideo($_GET['video']);
}
else
{
	getIndex($page);
}


function getIndex($page)
{
	global $site, $output;
	
	// get the GT index page
	$html = new simple_html_dom();
	$html->load_file($site . $page);
		
	// get list of trailers
	$items = $html->find('.newestlist_content table');
	
	// loop over trailers
	foreach ($items as $item)
	{
		// get link to trailer page
		if (!($target = $item->find('.newestlist_movie_format_SDHD a', 0)))
		{
			$target = $item->find('.newestlist_movie_format_SD a', 0);
		}
		$target = $target->href;
		
		// disregard flash video's (these URLs don't start with '/video/')
		if (substr($target, 0, strlen('/video/')) === '/video/')
		{
			// get trailer thumb, game, title, …
			$thumb = $item->find('.newestlist_thumb img', 0);
			$game  = str_replace('\\', '', $item->find('.newestlist_title', 0)->plaintext);
			$title = $item->find('.newestlist_subtitle', 0)->plaintext;
			
			// a little more work to get the description
			$text = $item->find('.newestlist_text', 0);
			$text = str_replace(' -  ', '', $text->find('text', 1));
			$text = str_replace('\\', '', $text);
			
			// platform tags need some juggling too
			$platform_list = "";
			$platforms = $item->find('.newestlist_platimage img');
			foreach ($platforms as $platform)
			{
				$platform = str_replace('http://gametrailers.mtvnimages.com/images/sitewide/plat_', '', str_replace('_default.gif', '', $platform->src));
				$platform_list .= '<span>' . $platform . '</span> ';
			}
			$platform_list = (($platform_list == '<span>na</span> ') ? '' : $platform_list);
			
			// add everything to output
			$output .= "\t\n" . '<li><a href="javascript:this.location = \'?video=' . $target . '\'">' . $thumb . '<h1>' . $game . '</h1><h2>' . $title . '</h2><p>' . $text . '</p><p class="platforms">' . $platform_list . '</p></a></li>';
		}
	}
		
	// clean up memory
	$html->clear(); 
	unset($html);
}


function getVideo($video)
{
	// get html for this trailer page
	$html = new simple_html_dom();
	$html->load_file('http://www.gametrailers.com' . $video);
	
	// extract video file link from trailer page
	$trailerlinks = $html->find("#MediaDownload a", 0);

	// clean up memory
	$html->clear();
	unset($html);

	// redirect to the video file
	header('Location: http://www.gametrailers.com' . $trailerlinks->href);
}

?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">	
	<title>Gametrailers</title>
	<link href="style.css" rel="stylesheet">
	<meta name="viewport" content="width=1024">
	<meta name="apple-mobile-web-app-capable" content="yes">
</head>
<body>
<nav>
	<?php if (intval($page) > 1) { ?><a class="previous" href="javascript:this.location = '?page=<?php echo (intval($page) - 1); ?>'">◀</a><?php } ?>
	<a href="./">Gametrailers</a> <span><?php echo $page; ?>/15</span>
	<?php if (intval($page) < 15) { ?><a class="next" href="javascript:this.location = '?page=<?php echo (intval($page) + 1); ?>'">▶</a><?php } ?>
</nav>
<ul>
	<?php echo $output; ?>
</ul>
</body>
</html>