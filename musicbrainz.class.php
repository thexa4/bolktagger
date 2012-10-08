<?php
include('settings.class.php');
class Musicbrainz
{
	protected static $curl = null;
	const endpoint = "http://www.musicbrainz.org/ws/2/";

	function InitCurl()
	{
		if(self::$curl != null)
			return;

		self::$curl = curl_init(self::endpoint);
		curl_setopt(self::$curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt(self::$curl, CURLOPT_USERAGENT, 'BolkTagger/' . Settings::Version . ' ( ' . Settings::Email . ' )' );
	}

	function DownloadAlbumMetadata($albummbid)
	{
		self::InitCurl();

		$albumpath = Settings::SystemAlbumPath . substr($albummbid, 0, 2) . '/' . $albummbid . '/';

		if(is_dir($albumpath . '.releases/'))
			return true;

		curl_setopt(self::$curl, CURLOPT_URL, self::endpoint . 'release-group/' . $albummbid . '?inc=releases+artists');
		$output = curl_exec(self::$curl);
		$f = fopen($albumpath . '.mbinfo', 'w');
		fwrite($f, $output);
		fclose($f);

		sleep(1);

		$info = self::ParseReleaseGroupInfo($output);

		mkdir($albumpath . '.releases/', 0775);

		foreach($info->releases as $release)
		{
			curl_setopt(self::$curl, CURLOPT_URL, self::endpoint . 'release/' . $release . '?inc=recordings');
			$output = curl_exec(self::$curl);

			$f = fopen($albumpath . '.releases/' . $release->id, 'w');
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
