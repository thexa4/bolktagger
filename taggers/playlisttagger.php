<?php
include_once('../classes/acoustid.class.php');
include_once('../classes/fingerprint.class.php');
include_once('../classes/tagger.class.php');

print "Bolk Playlist Tagger\n";

$input = '/pub/mp3/Queue/Playlists/';
$output = '/pub/mp3/Playlists/';

process('');

function process($folder)
{
	global $input, $output;

	$dir = scandir($input . $folder);
	foreach($dir as $entry)
	{
		if($entry == '.' | $entry == '..')
			continue;

		if(is_dir($input . $folder . $entry . '/'))
			process($folder . $entry . '/');
		else
		{
			$file = $folder . $entry;
			print $file . "\n";

			$data = new Fingerprint($input . $file);
			if($data->acoustid == false)
			{
				unlink($input . $file);
				print "unreadable, unlink\n";
				continue;
			}
			$tags = @Acoustid::GetMetadata($data);

			$path = pathinfo($output . $file)['dirname'];
			if(!is_dir($path))
				mkdir($path, 0775, true);

			if(!empty($tags['artist']))
			{
				//fingerprinting successfull
				if(is_file(Tagger::GetFilename($input . $file, $tags['artist'], $tags['album'] , $tags['title'])))
				{
					//already exists
					Tagger::Tag($input . $file, $tags['artist'], $tags['album'], $tags['title'], $tags['mbid'], $tags['albummbid'], $tags['artistmbid']);
					$location = Tagger::GetFilename($input . $file, $tags['artist'], $tags['album'] , $tags['title']);
					$old = md5_file($location);
					$new = md5_file($input . $file);
					if($old == $new)
					{
						//same file
						exec('ln -s -n ' . escapeshellarg($location) . ' ' . escapeshellarg($output . $file));
						unlink($input . $file);
					} else {
						//different versionA
						rename($input . $file, $output . $file);
					}
				} else {
					//does not exist, copy
					$location = Tagger::Process($input . $file, $tags['artist'], $tags['album'], $tags['title'], $tags['mbid'], $tags['albummbid'], $tags['artistmbid']);
					exec('ln -s -n ' . escapeshellarg($location) . ' ' . escapeshellarg($output . $file));
				}
			} else {
				//no idea what this is, move
				rename($input . $file, $output . $file);
			}

			usleep(300);
		}
	}
}
?>
