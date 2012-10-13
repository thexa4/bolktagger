<?php
include_once('classes/settings.class.php');
include_once('classes/record.class.php');

print "Bolk Record Processor\n";
Settings::EnsureOnlyRunning();

Record::ForAll(function($record) {
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
