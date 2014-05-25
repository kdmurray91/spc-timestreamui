<?php
// incude the template, a (soon to be) barebones xml with some additional extraneos data and structure. 
include "template.php";
// read the xml template as an xml string into a SimpleXMLElement so that we can play around with it.
$xml = new SimpleXMLElement($xmlstr);

// getting json data and decode into php object
$expts_decoded = json_decode(file_get_contents("http://localhost/~stormaes/BOREVITZ/json/expts_pretty.json"));
// Maybe load this once the eperiment has been selected.
$timestreams_decoded = json_decode(file_get_contents("http://localhost/~stormaes/BOREVITZ/json/timestreams_pretty.json"));
// make a filename
$filename = "config/".$expts_decoded[0]->experiments[0]->expt_id.".xml";
// check if the filename exists

	// setting the config name to the expt id (assuming all the config files)
	$xml->globals['config_id'] = $expts_decoded[0]->experiments[0]->expt_id;

	//should functionalise this date string screwery.
	$full_backwards_start_date = $expts_decoded[0]->experiments[0]->start_date;
	$start_day = substr($full_backwards_start_date, 8, 2);
	$start_month = substr($full_backwards_start_date, 5, 2);
	$start_year = substr($full_backwards_start_date, 0 , 4);
	$start_time = "00:00";

	$full_backwards_end_date = $expts_decoded[0]->experiments[0]->end_date;
	$end_day = substr($full_backwards_end_date, 8, 2);
	$end_month = substr($full_backwards_end_date, 5, 2);
	$end_year = substr($full_backwards_end_date, 0 , 4);
	$end_time = "00:00";

	// more date string concat screwery setting up the dates for the globals
	$xml->globals['date_start'] = "$start_day"."/"."$start_month"."/"."$start_year"." "."$start_time"." PM";
	$xml->globals['date_end'] = "$end_day"."/"."$end_month"."/"."$end_year"." "."$end_time"." PM";


	// iterating through the first experiment and then the list of timestreams
	// change this to POST/GET user selection later
	for ($check=0; $check < count($expts_decoded[0]->experiments[0]->timestreams); $check++) { 

		for ($i=0; $i < count($timestreams_decoded); $i++) { 

			// check against the string names of the timestreams to make a list of the streams 
			// available for the experiment
			if ( strcmp($expts_decoded[0]->experiments[0]->timestreams[$check], $timestreams_decoded[$i]->name) ==0) {
				// add new xml child under "components".

				$tc = $xml->components->addChild('timecam');
				// anything under "components" does not show up under "view source" in chrome, 
				// but it is there. 
				
				// "exploding" the stream name for the title
				list($prefixname, $suffixname) = explode('~', $timestreams_decoded[$i]->name);
				// substr the data path, because the timestreamconfig doesnt expect the /cloud/ bit.
				$datapath = substr($timestreams_decoded[$i]->webroot, 6);

				// ALL the attribute setting!
				$tc->addAttribute('id', $timestreams_decoded[$i]->name);
				$tc->addAttribute('image_access_mode', 'TIMESTREAM');
				$tc->addAttribute('title', $prefixname);
				

				// If stream_name and stream_name_hires is important and breaks things, look HERE1 to fix
				$tc->addAttribute('url_image_list', "$datapath"."~640/full/");
				$tc->addAttribute('stream_name', $timestreams_decoded[$i]->name);

				$tc->addAttribute('url_hires', "$datapath"."full/");
				$tc->addAttribute('stream_name_hires', $timestreams_decoded->name."~hires");

				$tc->addAttribute('period', '5 minute');
				$tc->addAttribute('num_images_to_load', 50);
				$tc->addAttribute('utc', 'false');
				$tc->addAttribute('timezone', '0');

				// this here could very well be checked by this php script using getimagesize().
				$tc->addAttribute('width', '480');
				$tc->addAttribute('height', '640');
				$tc->addAttribute('width_hires', '3072');
				$tc->addAttribute('height_hires', '1728');
				$tc->addAttribute('image_type', 'JPG');
				$tc->addAttribute('play_num_images', '100');
				$tc->addAttribute('play_num_images_hires', '50');
				$tc->addAttribute('no_header', 'false');
				$tc->addAttribute('show_timestream_selector', 'false');
					
			}
		}	
	}

	//
	// Todo here:
	// Functionalise the layouts to take a list of timestreams and create the appropriate layout
	// 
	
	$tbmedia = $xml->components->addChild('timebarmedia');
	$tbmedia->addAttribute('id', 'o_timebarmedia');
	$tbmedia->addAttribute('show_timeline', 'false');
	$tbmedia->addAttribute('show_date', 'true');

	// uncomment this next line to save a config file to server (maybe save future configs for later use?).
	// $xml->asXML($filename);
	
	// echo the data. May need to make sure that the corrcect http headers are attached,
	// but it might just need to be raw data.
	echo $xml->asXML();
?>