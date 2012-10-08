<?php
class Settings
{
	const Version = '0.2';
	const Email = 'max@nieuwedelft.nl';

	// The API key used to query acoustid
	const AcoustIDKey = 'GLgjIs5L';

	const Mp3Path = '/pub/mp3/';

	const FullAlbumPath = '/pub/mp3/Albums/';
	const AllAlbumsPath = '/pub/mp3/All/';
	const PlaylistPath = '/pub/mp3/Playlists/';

	const UntaggablePath = '/pub/mp3/Untaggable/';

	const AlbumQueuePath = '/pub/mp3/Queue/Albums/';
	const PlaylistQueuePath = '/pub/mp3/Queue/Playlists/';

	const SystemPath = '/pub/mp3/.tagger/';
	const SystemAlbumPath = '/pub/mp3/.tagger/albums/';
	const SystemRecordPath = '/pub/mp3/.tagger/records2/';
	const SystemReleasePath = '/pub/mp3/.tagger/releases/';

	// The minimum amount of records should be in an Album before it is put into FullAlbumPath
	const AlbumMinRecords = 4;
}
