<?php
class Settings
{
	const Version = '0.2';
	const Email = 'max@nieuwedelft.nl';

	// The API key used to query acoustid
	const AcoustIDKey = 'GLgjIs5L';

	const Mp3Path = '/pub/mp3/';
	const Mp3Owner = 'jukebox:jukebox';

	const FullAlbumPath = '/pub/mp3/Artists/';
	const AllAlbumsPath = '/pub/mp3/Uploads/Tagged/';
	const PlaylistPath = '/pub/mp3/Playlists/';
	const CompilationsPath = '/pub/mp3/Compilations/';
	const SoundtracksPath = '/pub/mp3/Soundtracks/';

	const UntaggablePath = '/pub/mp3/Uploads/Untaggable/';

	const AlbumQueuePath = '/pub/mp3/Uploads/Artists/';
	const PlaylistQueuePath = '/pub/mp3/Uploads/Playlists/';

	const SystemPath = '/pub/mp3/.tagger/';
	const SystemAlbumPath = '/pub/mp3/.tagger/albums/';
	const SystemRecordPath = '/pub/mp3/.tagger/records/';
	const SystemReleasePath = '/pub/mp3/.tagger/releases/';

	const LockPath = '/var/run/bolktagger/bolktagger.pid';
	const LogPath = '/var/log/mp3.log';

	// The minimum amount of records should be in an Album before it is put into FullAlbumPath
	const AlbumMinRecords = 4;
}

spl_autoload_register(function($class) {
    include('classes/' . strtolower($class) . '.class.php');
});
