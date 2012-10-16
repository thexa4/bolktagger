<?php
include_once('settings.class.php');
include_once('musicbrainz.class.php');
include_once('release.class.php');
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
		return is_file($this->file);
	}

	function GetInfo()
	{
		$this->info = MusicBrainz::ParseRecordInfo(MusicBrainz::GetRecordMetadata($this->mbid));
		return $this->info;
	}

	function ForEachRelease($function)
	{
		if(!$this->info)
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