<?php
include_once('settings.php');

print "Bolk Playlist Tagger\n";
Utils::EnsureOnlyRunning();

Tagger::IterateFolder(Settings::PlaylistQueuePath,
	function($file) {
		$record = Tagger::AddFile(Settings::PlaylistQueuePath . $file);
		if(is_int($record) && $record == -1)
		{
			// Not playable mp3 file
			unlink(Settings::PlaylistQueuePath . $file);
			print $file . " removed\n";
			return;
		}

		$destination = Settings::PlaylistPath . $file;

	        $dir = pathinfo(Settings::PlaylistPath . $file);
	        $dir = $dir['dirname'];
		if(!is_dir($dir))
			mkdir($dir, 0775, true);

		if($record)
		{
			if(is_file($destination))
				unlink($destination);
			symlink($record->file, $destination);

			// Remove from queue
			unlink(Settings::PlaylistQueuePath . $file);
		} else {
			rename(Settings::PlaylistQueuePath . $file, $destination);
		}
		print $file . " done\n";
	},
	function($dir) {}
);

exit;
	
function process($folder)
{
	$dir = scandir(Settings::PlaylistQueuePath . $folder);
	foreach($dir as $entry)
	{
		if($entry == '.' | $entry == '..')
			continue;

		$newdir = Settings::PlaylistQueuePath . $folder . $entry . '/';

		if(is_dir($newdir) && !is_link($newdir))
			process($folder . $entry . '/');
		else
		{
			$file = $folder . $entry;
			print $file . "\n";

			$data = new Fingerprint(Settings::PlaylistQueuePath . $file);
			if($data->acoustid == false)
			{
				unlink(Settings::PlaylistQueuePath . $file);
				print "unreadable, unlink\n";
				continue;
			}
			$tags = @Acoustid::GetMetadata($data);

            $path = pathinfo(Settings::PlaylistPath . $file);
            $path = $path['dirname'];
			if(!is_dir($path))
				mkdir($path, 0775, true);

			if(!empty($tags['artist']))
			{
				//fingerprinting successfull
				if(is_file(Tagger::GetFilename(Settings::PlaylistQueuePath . $file, $tags['artist'], $tags['album'] , $tags['title'])))
				{
					//already exists
					Tagger::Tag(Settings::PlaylistQueuePath . $file, $tags['artist'], $tags['album'], $tags['title'], $tags['mbid'], $tags['albummbid'], $tags['artistmbid']);
					$location = Tagger::GetFilename(Settings::PlaylistQueuePath . $file, $tags['artist'], $tags['album'] , $tags['title']);
					$old = md5_file($location);
					$new = md5_file(Settings::PlaylistQueuePath . $file);
					if($old == $new)
					{
						//same file
						exec('ln -s -n ' . escapeshellarg($location) . ' ' . escapeshellarg(Settings::PlaylistPath . $file));
						unlink(Settings::PlaylistQueuePath . $file);
					} else {
						//different versionA
						rename(Settings::PlaylistQueuePath . $file, Settings::PlaylistPath . $file);
					}
				} else {
					//does not exist, copy
					$location = Tagger::Process(Settings::PlaylistQueuePath . $file, $tags['artist'], $tags['album'], $tags['title'], $tags['mbid'], $tags['albummbid'], $tags['artistmbid']);
					exec('ln -s -n ' . escapeshellarg($location) . ' ' . escapeshellarg(Settings::PlaylistPath . $file));
				}
			} else {
				//no idea what this is, move
				rename(Settings::PlaylistQueuePath . $file, Settings::PlaylistPath . $file);
			}

			usleep(300);
		}
	}
}
?>
