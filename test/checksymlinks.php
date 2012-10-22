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

function checklinks($path)
{
	global $damage;

	if(is_link($path))
	{
		if(!readlink($path))
		{
			$damage++;
			print $path . ": Faulty symlink\n";
			if($autofix)
			{
				unlink($path);
				print " - removed.\n";
				$damage--;
			}
		}
	} else {
		if(is_dir($path))
		{
			$files = scandir($path);
			foreach($files as $file)
			{
				if($file[0] == '.')
					continue;
				checklinks($path . '/' . $file);
			}
		}
	}
}

checklinks(Settings::Mp3Path);


if($damage > 0)
{
	print $damage . " errors detected, please run 'php " . $argv[0] . " true' to autocorrect.\n";
}
if($autofix)
	print "Fix attempted, please rerun without autofix\n";

