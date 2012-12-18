<?php
include_once('settings.php');

print "Bolk Artist Collector\n";
Utils::EnsureOnlyRunning();

Album::ForAll(function($album){
	if(!isset($album->info))
		return;

	if(!in_array($album->info->type, array('Album', 'Single', 'EP', 'Live', 'Other', 'Remix', 'Compilation')))
		return;

	$releases = array();
	foreach($album->info->releases as $release)
		$releases[] = new Release($release->id);

	$records = array();
	foreach($releases as $release)
		foreach($release->info->medium as $medium)
			foreach($medium->tracks as $track)
			{
				$newrecord = new Record($track->recording->id);
				if($newrecord->Exists() && !isset($records[$track->recording->id]))
				{
					$newrecord->nr = sprintf('%02d%02d', $medium->position, $track->position);
					$records[$track->recording->id] = $newrecord;
				}
			}
	$count = count($records);

	if($count < Settings::AlbumMinRecords)
		return;

	$artists = $album->info->artistCredit;
	$title = $album->info->title;

	// No artist(s) attached
	if(count($artists) == 0 || !$title)
		return;

	$artist = $artists[0]->name;
	$destfolder = Settings::FullAlbumPath . Utils::CleanPath($artist) . '/' . Utils::CleanPath($title) . '/';
	if(!is_dir($destfolder))
	{
		mkdir($destfolder, 0775, true);
		print $album->mbid . " added\n";
	}

	$maxbest = 0;
	foreach($releases as $release)
	{
		$currentbest = 0;
		$current = array();
		foreach($release->info->medium as $medium)
			foreach($medium->tracks as $track)
			{
				if(!isset($records[$track->recording->id]))
					continue;
				$currentbest++;
				$current[$track->recording->id] = sprintf('%02d%02d', $medium->position, $track->position);
			}
		if($currentbest > $maxbest)
		{
			$maxbest = $currentbest;
			foreach($current as $id => $nr)
				$records[$id]->nr = $nr;
		}
	}

	// Add new symlinks
	$touched = array();
	foreach($records as $id => $record)
	{
		$filename = $record->nr . ' - ' . Utils::CleanPath($record->info->title) . '.mp3';
		$dest = $destfolder . $filename;

		$touched[] = $filename;
		if(!is_file($dest) && !is_link($dest))
			symlink($record->file, $dest);
	}

	// Remove wrong symlinks
	$dir = scandir($destfolder);
	foreach($dir as $file)
	{
		if($file[0] == '.')
			continue;
		if(!in_array($file, $touched))
			unlink($destfolder . $file);
	}
});
