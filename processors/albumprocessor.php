<?php
include_once('classes/settings.class.php');
include_once('classes/album.class.php');

print "Bolk Album Processor\n";
Settings::EnsureOnlyRunning();

Album::ForAll(function($album) {
	if(!isset($album->info) || !$album->info)
		$album->GetInfo();
});
