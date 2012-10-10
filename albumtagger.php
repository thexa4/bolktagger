<?php
include_once('acoustid.class.php');
include_once('fingerprint.class.php');
include_once('tagger.class.php');
include_once('settings.class.php');

print "Bolk Album Tagger\n";

process('');

function process($folder)
{
	$dir = scandir(Settings::AlbumQueuePath . $folder);
	foreach($dir as $entry)
	{
		if($entry == '.' | $entry == '..')
			continue;

		if(is_dir(Settings::AlbumQueuePath . $folder . $entry . '/'))
		{
			process($folder . $entry . '/');
			rmdir(Settings::AlbumQueuePath . $folder . $entry . '/');
		}
		else
		{
			$file = $folder . $entry;

			$data = new Fingerprint(Settings::AlbumQueuePath . $file);
			if($data->acoustid == false)
			{
				unlink(Settings::AlbumQueuePath . $file);
				print $file . ": fingerprint failed\n";
				continue;
			}
			$tags = @Acoustid::GetMetadata($data);

			if(empty($tags['artist']))
			{
				@mkdir(Settings::UntaggablePath . $folder, 0755, true);
				rename(Settings::AlbumQueuePath . $file, Settings::UntaggablePath . $file);
				print $file . ": unrecognised number\n";
				continue;
			}

			//fingerprinting successfull
			Tagger::Process(Settings::AlbumQueuePath . $file, $tags['artist'], $tags['album'], $tags['title'], $tags['mbid']);
			print $file . ": done\n";

			usleep(300);
		}
	}
}
?>
