<?php
include_once('settings.php');

print "Bolk Soundtrack Collector\n";
Utils::EnsureOnlyRunning();

Album::ForAll(function($album){
	if(!isset($album->info))
		return;

	if(!in_array($album->info->type, array('Soundtrack')))
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
	$destfolder = Settings::SoundtracksPath . Utils::CleanPath($title) . '/';
	if(!is_dir($destfolder))
	{
		mkdir($destfolder, 0775, true);
		print $album->mbid . " added\n";
	}

	foreach($records as $record)
		if($record[0] != '.' && !is_file($destfolder . $record) && !is_link($destfolder . $record))
			symlink($album->path . $record, $destfolder . $record);

});
