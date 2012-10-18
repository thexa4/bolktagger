<?php
include_once('classes/acoustid.class.php');
include_once('classes/musicbrainz.class.php');
include_once('classes/tagger.class.php');
include_once('classes/album.class.php');

print "Bolk Artist Collector\n";
Settings::EnsureOnlyRunning();

Album::ForAll(function($album){
	if(!isset($album->info))
		return;

	if(!in_array($album->info->type, ['Album', 'Single', 'EP', 'Live', 'Other', 'Remix', 'Compilation']))
		return;

	$records = scandir($album->path);
	$count = 0;
	foreach($records as $record)
		if($record[0] != '.')
			$count++;

	if($count < Settings::AlbumMinRecords)
		return;

	$artists = $album->info->artistCredit;
	$title = $album->info->title;

	// No artist(s) attached
	if(count($artists) == 0 || !$title)
		return;

	$artist = $artists[0]->name;
	$destfolder = Settings::FullAlbumPath . Settings::CleanPath($artist) . '/' . Settings::CleanPath($title) . '/';
	if(!is_dir($destfolder))
	{
		mkdir($destfolder, 0775, true);
		print $album->mbid . " added\n";
	}

	foreach($records as $record)
		if($record[0] != '.' && !is_file($destfolder . $record) && !is_link($destfolder . $record))
			symlink($album->path . $record, $destfolder . $record);

});
