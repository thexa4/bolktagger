<?php

class Tagger
{
	//Adds id3 tags to filename
	static function Tag($filename, $artist, $album, $title, $mbid)
	{
		if(empty($filename) || empty($artist) || empty($title) || empty($mbid))
			return;

		exec('lltag --yes --id3v2 -a ' . escapeshellarg($artist) . ' -A ' . escapeshellarg($album) . ' -t ' . escapeshellarg($title) . ' -c ' . escapeshellarg('mbid:' . $mbid) . ' ' . escapeshellarg($filename) . ' 2>/dev/null');
	}

	//Extracts mbid information from id3v2 tags (comment field)
	static function GetMbid($filename)
	{
		exec('lltag --id3v2 --show-tags comment	' . escapeshellarg($filename) . ' 2>/dev/null', $output);

		if(count($output) < 2)
			return false;

		//Return false if comment does not contain mbid signature
		if(substr($output[1], 0, 15) != '  COMMENT=mbid:')
			return false;

		//Extract mbid or return false
		if(!preg_match('/[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}', $output[1], $match))
			return false;

		return $match[0];
	}

	//Extracts the picard mbid
	static function GetPicardMbid($filename)
	{
		exec('tools/getmbid ' . escapeshellarg($filename), $output, $exitcode);
		if($exitcode != 0)
			return false;

		return preg_replace('/^[0-9a-fA-F-]/','',$output[0]);
	}

	static function AddFile($filename)
	{
		if(!is_file($filename))
			return -1;


		$data = new Fingerprint($filename);
		if($data->acoustid == false)
			return -1;

		$mbids = Acoustid::GetMbid($data);
		$usedfile = false;

		if($mbids)
		{
			foreach($mbids as $mbid)
			{
				$record = new Record($mbid);
				$usedfile = $record;
				$record->SetFile($filename, true);
			}

			return $usedfile;
		}

		// Allow user supplied tags
		$mbid = self::GetMbid($filename);
		if(!$mbid)
			$mbid = self::GetPicardMbid($filename);
		if(!$mbid)
			return false;

		$record = new Record($mbid);
		$record->SetFile($filename, false);

		return $record;
	}

	//Adds id3 tags to filename and moves it to the right location
	//Returns: new path or null on error
	static function Process($filename, $artist, $album, $title, $mbid)
	{
		if(empty($filename) || empty($artist) || empty($title) || empty($mbid))
			return null;

		$artist = Utils::CleanString($album);
		$album = Utils::CleanString($album);
		$title = Utils::CleanString($album);

		self::Tag($filename, $artist, $album, $title, $mbid);

		// Create internal record folder
		$intNumberPath = Settings::SystemRecordPath . substr($mbid, 0, 2) . '/' . $mbid . '/';

		if(!is_dir($intNumberPath))
			mkdir($intNumberPath, 0775, true);

		$newpath = $intNumberPath . 'record';

		if(file_exists($newpath))
		{
			unlink($filename);
			return true;
		}
		else
		{
			rename($filename, $newpath);
		}

		return $newpath;
	}

	// Run function for all files in directory
	static function IterateFolder($path, $filefunction, $dirfunction)
	{
		self::IterateEntry($path, '', $filefunction, $dirfunction);
	}

	static function IterateEntry($base, $path, $filefunction, $dirfunction)
	{
		$entry = $base . $path;
		if(is_link($entry))
			return;
		if(is_file($entry))
		{
			$filefunction($path);
			return;
		}
		if(is_dir($entry))
		{
			$sub = scandir($entry);
			foreach($sub as $s)
				if($s != '.' && $s != '..')
					self::IterateEntry($base, $path . '/' . $s, $filefunction, $dirfunction);

			$dirfunction($path . '/');
		}
	}
}
