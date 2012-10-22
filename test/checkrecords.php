<?php
include_once('settings.php');


$autofix = false;
$forcefix = false;
$damage = 0;
if($argc >= 2 && ($argv[1] == 'true' || $argv[1] == 'force'))
{
	$autofix = true;
	Utils::EnsureOnlyRunning();
	print "Bolk Record Checker\n";
	if($argv[1] == 'force')
	{
		$forcefix = true;
		print "Running in Forcefix mode! This will remove data!\n";
	}
	else
	{
		print "Running in Autofix mode\n";
	}
}

Record::ForAll(function($record) use ($autofix, $forcefix, &$damage) {

	if(!is_file($record->file))
	{
		$damage++;
		print $record->mbid . ": no audio file present\n";
		if($autofix)
		{
			unlink($record->path . '.mbinfo');
			rmdir($record->path);
			print " - removed record\n";
			$damage--;
		}
	}

	if(!isset($record->info) || !$record->info)
	{
		$damage++;
		print $record->mbid . ": no info loaded\n";
		if($autofix)
		{
			unlink($record->path . '.mbinfo');
			$record->GetInfo();
			if(!$record->info)
			{
				if($forcefix)
				{
					unlink($record->path . '.mbinfo');
					unlink($record->path . 'record');
					rmdir($record->path);
					print " - removed record\n";
					$damage--;
				} else {
					print " - cannot correct\n";
				}
			}
			else
			{
				print " - fixed\n";
				$damage--;
			}
		}
	}
});

if($damage > 0)
{
	if(!$autofix)
		print $damage . " errors detected, please run 'php " . $argv[0] . " true' to autocorrect.\n";
	else
		print $damage . " unfixable errors detected, run 'php " . $argv[0] . " force' to remove unfixable records. This will remove data!\n";
}
if($autofix)
	print "Fix attempted, please rerun without autofix\n";
