<?php
class Record
{
	function __construct($mbid)
	{
		$this->mbid = $mbid;
		$this->path = Settings::SystemRecordPath . substr($mbid, 0, 2) . '/' . $mbid . '/';
		$this->file = $this->path . 'record';
		if($this->HasInfo())
			$this->GetInfo();
	}

	function Exists()
	{
		return is_file($this->file);
	}

	function HasInfo()
	{
		return is_file($this->path . '.mbinfo');
	}

	function IsValidated()
	{
		return !is_file($this->path . '.notvalidated');
	}

	function SetFile($file, $validated)
	{
		if(!$this->Exists())
		{
			if(!is_dir($this->path))
				mkdir($this->path, 0775, true);

			copy($file, $this->file);

			if(!$validated)
				file_put_contents($this->path . '.notvalidated','');
			return true;
		} else {
			if(!$validated || $this->IsValidated())
				return false;

			copy($file, $this->file);
			unlink($this->path . '.notvalidated');
			return true;
		}
	}

	function GetInfo()
	{
		$this->info = MusicBrainz::ParseRecordInfo(MusicBrainz::GetRecordMetadata($this->mbid));
		return $this->info;
	}

	// Get the release the record should be tagged with
	function GetTaggedRelease()
	{
		if(!isset($this->info))
			$this->GetInfo();

		$mindate = array();
		$minreleases = array();
		date_default_timezone_set('UTC');

		$this->ForEachRelease(function($release) use (&$mindate, &$minreleases) {
			$type = $release->info->releaseGroup->type;
			$date = strptime($release->info->releaseGroup->firstReleaseDate, '%Y-%m-%d');
			if(!$date)
				$date = strptime($release->info->releaseGroup->firstReleaseDate, '%Y');
			if(!$date)
				return;

			$time = mktime(0,0,0,$date['tm_mon'],$date['tm_mday'],$date['tm_year'] + 1900);

			if(!isset($mindate[$type]) || $mindate[$type] > $time)
			{
				$mindate[$type] = $time;
				$minreleases[$type] = $release;
			}
		});

		$importances = array(
			'Album',
			'Live',
			'Soundtrack',
			'Single',
			'EP',
			'Compilation',
			'Remix',
			'Other',
		);

		foreach($importances as $importance)
			if(isset($minreleases[$importance]))
				return $minreleases[$importance];


		if(count($mindate) > 0)
			return $minreleases[0];

		return false;
	}

	function ForEachRelease($function)
	{
		if(!isset($this->info))
			$this->GetInfo();

		// If get failed
		if(!$this->info)
			return;

		foreach($this->info->releases as $releaseinfo)
		{
			$release = new Release($releaseinfo->id);
			$function($release);
		}
	}

	public static function ForAll($function)
    {
        if(!is_dir(Settings::SystemRecordPath))
            mkdir(Settings::SystemRecordPath, 0775, true);

		$prefixes = scandir(Settings::SystemRecordPath);
		foreach($prefixes as $prefix)
		{
			if($prefix[0] == '.')
				continue;

			$records = scandir(Settings::SystemRecordPath . $prefix);
			foreach($records as $record)
			{
				if($record[0] == '.')
					continue;

				$o = new Record($record);

				$function($o);
			}
		}
	}
}
