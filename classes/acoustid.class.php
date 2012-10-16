<?php
define('ACOUSTID_KEY', 'GLgjIs5L');
class Acoustid
{
	protected static $curl = null;

	static function InitCurl()
	{
		if(self::$curl == null)
		{
			self::$curl = curl_init("http://api.acoustid.org/v2/lookup");
			curl_setopt(self::$curl, CURLOPT_POST, true);
			curl_setopt(self::$curl, CURLOPT_RETURNTRANSFER, true);
		}
	}

	static function GetMbid($fingerprint)
	{
		self::InitCurl();

		curl_setopt(self::$curl, CURLOPT_POSTFIELDS, "client=" . Settings::AcoustIDKey . '&duration=' . $fingerprint->duration . '&fingerprint=' . $fingerprint->acoustid . '&meta=recordings');
		$output = curl_exec(self::$curl);
		$output = json_decode($output);

		if($output->status != "ok")
			return false;

		$res = array();

		if(!isset($output->results[0]->recordings))
			return false;

		foreach($output->results[0]->recordings as $recording)
			$res[] = preg_replace('/[^0-9a-fA-F-]/','',$recording->id);

		if(count($res) == 0)
			return false;

		return $res;
	}

	function GetMetadata($fingerprint)
	{
		self::InitCurl();

		curl_setopt(self::$curl, CURLOPT_POSTFIELDS, "client=" . ACOUSTID_KEY . '&duration=' . $fingerprint->duration . '&fingerprint=' . $fingerprint->acoustid . '&meta=recordings+releasegroups+compress');
		$output = curl_exec(self::$curl);
		$output = json_decode($output);

		if($output->status != "ok")
			return -1;

		$match = $output->results[0]->recordings[0];

		$albummbid = array();

		foreach($match->releasegroups as $release)
			$albummbid[] = $release->id;

		return array("artist" => $match->artists[0]->name,
			"album" => $match->releasegroups[0]->title,
			"title" => $match->title,
			"mbid" => $match->id,
			"artistmbid" => $match->artists[0]->id,
			"albummbid" => $albummbid,
		);
	}
}
?>
