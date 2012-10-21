<?php
include_once('settings.php');

print "Bolk Album Tagger\n";
Utils::EnsureOnlyRunning();

if(!is_dir(Settings::AlbumQueuePath))
    mkdir(Settings::AlbumQueuePath, 0777, true);
process('');

function process($folder)
{
	$dir = scandir(Settings::AlbumQueuePath . $folder);
	foreach($dir as $entry)
	{
		if($entry == '.' | $entry == '..')
			continue;

		$newdir = Settings::AlbumQueuePath . $folder . $entry . '/';

		if(is_dir($newdir) && !is_link($newdir))
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
			$tags = Acoustid::GetMetadata($data);

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
