<?php
include_once('settings.php');

print "Bolk Album Processor\n";
Utils::EnsureOnlyRunning();

Album::ForAll(function($album) {
	if(!isset($album->info) || !$album->info)
		$album->GetInfo();
});
