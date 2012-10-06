<?php
class Tagger
{
	const destination = "/pub/mp3/Artists/";

	//Adds id3 tags to filename
	static function Tag($filename, $artist, $album, $title, $mbid)
	{
		if(empty($filename) || empty($artist) || empty($title) || empty($mbid))
			return;

		exec('lltag --yes --id3v2 -a ' . escapeshellarg($artist) . ' -A ' . escapeshellarg($album) . ' -t ' . escapeshellarg($title) . ' -c ' . escapeshellarg('mbid:' . $mbid) . ' ' . escapeshellarg($filename) . ' 2>/dev/null');
	}

	//Extracts mbid information from id3v2 tags (comment field)
	static function GetMbid($filename)
	{
		exec('lltag --id3v2 --show-tags comment	' . escapeshellarg($filename) . ' 2>/dev/null', $output);

		//Return false if comment does not contain mbid signature
		if(substr($output[1], 0, 15) != '  COMMENT=mbid:')
			return false;

		//Extract mbid or return false
		if(!preg_match('/[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}', $output[1], $match))
			return false;

		return $match[0];
	}

	//Adds id3 tags to filename and moves it to the right location
	//Returns: new path or null on error
	static function Process($filename, $artist, $album, $title, $mbid)
	{
		if(empty($filename) || empty($artist) || empty($title) || empty($mbid))
			return null;

		self::Tag($filename, $artist, $album, $title, $mbid);

		setlocale(LC_ALL, 'en_GB.utf8');
		$newpath = self::destination . str_replace('.','',str_replace('/','',iconv('UTF-8','ASCII//TRANSLIT//IGNORE', $artist))) . '/' . str_replace('.','',str_replace('/','',iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$album)));

		if(!is_dir($newpath))
			mkdir($newpath, 0775, true);

		$newname = $newpath . '/' . str_replace('/','',iconv('UTF-8','ASCII//TRANSLIT//IGNORE', $title) . '.' .  pathinfo($filename)['extension']);
		if(file_exists($newname))
			unlink($filename);
		else
			rename($filename, $newname);
		return $newname;
	}

	static function GetFilename($filename, $artist, $album, $title)
	{
		setlocale(LC_ALL, 'en_GB.utf8');
		return self::destination . str_replace('.','',str_replace('/','',iconv('UTF-8','ASCII//TRANSLIT//IGNORE', $artist))) . '/' . str_replace('.','',str_replace('/','',iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$album))) . '/' . str_replace('/','',iconv('UTF-8','ASCII//TRANSLIT//IGNORE', $title) . '.' .  pathinfo($filename)['extension']);
	}
}
