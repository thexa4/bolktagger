<?php
include('acoustid.class.php');
include('musicbrainz.class.php');
include('tagger.class.php');

print "Bolk Album Processor\n";

$prefixes = scandir(Settings::SystemAlbumPath);
foreach($prefixes as $prefix)
{
	if($prefix[0] == '.')
		continue;

	$albums = scandir(Settings::SystemAlbumPath . $prefix . '/');
	foreach($albums as $album)
	{
		if($album[0] == '.')
			continue;

		$xml = MusicBrainz::GetAlbumMetadata($album);

		$dir = Settings::SystemAlbumPath . $prefix . '/' . $album . '/';

		$records = scandir($dir);
		$count = 0;
		foreach($records as $record)
			if($record[0] != '.')
				$count++;

		if($count < Settings::AlbumMinRecords)
			continue;

		$info = MusicBrainz::ParseReleaseGroupInfo($xml);

		$artists = $info->artistCredit;
		$title = $info->title;

		// No artist(s) attached
		if(count($artists) == 0 || !$title)
		{
			print 'Error in album ' . $album . ", no title or artist\n";
			continue;
		}

		$artist = $artists[0]->name;

		// Add to Artist Albums folder
		setlocale(LC_ALL, 'en_GB.utf8');
		$artistpath = Settings::FullAlbumPath . str_replace('.','',str_replace('/','',iconv('UTF-8','ASCII//TRANSLIT//IGNORE', $artist))) . '/';
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
