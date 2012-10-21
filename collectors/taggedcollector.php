<?php
include_once('settings.php');

print "Bolk Tagged Collector\n";
Utils::EnsureOnlyRunning();

Record::ForAll(function($record)
{
	if(!$record->info)
		return;

	$title = Settings::CleanString($record->info->title);

	if(count($record->info->artistCredit) < 1)
		return;
	$artist = Settings::CleanString($record->info->artistCredit[0]->name);

	if(count($record->info->releases) < 1)
		return;
	$album = Settings::CleanString($record->info->releases[0]->title);

	$dir = Settings::AllAlbumsPath . Settings::CleanPath($artist) . '/' . Settings::CleanPath($album) . '/';
	$file = $dir . Settings::CleanPath($title) . '.mp3';

	if(!is_file($file) && !is_link($file))
	{
		if(!is_dir($dir))
			mkdir($dir, 0775, true);
		symlink($record->path . 'record', $file);
		print($record->mbid . ": added\n");
	}
});
