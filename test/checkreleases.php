<?php
include_once('settings.php');


$autofix = false;
$damage = false;
if($argc >= 2 && $argv[1] == 'true')
{
	$autofix = true;
	Settings::EnsureOnlyRunning();
	print "Bolk Release Checker\n";
	print "Running in Autofix mode\n";
}

Release::ForAll(function($release) use ($autofix, &$damage) {

	if(!$release->info)
	{
		$damage = true;
		print $release->mbid . ": no info loaded\n";
		if($autofix)
		{
			if(is_file($release->path . '.mbinfo'))
				unlink($release->path . '.mbinfo');
			$release->GetInfo();
			if(!$release->info)
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
