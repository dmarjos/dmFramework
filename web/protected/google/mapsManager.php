<?php
Application::Uses("sys.tools.curl");
class mapsManager {
	
	public static function getLatLong($address) {
		if (!is_string($address)) return false;
		//$_url = sprintf('http://maps.google.com/maps?output=js&q=%s',rawurlencode($address));
		$_url = sprintf('https://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false',rawurlencode($address));
		$_result = false;
		
		$curl=new curl($_url);
		$_result = $curl->execute();
		if($_result) {
			$data = json_decode($_result, true);
			$_coords['lat'] = $data["results"][0]["geometry"]["location"]["lat"];
			$_coords['long'] = $data["results"][0]["geometry"]["location"]["lng"];
		}
		return $_coords;
	}
}