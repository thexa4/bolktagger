<?php
include_once('acoustid.class.php');
include_once('musicbrainz.class.php');
include_once('tagger.class.php');

print "Bolk Record Processor\n";

$prefixes = scandir(Settings::SystemRecordPath);
foreach($prefixes as $prefix)
{
	if($prefix[0] == '.')
		continue;

	$records = scandir(Settings::SystemRecordPath . $prefix . '/');
	foreach($records as $record)
	{
		if($record[0] == '.')
			continue;
		print("hi\n");

		// Get metadata
		$info = MusicBrainz::ParseRecordInfo(MusicBrainz::GetRecordMetadata($record));

		setlocale(LC_CTYPE, 'en_GB.UTF8');
		$title = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $info->title);

		// Place in internal album folders
		foreach($info->releases as $release)
		{
			$relinfo = MusicBrainz::ParseReleaseInfo((MusicBrainz::GetReleaseMetadata($release->id)));

			$ambid = $relinfo->releaseGroup->id;
			$dir = Settings::SystemAlbumPath . substr($ambid, 0, 2) . '/' . $ambid . '/';
			if(!is_dir($dir))
				mkdir(dir, 0775, true);

			@symlink(Settings::SystemRecordPath . $prefix . '/' . $record . '/record', $dir . $title);
		}

		// Place in All folder
		$artist = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $info->artistCredit[0]->name);
		$album = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $info->releases[0]->title);

		$dir = Settings::AllAlbumsPath . str_replace('/','',$artist) . '/' . str_replace('/','',$album) . '/';
		if(!is_dir($dir))
			mkdir($dir, 0775, true);
		@symlink(Settings::SystemRecordPath . $prefix . '/' . $record . '/record', $dir . str_replace('/','',$title);
	}
}
