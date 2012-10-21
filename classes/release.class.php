<?php
class Release
{
	function __construct($mbid)
	{
		$this->mbid = $mbid;
		$this->path = Settings::SystemReleasePath . substr($mbid, 0, 2) . '/' . $mbid . '/';
		if($this->HasInfo());
			$this->GetInfo();
	}

	function Exists()
	{
		return HasInfo();
	}

	function HasInfo()
	{
		return is_file($this->path);
	}

	function GetInfo()
	{
		$this->info = MusicBrainz::ParseReleaseInfo(MusicBrainz::GetReleaseMetadata($this->mbid));
		return $this->info;
	}

	function GetAlbum()
	{
		if(!$this->info)
			$this->GetInfo();

		if(!$this->info)
			return false;

		return new Album($this->info->releaseGroup->id);
	}

	function IsComplete()
	{
		if(!$this->info)
			$this->GetInfo();

		// Get failed
		if(!$this->info)
			return false;

		foreach($this->info->medium as $medium)
			foreach($medium->tracks as $track)
			{
				$record = new Record($track->recording->id);
				if(!$record->Exists())
					return false;
			}

		return true;
	}

	public static function ForAll($function)
    {
        if(!is_dir(Settings::SystemReleasePath))
            mkdir(Settings::SystemReleasePath, 0775, true);

		$prefixes = scandir(Settings::SystemReleasePath);
		foreach($prefixes as $prefix)
		{
			if($prefix[0] == '.')
				continue;

			$releases = scandir(Settings::SystemReleasePath . $prefix);
			foreach($releases as $release)
			{
				if($release[0] == '.')
					continue;

				$o = new Release($release);

				$function($o);
			}
		}
	}
}
