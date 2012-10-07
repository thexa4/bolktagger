<?php
include('acoustid.class.php');
include('fingerprint.class.php');
include('tagger.class.php');

print "Bolk Album Tagger\n";

$input = '/pub/mp3/Queue/Albums/';
$untaggable = '/pub/mp3/Untaggable/';

process('');

function process($folder)
{
	global $input, $output, $untaggable;

	$dir = scandir($input . $folder);
	foreach($dir as $entry)
	{
		if($entry == '.' | $entry == '..')
			continue;

		if(is_dir($input . $folder . $entry . '/'))
		{
			process($folder . $entry . '/');
			rmdir($input . $folder . $entry . '/');
		}
		else
		{
			$file = $folder . $entry;
			print $file . ': ';

			$data = new Fingerprint($input . $file);
			if($data->acoustid == false)
			{
				unlink($input . $file);
				print "fingerprint failed\n";
				continue;
			}
			$tags = @Acoustid::GetMetadata($data);

			if(empty($tags['artist']))
			{
				@mkdir($untaggable . $folder, 0755, true);
				rename($input . $file, $untaggable . $file);
				print "unrecognised number\n";
				continue;
			}

			//fingerprinting successfull
			Tagger::Process($input . $file, $tags['artist'], $tags['album'], $tags['title'], $tags['mbid'], $tags['albummbid'], $tags['artistmbid']);
			print "\n";

			usleep(300);
		}
	}
}
?>
