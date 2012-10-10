<?php
include_once('../classes/acoustid.class.php');
include_once('../classes/musicbrainz.class.php');
include_once('../classes/tagger.class.php');

print "Bolk Record Checker\n";
if($argc < 2)
{
	print "Usage: php recordchecker.php <AutoFix>\n";
	print "Example: php recordchecker.php true\n";
	exit;
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
			//print $record . ": no mbinfo file, try running processrecords.php\n";
			continue;
		}

		// Get metadata
		$info = MusicBrainz::ParseRecordInfo(file_get_contents($dir . '.mbinfo'));
		if(!$info)
		{
			print $record . ": invalid .mbinfo file!\n";
			continue;
		}

		if(!is_file($dir . 'record'))
		{
			print $record . ": no mp3 file found!\n";
			continue;
		}

	}
}
