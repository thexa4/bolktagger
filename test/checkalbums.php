<?php
include_once('settings.php');


$autofix = false;
$damage = false;
if($argc >= 2 && $argv[1] == 'true')
{
	$autofix = true;
	Settings::EnsureOnlyRunning();
	print "Bolk Album Checker\n";
	print "Running in Autofix mode\n";
}

Album::ForAll(function($album) use ($autofix, &$damage) {

	$links = scandir($album->path);
	foreach($links as $link)
	{
		if($link[0] == '.')
			continue;

		if(!is_link($album->path . $link))
		{
			$damage = true;
			print $album->mbid . ': Unknown file (' . $link . ") found\n";
			if($autofix)
			{
				unlink($album->path . $link);
				print " - removed\n";
			}
		} else {
			if(!is_file($album->path . $link))
			{
				$damage = true;
				print $album->mbid . ': Broken symlink (' . $link . ") found\n";
				if($autofix)
				{
					unlink($album->path . $link);
					print " - removed\n";
				}
			}
		}
	}

	if(!$album->info)
	{
		$damage = true;
		print $album->mbid . ": no info loaded\n";
		if($autofix)
		{
			unlink($album->path . '.mbinfo');
			$album->GetInfo();
			if(!$album->info)
				print " - cannot correct\n";
			else
				print " - fixed\n";
		}
	}
});

if($damage)
{
	if($autofix)
		print "Fix attemted, please rerun without autofix\n";
	else
		print "Damage detected, please run 'php " . $argv[0] . " true' to autocorrect. (This might remove some data)\n";
}
