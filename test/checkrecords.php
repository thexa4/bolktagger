<?php
include_once('../classes/acoustid.class.php');
include_once('../classes/musicbrainz.class.php');
include_once('../classes/tagger.class.php');

print "Bolk Record Checker\n";

$autofix = false;
if($argc >= 2 && $argv[1] == 'true')
{
	$autofix = true;
	Settings::EnsureOnlyRunning();
	print "Running in Autofix mode\n";
} else {
	print "Run with argument true to autofix issues (where possible)\n";
}

$prefixes = scandir(Settings::SystemRecordPath);
foreach($prefixes as $prefix)
{
	if($prefix[0] == '.')
		continue;

	$records = scandir(Settings::SystemRecordPath . $prefix . '/');
	foreach($records as $record)
	{
		if($record[0] == '.')
			continue;

		$dir = Settings::SystemRecordPath . $prefix . '/' . $record . '/';

		if(!is_file($dir . '.mbinfo'))
		{
			print $record . ": no mbinfo file, try running processrecords.php\n";
			if(!$autofix)
				continue;
			MusicBrainz::GetRecordMetadata($record);
			print $record . ": downloaded new .mbinfo\n";
		}

		// Get metadata
		$info = MusicBrainz::ParseRecordInfo(file_get_contents($dir . '.mbinfo'));
		if(!$info)
		{
			print $record . ": invalid .mbinfo file!\n";

			if(!$autofix)
				continue;

			unlink($dir . '.mbinfo');
			MusicBrainz::GetRecordMetadata($record);
			print $record . ": downloaded new .mbinfo\n";
		}

		if(!is_file($dir . 'record'))
		{
			print $record . ": no mp3 file found!\n";

			if(!$autofix)
				continue;

			unlink($dir . '.mbinfo');
			rmdir($dir);
			print $record . ": removed record folder\n";
		}

	}
}
