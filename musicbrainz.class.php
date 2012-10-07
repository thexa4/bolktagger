<?php
class Musicbrainz
{
	protected static $curl = null;
	const endpoint = "http://www.musicbrainz.org/ws/2/";
	const systemfolder = "/pub/mp3/.tagger/";

	function DownloadMetadata($albummbid)
	{
		if(self::$curl == null)
		{
			self::$curl = curl_init(self::endpoint);
			curl_setopt(self::$curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt(self::$curl, CURLOPT_USERAGENT, 'BolkTagger/0.1 ( max@nieuwedelft.nl )' );
		}

		$albumpath = self::systemfolder . 'albums/' . substr($albummbid, 0, 2) . '/' . $albummbid . '/';

		if(is_dir($albumpath . '.releases/'))
			return true;

		curl_setopt(self::$curl, CURLOPT_URL, self::endpoint . 'release-group/' . $albummbid . '?inc=releases+artists');
		$output = curl_exec(self::$curl);
		$f = fopen($albumpath . '.mbinfo', 'w');
		fwrite($f, $output);
		fclose($f);

		sleep(1);

		if(!preg_match_all('/<release id="(\w{8}-\w{4}-\w{4}-\w{4}-\w{12})"/', $output, $releases))
			return false;

		mkdir($albumpath . '.releases/', 0775);

		foreach($releases[1] as $release)
		{
			curl_setopt(self::$curl, CURLOPT_URL, self::endpoint . 'release/' . $release . '?inc=recordings');
			$output = curl_exec(self::$curl);

			$f = fopen($albumpath . '.releases/' . $release, 'w');
			fwrite($f, $output);
			fclose($f);

			sleep(1);
		}
	}
}
?>
