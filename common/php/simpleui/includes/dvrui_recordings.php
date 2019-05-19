<?php
	require_once("includes/dvrui_hdhrjson.php");
	require_once("includes/dvrui_common.php");
	require_once("includes/dvrui_tz.php");

class DVRUI_Recordings {
	private $recording_Category = 'Category';
	private $recording_ChannelImageURL = 'ChannelImageURL';
	private $recording_ChannelName = 'ChannelName';
	private $recording_ChannelNumber = 'ChannelNumber';
	private $recording_ChannelAffiliate = 'ChannelAffiliate';
	private $recording_EpisodeNumber = 'EpisodeNumber';
	private $recording_ImageURL = 'ImageURL';
	private $recording_EpisodeTitle = 'EpisodeTitle';
	private $recording_OriginalAirDate = 'OriginalAirdate';
	private $recording_FirstAiring = 'FirstAiring';
	private $recording_StartTime = 'StartTime';
	private $recording_EndTime = 'EndTime';
	private $recording_RecordStartTime = 'RecordStartTime';
	private $recording_RecordEndTime = 'RecordEndTime';
	private $recording_RecordSuccess = 'RecordSuccess';
	private $recording_Synopsis = 'Synopsis';
	private $recording_Title = 'Title';
	private $recording_PlayURL = 'PlayURL';
	private $recording_CmdURL = 'CmdURL';
	private $recording_ID = 'RecID';
	
	private $recordings_list = array();
	
	private $recording_cmd_delete = '&cmd=delete&rerecord=0';
	private $recording_cmd_rerecord = '&cmd=delete&rerecord=1';
	

	public function DVRUI_Recordings($hdhr) {
		DVRUI_setTZ();
		$recordings_info = getJsonFromUrl($hdhr->get_storage_url());
		for ($i = 0; $i < count($recordings_info); $i++) {
			$this->processRecordingData($recordings_info[$i]);
		}
	}
	
	private function processRecordingData($recording) {
		$playURL = $recording[$this->recording_PlayURL];
		$cmdURL = $recording[$this->recording_CmdURL];
		$category = '';
		$channelImageURL = '';
		$channelName = '';
		$channelNumber = '';
		$channelAffiliate = '';
		$episodeNumber = 'X';
		$imageURL = '';
		$episodeTitle = 'X';
		$originalAirDate = '';
		$recordStartTime = '';
		$recordEndTime = '';
		$recordSuccess = '';
		$startTime = '';
		$endTime = '';
		$firstAiring = '';
		$synopsis = '';
		$title = '';

		$recID = $this->getRecordingIDfromURL($cmdURL);

		if (array_key_exists($this->recording_Category,$recording)){
			$category = $recording[$this->recording_Category];
		}
		if (array_key_exists($this->recording_ChannelImageURL,$recording)){
			$channelImageURL = $recording[$this->recording_ChannelImageURL];
		}
		if (array_key_exists($this->recording_ChannelName,$recording)){
			$channelName = $recording[$this->recording_ChannelName];
		}
		if (array_key_exists($this->recording_ChannelNumber,$recording)){
			$channelNumber = $recording[$this->recording_ChannelNumber];
		}
		if (array_key_exists($this->recording_ChannelAffiliate,$recording)){
			$channelAffiliate = $recording[$this->recording_ChannelAffiliate];
		}
		if (array_key_exists($this->recording_EpisodeNumber,$recording)){
			$episodeNumber = $recording[$this->recording_EpisodeNumber];
		}
		if (array_key_exists($this->recording_ImageURL,$recording)){
			$imageURL = $recording[$this->recording_ImageURL];
		}
		if (array_key_exists($this->recording_EpisodeTitle,$recording)){
			$episodeTitle = $recording[$this->recording_EpisodeTitle];
		}
		if (array_key_exists($this->recording_OriginalAirDate,$recording)){
			$originalAirDate = $recording[$this->recording_OriginalAirDate];
		}
		if (array_key_exists($this->recording_StartTime,$recording)){
			$startTime = $recording[$this->recording_StartTime];
		}
		if (array_key_exists($this->recording_EndTime,$recording)){
			$endTime = $recording[$this->recording_EndTime];
		}
		if (array_key_exists($this->recording_FirstAiring,$recording)){
			$firstAiring = $recording[$this->recording_FirstAiring];
		}
		if (array_key_exists($this->recording_RecordStartTime,$recording)){
			$recordStartTime = $recording[$this->recording_RecordStartTime];
		}
		if (array_key_exists($this->recording_RecordEndTime,$recording)){
			$recordEndTime = $recording[$this->recording_RecordEndTime];
		}
		if (array_key_exists($this->recording_RecordSuccess,$recording)){
			$recordSuccess = $recording[$this->recording_RecordSuccess];
		}
		if (array_key_exists($this->recording_Synopsis,$recording)){
			$synopsis = $recording[$this->recording_Synopsis];
		}
		if (array_key_exists($this->recording_Title,$recording)){
			$title = $recording[$this->recording_Title];
		}
		
		$this->recordings[] = array(
			$this->recording_PlayURL => $playURL,
			$this->recording_CmdURL => $cmdURL,
			$this->recording_Category => $category,
			$this->recording_ChannelImageURL => $channelImageURL,
			$this->recording_ChannelName => $channelName,
			$this->recording_ChannelNumber => $channelNumber,
			$this->recording_ChannelAffiliate => $channelAffiliate,
			$this->recording_EpisodeNumber => $episodeNumber,
			$this->recording_ImageURL => $imageURL,
			$this->recording_EpisodeTitle => $episodeTitle,
			$this->recording_OriginalAirDate => $originalAirDate,
			$this->recording_StartTime => $startTime,
			$this->recording_EndTime => $endTime,
			$this->recording_FirstAiring => $firstAiring,
			$this->recording_RecordStartTime => $recordStartTime,
			$this->recording_RecordEndTime => $recordEndTime,
			$this->recording_RecordSuccess => $recordSuccess,
			$this->recording_Synopsis => $synopsis,
			$this->recording_Title => $title,
			$this->recording_ID => $recID);
	}
	
	private function getRecordingIDfromURL($url) {
		$pattern = "/.+id=(\S{8})$/";
		if (preg_match($pattern, $url, $matches)) {
			// First match is the URL - so need to return the 2nd
			return $matches[1];
		}
		return '00000000';
	}

	private function getCmdFromID($id) {
		for ($i = 0; $i < count($this->recordings); $i++) {
			if($this->recordings[$i][$this->recording_ID] == $id) {
				return $this->recordings[$i][$this->recording_CmdURL];
			}
		}
		return '';
	}

	private function removeID($id) {
		for ($i = 0; $i < count($this->recordings); $i++) {
			if($this->recordings[$i][$this->recording_ID] == $id) {
				array_splice($this->recordings,$i,1);
			}
		}
		return '';
	}

	public function deleteRecording($id,$rerecord) {
		error_log('Deleting Recording: ' . $id);
		$url = $this->getCmdFromID($id);
		$fields ='';
		if ($rerecord == 'true') {
			$url .= $this->recording_cmd_rerecord;
		} else {
			$url .= $this->recording_cmd_delete;
		}
		error_log('Sending CMD ' . $url . ' with params '. $fields);
		$response = postToUrl($url,$fields);
		error_log('DVR responded ' . print_r($response,true));
		$this->removeID($id);
		/* ignore the response at this time */
		echo('Removed ' . $id . ' : ' . $response);
	}
	
	public function getRecordingCount() {
		return count($this->recordings);
	}

	public function getRecordingID($pos) {
		return $this->recordings[$pos][$this->recording_ID];
	}


	public function get_PlayURL($pos) {
		return $this->recordings[$pos][$this->recording_PlayURL];
	}

	public function get_CmdURL($pos) {
		return $this->recordings[$pos][$this->recording_CmdURL];
	}

	public function getRecordingImage($pos) {
		return $this->recordings[$pos][$this->recording_ImageURL];
	}

	public function getCategory($pos) {
		return $this->recordings[$pos][$this->recording_Category];
	}

	public function getChannelImageURL($pos) {
		return $this->recordings[$pos][$this->recording_ChannelImageURL];
	}

	public function getChannelName($pos) {
		return $this->recordings[$pos][$this->recording_ChannelName];
	}

	public function getChannelNumber($pos) {
		return $this->recordings[$pos][$this->recording_ChannelNumber];
	}

	public function getChannelAffiliate($pos) {
		return $this->recordings[$pos][$this->recording_ChannelAffiliate];
	}

	public function getEpisodeNumber($pos) {
		if ($this->recordings[$pos][$this->recording_EpisodeNumber] == 'X') {
			return $this->getShortOriginalAirDate($pos);
		} else {
			return $this->recordings[$pos][$this->recording_EpisodeNumber];
		}
	}

	public function getEpisodeTitle($pos) {
		if ($this->recordings[$pos][$this->recording_EpisodeTitle] == 'X') {
			return 'No Episode Title Available';
		} else {
			return $this->recordings[$pos][$this->recording_EpisodeTitle];
		}
	}

	public function isRepeat($pos) {
		if ($this->recordings[$pos][$this->recording_FirstAiring] == '1'){
			return false;
		} else {
			return true;
		}
	}
	
	public function getOriginalAirDate($pos) {
		if ($this->recordings[$pos][$this->recording_OriginalAirDate] == '') {
			return gmdate('D M/d Y',0);
		} else {
			return gmdate('D M/d Y',$this->recordings[$pos][$this->recording_OriginalAirDate]);
		}
	}

	public function getShortOriginalAirDate($pos) {
		if ($this->recordings[$pos][$this->recording_OriginalAirDate] == '') {
			return gmdate('m/d/y',0);
		} else {
			return gmdate('m/d/y',$this->recordings[$pos][$this->recording_OriginalAirDate]);
		}
	}

	public function getStartTime($pos) {
		return date('D M/d Y @ g:ia',$this->recordings[$pos][$this->recording_StartTime]);
	}

	public function getEndTime($pos) {
		return date('D M/d Y @ g:ia',$this->recordings[$pos][$this->recording_EndTime]);
	}

	public function getRecordStartTime($pos) {
		return date('D M/d Y @ g:ia',$this->recordings[$pos][$this->recording_RecordStartTime]);
	}

	public function getRecordEndTime($pos) {
		return date('D M/d Y @ g:ia',$this->recordings[$pos][$this->recording_RecordEndTime]);
	}

	public function getRecordSuccess($pos) {
		return $this->recordings[$pos][$this->recording_RecordSuccess];
	}

	public function getSynopsis($pos) {
		return $this->recordings[$pos][$this->recording_Synopsis];
	}

	public function getTitle($pos) {
		return $this->recordings[$pos][$this->recording_Title];
	}

	public function getDeleteCmdURL($pos) {
		return $this->recordings[$pos][$this->recording_CmdURL] . '&cmd=delete&rerecord=0';
	}

	public function getRerecordCmdURL($pos) {
		return $this->recordings[$pos][$this->recording_CmdURL] . '&cmd=delete&rerecord=1';
	}
	
}
?>
