<?php
include('acoustid.class.php');
include('musicbrainz.class.php');
include('tagger.class.php');

print "Bolk Album Processor\n";

$input = '/pub/mp3/.tagger/albums/';
$output = '/pub/mp3/Albums/';

// Minimum numbers to show up in public list
$mincount = 4;

$prefixes = scandir($input);
foreach($prefixes as $prefix)
{
	if($prefix[0] == '.')
		continue;

	$albums = scandir($input . $prefix . '/');
	foreach($albums as $album)
	{
		if($album[0] == '.')
			continue;

		MusicBrainz::DownloadMetadata($album);

		$dir = $input . $prefix . '/' . $album . '/';

		$records = scandir($dir);
		$count = 0;
		foreach($records as $record)
			if($record[0] != '.')
				$count++;

		if($count < $mincount)
			continue;

		$xml = simplexml_load_file($dir . '.mbinfo');
		$xml->registerXPathNamespace('m','http://musicbrainz.org/ns/mmd-2.0#');

		$artists = $xml->xpath('//m:artist/m:name');
		$title = $xml->xpath('m:release-group/m:title');

		// No artist(s) attached
		if(count($artists) == 0 || count($title) == 0)
		{
			print 'Error in album ' . $album . ", no title or artist\n";
			continue;
		}

		$title = $title[0];
		$artist = $artists[0];

		// Add to Artist Albums folder
		setlocale(LC_ALL, 'en_GB.utf8');
		$artistpath = $output . str_replace('.','',str_replace('/','',iconv('UTF-8','ASCII//TRANSLIT//IGNORE', $artist))) . '/';
		$fullpath = $artistpath . str_replace('.','',str_replace('/','',iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$title)));
		if(!file_exists($fullpath))
		{
			if(!is_dir($artistpath))
				mkdir($artistpath, 0775, true);
			symlink($dir, $fullpath);
			print $artist . ' - ' . $title . " added.\n";
		}
	}
}
