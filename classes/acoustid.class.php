<?php
include_once('settings.class.php');
class Acoustid
{
	protected static $curl = null;

	function GetMetadata($fingerprint)
	{
		if(self::$curl == null)
		{
			self::$curl = curl_init("http://api.acoustid.org/v2/lookup");
			curl_setopt(self::$curl, CURLOPT_POST, true);
			curl_setopt(self::$curl, CURLOPT_RETURNTRANSFER, true);
		}

		curl_setopt(self::$curl, CURLOPT_POSTFIELDS, "client=" . Settings::AcoustIDKey . '&duration=' . $fingerprint->duration . '&fingerprint=' . $fingerprint->acoustid . '&meta=recordings+releasegroups+compress');
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
