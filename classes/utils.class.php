<?php
class Utils
{
	static function CleanString($string)
	{
		setlocale(LC_ALL, 'en_GB.UTF8');
		return iconv('UTF-8','ASCII//TRANSLIT//IGNORE', $string);
	}

	// Strips leading dots, removes slashes and removes unicode characters
	static function CleanPath($string)
	{
		return preg_replace('/^\.+/','',str_replace('/','',self::CleanString($string)));
	}

	static function EnsureOnlyRunning()
	{
		if(is_file(Settings::LockPath))
		{
			$pid = file_get_contents(Settings::LockPath);
			if(!self::isRunning($pid))
				RemoveLock();
			else
				die("Already running\n");
		}

		$lockfile = fopen(Settings::LockPath, 'w');
		fwrite($lockfile, getmypid());
		fclose($lockfile);

		register_shutdown_function('RemoveLock');
	}

	// Kill signal 0 doesn't kill it but checks if a signal can be sent
	static function isRunning($pid) {
		return posix_kill($pid, 0);
	}
}

function RemoveLock()
{
	unlink(Settings::LockPath);
}
