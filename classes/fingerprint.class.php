<?php
class Fingerprint
{
	public $acoustid;
	public $duration;
	public $filename;

	function __construct($filename)
	{
		$output = array();
		exec('fpcalc ' . escapeshellarg($filename) . ' 2>/dev/null', $output);
		$this->filename = $filename;
		$this->duration = @substr($output[1],9);
		$this->acoustid = @substr($output[2],12);
	}
}
?>
