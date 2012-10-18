<?php
include_once('classes/acoustid.class.php');
include_once('classes/musicbrainz.class.php');
include_once('classes/tagger.class.php');
include_once('classes/album.class.php');
include_once('classes/release.class.php');
include_once('classes/record.class.php');

print "Bolk Compilations Collector\n";
Settings::EnsureOnlyRunning();

Album::ForAll(function($album){
	// Check missing .mbinfo file and return
	if(!$album->info)
		return;

	// Album type should be compilation
	if(!in_array($album->info->type, ['Compilation']))
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
	$destfolder = Settings::CompilationsPath . Settings::CleanPath($title) . '/';
	if(!is_dir($destfolder))
	{
		mkdir($destfolder, 0775, true);
		print $album->mbid . " added\n";
	}

	foreach($records as $record)
		if($record[0] != '.' && !is_file($destfolder . $record) && !is_link($destfolder . $record))
			symlink($album->path . $record, $destfolder . $record);

});
