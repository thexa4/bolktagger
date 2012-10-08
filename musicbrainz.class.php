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

	function ParseReleaseGroupInfo($xml)
	{
		$xml = simplexml_load_string($xml);
		$xml->registerXPathNamespace('m','http://musicbrainz.org/ns/mdd-2.0#');
		$g = $xml->{'release-group'};

		$res = new stdClass();
		$res->type = (string)$g['type'];
		$res->id = (string)$g['id'];
		$res->title = (string)$g->title;
		$res->firstReleaseDate = (string)$g->{'first-release-date'};
		$res->primaryType = (string)$g->{'primary-type'};

		$res->artistCredit = array();
		foreach($g->{'artist-credit'}->{'name-credit'} as $credit)
		{
			$c = new stdClass();
			$c->id = (string)$credit->artist['id'];
			$c->name = (string)$credit->artist->name;
			$c->sortName = (string)$credit->artist->{'sort-name'};
			$res->artistCredit[] = $c;
		}

		$res->releases = array();
		foreach($g->{'release-list'}->release as $release)
		{
			$r = new stdClass();
			$r->id = (string)$release['id'];
			$r->title = (string)$release->status;
			$r->date = (string)$release->date;
			$res->releases[] = $r;
		}

		return $res;
	}
}
