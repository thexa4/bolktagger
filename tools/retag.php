<?php
include_once('settings.php');

print "Bolk Record Retagger\n";
Utils::EnsureOnlyRunning();

Record::ForAll(function($record) {
	$record->GetInfo();

	if(!isset($record->info))
		return;

	$title = Utils::CleanPath($record->info->title) . '.mp3';

	Tagger::Tag($record->file, $record->info->artistCredit[0]->name, $record->info->releases[0]->title, $record->info->title, $record->mbid);

	print($record->mbid . " processed\n");
});
