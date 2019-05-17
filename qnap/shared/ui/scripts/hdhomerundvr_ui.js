/******************************************************************************
 * @file hdhomerunedvrui.js
 * @description
 * @version 1.0
 * @author Rik Dunphy
 *
 * Copyright 2008 (c) Rik Dunphy
 * You are free to use this file in your own programs, but I do not accept
 * any liability for errors or problems arising from it's use. 
 *****************************************************************************/


function openTab(evt, tabname) {
	var i, tabcontent, tablinks;
	// get elements with class="tabcontent" and hide
	tabcontent = document.getElementsByClassName("tabcontent");
	for (i=-0; i < tabcontent.length; i++) {
		tabcontent[i].style.display = "none";
	}
	
	// get elements with class="tablink" and remove the active
	tablinks = document.getElementsByClassName("tablink");
	for (i=0; i < tablinks.length; i++) {
		tablinks[i].className = tablinks[i].className.replace(" active", "");
	}
	
	// load the page
	if (tabname == 'dashboard_page') {
		openDashboard();
	}
	if (tabname == 'recordings_page') {
		openRecordingsPage();
	}
	if (tabname == 'diagnostics_page') {
		openDiagnosticsPage();
	}

	//show the tablinks
	document.getElementById(tabname).style.display = "block";
	evt.currentTarget.className += " active";
}

/* Set the status message */
function setStatus(msg)
{
	isStatusIdle = 0;
	if(msg == '' || msg == null || msg == undefined)
	{
		isStatusIdle = 1;
		msg = "Idle.";
	}
	document.getElementById('statusMessage').innerHTML = msg;
}

function openLogFile(evt, value)
{
	// get elements with class="tablink" and remove the active
	tablinks = document.getElementsByClassName("loglink");
	for (i=0; i < tablinks.length; i++) {
		tablinks[i].className = tablinks[i].className.replace(" active", "");
	}

	getLogFile(value);
	
	evt.currentTarget.className += " active";
}

function deleteLogFile(value)
{
	rmLogFile(value);
}

function restartService(value)
{
	changeDvrState(value);
}

function updateServer() {
	var port = document.getElementById('Port').value;
	var path = document.getElementById('RecordPath').value;
	var streams = document.getElementById('RecordStreamsMax').value;
	updateServerConfig(port, path, streams);
}

function upgradeServer() {
	//upgradeServerEngine();
	window.alert("*** Coming Soon! ***");
}

function reveal(evt, modal) {
	document.getElementById(modal).style.display = "block";
}

function hideReveal(evt, modal) {
	document.getElementById(modal).style.display = 'none';
}

function deleteRecording(evt, recording_id, reveal) {
	deleteRecordingByID(recording_id,false);
	hideReveal(evt, reveal);
}

function rerecordRecording(evt, recording_id, reveal) {
	deleteRecordingByID(recording_id,true);
	hideReveal(evt, reveal);
}
