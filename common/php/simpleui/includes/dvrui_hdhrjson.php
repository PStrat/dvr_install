<?php
	require_once("includes/dvrui_common.php");
	
class DVRUI_HDHRjson {
	private $myhdhrurl = '';
	private $hdhrkey_devID = 'DeviceID';
	private $hdhrkey_localIP = 'LocalIP';
	private $hdhrkey_baseURL = 'BaseURL';
	private $hdhrkey_discoverURL = 'DiscoverURL';
	private $hdhrkey_lineupURL = 'LineupURL';
	private $hdhrkey_modelNum = 'ModelNumber';
	private $hdhrkey_modelName = 'FriendlyName';
	private $hdhrkey_auth = 'DeviceAuth';
	private $hdhrkey_fwVer = 'FirmwareVersion';
	private $hdhrkey_ver = 'Version';
	private $hdhrkey_fwName = 'FirmwareName';
	private $hdhrkey_tuners = 'TunerCount';
	private $hdhrkey_legacy = 'Legacy';
	private $hdhrkey_freespace = 'FreeSpace';

	private $hdhrkey_storageID = 'StorageID';
	private $hdhrkey_storageURL = 'StorageURL';
	private $hdhrlist_key_channelcount = 'ChannelCount';
	private $hdhrlist = array();
	private $enginelist = array();

	public function DVRUI_HDHRjson() {
		$this->myhdhrurl = DVRUI_Vars::DVRUI_apiurl . 'discover';
		$storageURL = "??";
		$myip = $_SERVER['SERVER_ADDR'];

    error_log("Checkip IPs: " . getHostByName(getHostName()));
		$hdhr_data = getJsonFromUrl($this->myhdhrurl);
		$hdhr_count = count($hdhr_data);
		error_log('Processing ' . $hdhr_count . ' discovered devices');
		
		for ($i=0;$i<$hdhr_count;$i++) {
			$hdhr = $hdhr_data[$i];
			$hdhr_base = $hdhr[$this->hdhrkey_baseURL];
			$hdhr_ip = $hdhr[$this->hdhrkey_localIP];
			
			if (!array_key_exists($this->hdhrkey_discoverURL,$hdhr)) {
				// Skip this HDHR - it doesn't support the newer HTTP interface
				// for DVR
				error_log('Skipping Device - not HTTP capable');
				continue;
			}

			error_log('Processing ' . $hdhr[$this->hdhrkey_discoverURL]);
			$hdhr_info = getJsonFromUrl($hdhr[$this->hdhrkey_discoverURL]);

			if (array_key_exists($this->hdhrkey_storageURL,$hdhr)) {
				error_log('Record Engine Discovered');
				// this is a record engine!
				// Need to confirm it's a valid one - After restart of
				// engine it updates api.hdhomerun.com but sometimes the
				// old engine config is left behind.
				$rEngine = getJsonFromUrl($hdhr[$this->hdhrkey_discoverURL]);
				error_log('Engine found ' . $rEngine[$this->hdhrkey_storageID]. 'Checking against' . $hdhr[$this->hdhrkey_storageID]);
				$engCheck = strcasecmp($rEngine[$this->hdhrkey_storageID],$hdhr[$this->hdhrkey_storageID])
				if ( $engCheck != 0) {
					//skip, this is not our engine
					error_log('Engine found - not this record engine');
					continue;
				} else  {
				  // this is us :)
					error_log('Engine found - matches our expect StorageID - must be us');
    			$this->storageURL = $hdhr[$this->hdhrkey_storageURL];
    		}

        /*
				//get the IP address of record engine.
				$hdhr_ip = $hdhr[$this->hdhrkey_localIP];
				// Split IP and port
				if (preg_match('/^(\d[\d.]+):(\d+)\b/', $hdhr_ip, $matches)) {
					$ip = $matches[1];
					$port = $matches[2];
					// if IP of record engine matches the IP of this server
					// return storageURL
					error_log('Checking '. $ip . ' against ' . $myip);
					if($ip == $myip){	
						$this->storageURL = $hdhr[$this->hdhrkey_storageURL];
					}
				}
				*/
				
				error_log('Adding engine ' . $hdhr_base);
				$this->enginelist[] = array( $this->hdhrkey_storageID => $hdhr[$this->hdhrkey_storageID],
					$this->hdhrkey_baseURL => $hdhr_base,
					$this->hdhrkey_modelName => $hdhr_info[$this->hdhrkey_modelName],
					$this->hdhrkey_ver => $hdhr_info[$this->hdhrkey_ver],
					$this->hdhrkey_storageID => $hdhr_info[$this->hdhrkey_storageID],
					$this->hdhrkey_storageURL => $hdhr_info[$this->hdhrkey_storageURL],
					$this->hdhrkey_freespace => $hdhr_info[$this->hdhrkey_freespace]);
				continue;
			}
			error_log('must be a tuner');
			// ELSE we have a tuner

			$tuners='unknown';
			if (array_key_exists($this->hdhrkey_tuners,$hdhr_info)) {
				$tuners = $hdhr_info[$this->hdhrkey_tuners];
			}

			$legacy='No';
			if (array_key_exists($this->hdhrkey_legacy,$hdhr_info)) {
				$legacy = $hdhr_info[$this->hdhrkey_legacy];
			}

			$hdhr_lineup = getJsonFromUrl($hdhr_info[$this->hdhrkey_lineupURL]);	

			$this->hdhrlist[] = array( $this->hdhrkey_devID => $hdhr[$this->hdhrkey_devID],
										$this->hdhrkey_modelNum => $hdhr_info[$this->hdhrkey_modelNum],
										$this->hdhrlist_key_channelcount => count($hdhr_lineup),
										$this->hdhrkey_baseURL => $hdhr_base,
										$this->hdhrkey_lineupURL => $hdhr_info[$this->hdhrkey_lineupURL],
										$this->hdhrkey_modelName => $hdhr_info[$this->hdhrkey_modelName],
										$this->hdhrkey_auth =>$hdhr_info[$this->hdhrkey_auth],
										$this->hdhrkey_fwVer => $hdhr_info[$this->hdhrkey_fwVer],
										$this->hdhrkey_tuners => $tuners,
										$this->hdhrkey_legacy => $legacy,
										$this->hdhrkey_fwName => $hdhr_info[$this->hdhrkey_fwName]);
		}		
	}
	
	public function device_count() {
		return count($this->hdhrlist);
	}

	public function get_device_info($pos) {
		$device = $this->hdhrlist[$pos];
		return ' DeviceID: ' . $device[$this->hdhrkey_devID] 
		          . ' Model Number: ' . $device[$this->hdhrkey_modelNum] 
		          . ' Channels: ' . $device[$this->hdhrlist_key_channelcount] . ' ';
	}
	public function get_storage_url(){
		return $this->storageURL;
	}	
	
	public function get_device_id($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_devID];
	}

	public function get_device_model($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_modelNum];
	}

	public function get_device_modelname($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_modelName];
	}

	public function get_device_channels($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrlist_key_channelcount];
	}

	public function get_device_lineup($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_lineupURL];
	}

	public function get_device_baseurl($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_baseURL];
	}

	public function get_device_firmware($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_fwVer];
	}

	public function get_device_fwname($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_fwName];
	}

	public function get_device_tuners($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_tuners];
	}


	public function get_device_auth($pos) {
		$device = $this->hdhrlist[$pos];
		if (array_key_exists($this->hdhrkey_auth,$device)) {
			return $device[$this->hdhrkey_auth];
		} else {
			return '??';
		}
	}

	public function engine_count() {
		return count($this->enginelist);
	}

	public function get_engine_baseurl($pos) {
		$device = $this->enginelist[$pos];
		return $device[$this->hdhrkey_baseURL];
	}

	public function get_engine_modelname($pos) {
		$device = $this->enginelist[$pos];
		return $device[$this->hdhrkey_modelName];
	}
	
	public function get_engine_firmware($pos) {
		$device = $this->enginelist[$pos];
		return $device[$this->hdhrkey_ver];
	}

	public function get_engine_freespace($pos) {
		$device = $this->enginelist[$pos];
		return $this->convert_size($device[$this->hdhrkey_freespace]);
	}

	public function get_engine_storageId($pos) {
		$device = $this->enginelist[$pos];
		return $device[$this->hdhrkey_storageID];
	}

	public function get_engine_storageUrl($pos) {
		$device = $this->enginelist[$pos];
		return $device[$this->hdhrkey_storageURL];
	}

	public function get_device_image($pos) {
		$device = $this->hdhrlist[$pos];
		return $this->get_image_url($device[$this->hdhrkey_modelNum]);
	}

	public function get_engine_image($pos) {
		return './images/HDHR-DVR.png';
	}

	private function get_image_url($model) {
		switch ($model) {
			case 'HDTC-2US':
				return './images/HDTC-2US.png';
			case 'HDHR3-CC':
				return './images/HDHR3-CC.png';
			case 'HDHR3-4DC':
				return './images/HDHR3-4DC.png';
			case 'HDHR3-EU':
			case 'HDHR3-US':
			case 'HDHR3-DT':
				return './images/HDHR3-US.png';
			case 'HDHR4-2US':
			case 'HDHR4-2DT':
				return './images/HDHR4-2US.png';
			case 'HDHR5-DT':
			case 'HDHR5-2US':
			case 'HDHR5-4DC':
			case 'HDHR5-4DT':
			case 'HDHR5-4US':
				return './images/HDHR5-US.png';
			case 'HDHR5-6CC':
				return './images/HDHR5-6CC.png';
			case 'TECH4-2DT':
			case 'TECH4-2US':
				return './images/TECH4-2US.png';
			case 'TECH4-8US':
				return './images/TECH4-8US.png';
			case 'TECH5-36CC':
				return './images/TECH5-36CC.png';
			case 'TECH5-16DC':
			case 'TECH5-16DT':
				return './images/TECH5-16DT.png';
			default:
				return './images/HDHR5-US.png';
		}
	}

  private function convert_size($bytes)
  {
  	$bytes = floatval($bytes);
  	$arBytes = array(
  		0 => array(
  			"UNIT" => "TB",
  			"VALUE" => pow(1024, 4)
  		),
  		1 => array(
  			"UNIT" => "GB",
  			"VALUE" => pow(1024, 3)
  		),
  		2 => array(
  			"UNIT" => "MB",
  			"VALUE" => pow(1024, 2)
  			),
  		3 => array(
  			"UNIT" => "KB",
  			"VALUE" => 1024
  		),
  		4 => array(
  			"UNIT" => "B",
  			"VALUE" => 1
  		),
  	);

  	foreach($arBytes as $arItem)
  	{
  		if($bytes >= $arItem["VALUE"])
  		{
  			$result = $bytes / $arItem["VALUE"];
  			$result = strval(round($result, 2))." ".$arItem["UNIT"];
  			break;
  		}
  	}
  	return $result;
  }
}
?>
