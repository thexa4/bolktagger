<?php
include_once('settings.php');

print "Bolk Record Processor\n";
Utils::EnsureOnlyRunning();

Record::ForAll(function($record) {
	$record->GetInfo();

	if(!exists($record->info))
		return;

	$title = Settings::CleanPath($record->info->title) . '.mp3';
	$changed = false;
	$record->ForEachRelease(function($release) use ($record, $title, &$changed) {

		$album = $release->GetAlbum();
		if(!$album)
			return;

		if(!is_dir($album->path))
			mkdir($album->path, 0775, true);

		$file = $album->path . $title;
		if(is_file($file) || is_link($file))
			return;

		symlink($record->file, $file);
		$changed = true;
	});
	if($changed)
		print($record->mbid . " processed\n");
});
