<?php
if (!defined('ELK')) 
	die('Hacking attempt...');

function video_bbcode(&$codes, &$no_autolink_tags, &$itemcodes)
{
	global $modSettings;

	// Only for when bbc is on
	if (empty($modSettings['enableBBC']))
		return;

	// Make sure the admin has not disabled the move tag
	if (!empty($modSettings['disabledBBC']))
	{
		foreach (explode(',', $modSettings['disabledBBC']) as $tag)
		{
			if ($tag === 'video')
				return;
		}
	}
	
	$codes[] = array(
		'tag' => 'video',
		'type' => 'unparsed_content',
		'content' => '$1',
		'validate' => function (&$tag, &$data, &$disabled)
		{
			// from http://stackoverflow.com/a/17030234
			if (preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([\w-]{6,16})/", $data, $matches))
			{
				$youtube_embed_url = '//www.youtube.com/embed/' . $matches[1];
				$youtube_link_url = 'http://www.youtube.com/watch?v=' . $matches[1];
				
				// There are two types of YouTube timestamped links
				// http://youtu.be/lLOE3fBZcUU?t=1m37s when you click share underneath the video
				// http://youtu.be/lLOE3fBZcUU?t=97 when you right click on a video and choose "Copy video URL at current time"
				// For embedding, you need to use "?start=97" instead, so we have to convert t=1m37s to seconds while also supporting t=97
				if (preg_match("/t=(?:(?P<hours>[1-9]{1,2})h)?(?:(?P<minutes>[1-9]{1,2})m)?(?:(?P<seconds>[1-9]+)s?)/", $data, $video_start_at))
				{
					$video_start_at_seconds = 0;
					
					$video_start_at_seconds += $video_start_at['hours'] * 3600;
					$video_start_at_seconds += $video_start_at['minutes'] * 60;
					$video_start_at_seconds += $video_start_at['seconds'];
					
					$youtube_embed_url .= '?start=' . $video_start_at_seconds;
					$youtube_link_url .= '?start=' . $video_start_at_seconds;
				}
				
				$data = '<iframe width="560" height="315" src="' . $youtube_embed_url . '" frameborder="0" allowfullscreen><a href="' . $youtube_link_url . '" target="_blank" class="new_win">' . $youtube_link_url . '</a></iframe>';
			}
			// partially adapted from http://stackoverflow.com/a/13286930
			else if (preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?vimeo.com\/(?:channels\/|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/", $data, $matches))
			{
				$data = '<iframe src="//player.vimeo.com/video/' . $matches[3] . '" width="560" height="315" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
			}
			else
			{
				$tag['content'] = '$1';
			}
		},
		'disabled_content' => '<a href="$1" target="_blank" class="new_win">$1</a>',
	);

	$no_autolink_tags[] = 'video';
}

function video_bbcode_button(&$buttons)
{
	$buttons[count($buttons) - 1][] = array(
		'image' => 'video',
		'code' => 'video',
		'description' => 'Paste a YouTube or Vimeo URL',
		'before' => '[video]',
		'after' => '[/video]',
	);
}


?>
