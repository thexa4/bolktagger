<?php
include_once('settings.php');

print "Bolk Full Album Collector\n";
Utils::EnsureOnlyRunning();

Album::ForAll(function($album){
	// Check missing .mbinfo file and return
	if(!$album->info)
		return;

	if(!in_array($album->info->type, array('Album', 'EP', 'Live', 'Other', 'Remix', 'Compilation')))
		return;

	// Make sure one release is full
	if(!$album->HasFullRelease())
		return;

	$records = scandir($album->path);
	$artists = $album->info->artistCredit;
	$title = $album->info->title;

	// No artist(s) attached
	if(count($artists) == 0 || !$title)
		return;

	$artist = $artists[0]->name;
	$destfolder = '/pub/mp3/Uploads/FullAlbum/' . Utils::CleanPath($artist) . '/' . Utils::CleanPath($title) . '/';
	if(!is_dir($destfolder))
	{
		mkdir($destfolder, 0775, true);
		print $album->mbid . " added\n";
	}

	foreach($records as $record)
		if($record[0] != '.' && !is_file($destfolder . $record) && !is_link($destfolder . $record))
			symlink($album->path . $record, $destfolder . $record);

});
