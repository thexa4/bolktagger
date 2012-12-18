<?php
include_once('settings.php');

print "Bolk Record Retagger\n";
Utils::EnsureOnlyRunning();

Record::ForAll(function($record) {
	$record->GetInfo();

	if(!isset($record->info))
		return;

	$title = Utils::CleanString($record->info->title);
	$artist = Utils::CleanString($record->info->artistCredit[0]->name);
	$album = Utils::CleanString($record->info->releases[0]->title);

	

	Tagger::Tag($record->file, $artist, $album, $title, $record->mbid);
	system('lltag --mp3 --id3v2 -S "' . $record->file .'"');

	print($record->mbid . " processed\n");
});
