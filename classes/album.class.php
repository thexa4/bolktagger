<?php
class Album
{
	function __construct($mbid)
	{
		$this->mbid = $mbid;
		$this->path = Settings::SystemAlbumPath . substr($mbid, 0, 2) . '/' . $mbid . '/';
		if($this->HasInfo())
			$this->GetInfo();
	}

	function Exists()
	{
		return is_path($this->path);
	}

	function HasInfo()
	{
		return is_file($this->path . '.mbinfo');
	}

	function HasFullRelease()
	{
		$result = false;
		$this->ForReleases(function($release) use ($result){
			if($release->IsComplete())
				$result = true;
		});

		return $result;
	}

	function GetInfo()
	{
		$this->info = MusicBrainz::ParseReleaseGroupInfo(MusicBrainz::GetAlbumMetadata($this->mbid));
		return $this->info;
	}

	function ForReleases($function)
	{
		if(!$this->info)
			$this->GetInfo();

		// Get failed
		if(!$this->info)
			return false;

		foreach($this->info->releases as $releaseinfo)
		{
			$release = new Release($releaseinfo->id);
			$function($release);
		}
	}

	public static function ForAll($function)
    {
        if(!is_dir(Settings::SystemAlbumPath))
            mkdir(Settings::SystemAlbumPath, 0775, true);

		$prefixes = scandir(Settings::SystemAlbumPath);
		foreach($prefixes as $prefix)
		{
			if($prefix[0] == '.')
				continue;

			$albums = scandir(Settings::SystemAlbumPath . $prefix . '/');
			foreach($albums as $album)
			{
				if($album[0] == '.')
					continue;

				$o = new Album($album);
				if(strlen($album) == 2)
					print Settings::SystemAlbumPath . $prefix . '/';

				$function($o);
			}
		}
	}
}
