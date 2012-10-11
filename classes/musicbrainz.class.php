<?php
include_once('settings.class.php');
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

	function GetAlbumMetadata($albummbid)
	{
		self::InitCurl();

		$albumpath = Settings::SystemAlbumPath . substr($albummbid, 0, 2) . '/' . $albummbid . '/';

		if(is_dir($albumpath . '.releases/') && is_file($albumpath . '.mbid'))
			return file_get_contents($albumpath . '.mbid');

		curl_setopt(self::$curl, CURLOPT_URL, self::endpoint . 'release-group/' . $albummbid . '?inc=releases+artists');
		$output = curl_exec(self::$curl);
		$f = fopen($albumpath . '.mbinfo', 'w');
		fwrite($f, $output);
		fclose($f);

		sleep(1);

		$info = self::ParseReleaseGroupInfo($output);

		@mkdir($albumpath . '.releases/', 0775);

		foreach($info->releases as $release)
		{
			self::GetReleaseMetadata($release->id);
			@symlink(Settings::SystemReleasePath . substr($release->id, 0, 2) . '/' . $release->id . '/.mbinfo', $albumpath . '.releases/' . $release->id);
		}

		return $output;
	}

	function GetReleaseMetadata($mbid)
	{
		$path = Settings::SystemReleasePath . substr($mbid, 0, 2) . '/' . $mbid . '/';

		if(file_exists($path . '.mbinfo'))
			return file_get_contents($path . '.mbinfo');

		curl_setopt(self::$curl, CURLOPT_URL, self::endpoint . 'release/' . $mbid . '?inc=recordings+release-groups+artists');
		$data = curl_exec(self::$curl);

		if(!is_dir($path))
			mkdir($path, 0775, true);

		$f = fopen($path . '.mbinfo', 'w');
		fwrite($f, $data);
		fclose($f);

		sleep(1);

		return $data;
	}

	function GetRecordMetadata($mbid)
	{
		self::InitCurl();

		$location = Settings::SystemRecordPath . substr($mbid, 0, 2) . '/' . $mbid;
		if(file_exists($location . '/.mbinfo'))
			return file_get_contents($location . '/.mbinfo');

		if(!is_dir($location))
			mkdir($location, 0775, true);

		curl_setopt(self::$curl, CURLOPT_URL, self::endpoint . 'recording/' . $mbid . '?inc=artists+releases');
		$output = curl_exec(self::$curl);

		$f = fopen($location . '/.mbinfo', 'w');
		fwrite($f, $output);
		fclose($f);

		sleep(1);

		return $output;
	}

	function ParseReleaseGroupInfo($xml)
	{
		$xml = simplexml_load_string($xml);
		$g = $xml->{'release-group'};

		if(!$g)
			return false;

		$res = new stdClass();
		$res->type = (string)$g['type'];
		$res->id = (string)$g['id'];
		$res->title = (string)$g->title;
		$res->firstReleaseDate = (string)$g->{'first-release-date'};
		$res->primaryType = (string)$g->{'primary-type'};

		$res->artistCredit = array();
		foreach($g->{'artist-credit'}->{'name-credit'} as $credit)
			$res->artistCredit[] = self::ParseArtist($credit->artist);

		$res->releases = array();
		foreach($g->{'release-list'}->release as $release)
			$res->releases[] = self::ParseRelease($release);

		return $res;
	}

	function ParseRelease($xmlelement)
	{
		$r = new stdClass();
		$r->id = (string)$xmlelement['id'];
		$r->title = (string)$xmlelement->title;
		$r->date = (string)$xmlelement->date;
		return $r;
	}

	function ParseArtist($xmlelement)
	{
		$a = new stdClass();
		$a->id = (string)$xmlelement['id'];
		$a->name = (string)$xmlelement->name;
		$a->sortName = (string)$xmlelement->{'sort-name'};
		return $a;
	}

	function ParseRecordInfo($xml)
	{
		$xml = simplexml_load_string($xml);
		$r = $xml->{'recording'};
		if(!$r)
			return false;

		$res = new stdClass();

		$res->id = (string)$r['id'];
		$res->title = (string)$r->title;
		$res->length = (string)$r->length;

		$res->artistCredit = array();
		foreach($r->{'artist-credit'}->{'name-credit'} as $credit)
			$res->artistCredit[] = self::ParseArtist($credit->artist);

		$res->releases = array();
		foreach($r->{'release-list'}->release as $release)
			$res->releases[] = self::ParseRelease($release);

		return $res;
	}

	function ParseReleaseInfo($xml)
	{
		$xml = simplexml_load_string($xml);
		$r = $xml->release;
		if(!$r)
			return false;
		$res = new stdClass();

		$res->id = (string)$r['id'];
		$res->title = (string)$r->title;
		$res->status = (string)$r->status;

		$res->artistCredit = array();
		foreach($r->{'artist-credit'}->{'name-credit'} as $credit)
			$res->artistCredit[] = self::ParseArtist($credit->artist);

		$rg = $r->{'release-group'};
		$res->releaseGroup = new stdClass();
		$res->releaseGroup->id = (string)$rg['id'];
		$res->releaseGroup->type = (string)$rg['type'];
		$res->releaseGroup->title = (string)$rg->title;
		$res->releaseGroup->firstReleaseDate = (string)$rg->{'first-release-date'};

		$res->date = (string)$r->date;
		$res->country = (string)$r->country;
		$res->barcode = (string)$r->barcode;
		$res->asin = (string)$r->asin;

		$res->medium = array();
		foreach($r->{'medium-list'}->medium as $medium)
		{
			$m = new stdClass();
			$m->position = (string)$medium->position;

			$m->tracks = array();
			foreach($medium->{'track-list'}->track as $track)
			{
				$t = new stdClass();
				$t->position = (string)$track->position;
				$t->number = (string)$track->number;
				$t->length = (string)$track->length;
				$t->recording = new stdClass();
				$t->recording->id = (string)$track->recording['id'];
				$t->recording->title = (string)$track->recording->title;
				$t->recording->length = (string)$track->recording->length;
				$m->tracks[] = $t;
			}
			$res->medium[] = $m;
		}

		return $res;
	}
}
