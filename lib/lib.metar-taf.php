<?php

/**
 * lib.metar-taf.php by Bruno Adele (bruno.adele@jesuislibre.org) (2010) in GPL Licence
 *
 * The library Metar-Taf is the fatest function for decode the Metar or Taf Bulletin
 * It's inspired from GetWx (Get Weather) made by Mark Woodward woody.cowpi.com/phpscripts/
 *
 *
 * Cut in code section
 * Add NOPARSE column
 * Add NSC token for cloud
 * Add NCD toker for cloud
 * Add NVD token visibility
 * Add ///|CB|TCU token for cloud
 * Add // for weather condition
 * Add Recent condition
 * Add Wind Variability
 * Add some converter function (speed unit, wind force, etc ...)
 * Add I18n support
 * Add Maximal and Minimal temperature
 * Add WindShear
 * Add Probality
 * Add Vertical Visibility
 * Convert to class
 */


/*
 * default Unit (Km for speed, km for length, °C for temperature)
*/

require_once(dirname(__FILE__).'/lib.unit-convert.php');

class metar_taf {

    private $wxInfo = array();

    private $mode = "";

    private $timestamp = 0;

    private $message = "";

    private $functions = array();

    private $tend = 0;

    private $current_group = 0;

    private $current_group_text = "";

    private $current_ptr = 0;

    private $max_date_end = 0;

    function metar_taf() {
        $this->max_date_end  = 0;
    }


    public function decode_metar($timestamp,$metar) {
        $this->functions = array('get_station','get_time','get_station_type','get_wind','get_var_wind','get_visibility','get_runway','get_conditions','get_cloud_cover','get_windshear','get_temperature','get_barometer','get_recent_conditions','get_tend','get_remark');
        $this->mode = 'METAR';
        $this->timestamp = $timestamp;
        $this->message = $metar;
        $this->decode();
    }

    public function decode_taf($timestamp,$taf) {
        $this->functions = array('get_error','get_station','get_time','get_time_range','get_wind','get_var_wind','get_visibility','get_conditions','get_cloud_cover','get_barometer','get_windshear','get_temp_maxmin', 'get_probality','get_tend','get_remark','get_end');
        $this->mode = 'TAF';
        $this->timestamp = $timestamp;
        $this->message = $taf;
        $this->decode();
    }

    protected function decode() {

//print "METAR/TAF: $metar<br>\n";

        if ($this->message != '') {
            $this->message = preg_replace('/\s\s+/', ' ', $this->message);

            $metarParts = explode(' ',$this->message);

            $this->current_ptr = 0;
            $this->current_group = 0;
            $this->tend = 0;
            $this->wxInfo["CODE_$this->mode"] = $this->message;

            $countloop=0;
            while ($this->current_group < count($this->functions) && $this->current_ptr<count($metarParts) && $countloop<1000) { // limit loop
                $this->current_group_text = $metarParts[$this->current_ptr];
                //echo "TEND[$this->tend] Parsing '$this->current_group_text' with ".$functions[$group]."(gid=$group,ptr=$this->current_ptr)<br>\n";
                //$this->functions[$this->current_group]();  // $groupName is a function variable
                //call_user_func('$this->'.$this->functions[$this->current_group].'()');
                call_user_func( array( &$this, $this->functions[$this->current_group] ));
                $countloop++;
            }

            if ($countloop<1000) {
                while ($this->current_ptr<count($metarParts)) {
                    $this->current_group_text = $metarParts[$this->current_ptr];
                    $this->varConcatenate($this->wxInfo, 'IGNORES', ' '.$this->current_group_text);
//$noparsed .= $metarParts[$this->current_ptr]. ' ';
                    $this->current_ptr++;
                }
            } else {
                $this->wxInfo['ERROR'] = 'Loop Decode';
            }


//$this->wxInfo['CODE_NOPARSED'] = trim($noparsed);
        }
        else $this->wxInfo['ERROR'] = 'Data not available';

    }

    public function getResult() {
        return $this->wxInfo;
    }

    protected function get_error( ) {
// Because some station add Ammendment word or another,
//  i store only the latest word with 3 letters

        if (strlen($this->current_group_text) != 4) {
            if (strlen($this->current_group_text) == 3) {
                if ($this->current_group_text!='TAF') {
                    $this->wxInfo['CODE_ERROR'] =$this->current_group_text;
                }
                $this->current_ptr++;
            }
            else {
                $this->current_ptr++;
            }
        } else {
            $this->current_group++;
        }
    }


    protected function get_station( ) {
// Ignore station code. Script assumes this matches requesting $station.
// This function is never called. It is here for completeness of documentation.

        if (strlen($this->current_group_text) == 4) {
            $this->wxInfo['STATION'] = $this->current_group_text;
            $this->current_ptr++;
        }
        $this->current_group++;
    }

    protected function get_time() {
// Ignore observation time. This information is found in the first line of the NWS file.
// Format is ddhhmmZ where dd = day, hh = hours, mm = minutes in UTC time.
        if (preg_match('#^([0-9]{2})([0-9]{2})([0-9]{2})Z$#',$this->current_group_text,$pieces)) {

//    date_default_timezone_set('UTC');
            $month_start = date('m',$this->timestamp);
            $year_start = date('Y',$this->timestamp);

            $hour = $pieces[2];
            $minute = $pieces[3];
            $day = $pieces[1];

            $this->wxInfo['ITEMS'][$this->tend]['CODE_TIME'] = $this->current_group_text;
//        $this->wxInfo['ITEMS'][$this->tend]['DATE_DAY'] = $pieces[1];
//        $this->wxInfo['ITEMS'][$this->tend]['DATE_TIME'] = $pieces[2].':'.$pieces[3];

            $this->wxInfo['ITEMS'][$this->tend]['DATE_TIMESTAMP'] = mktime( $hour, $minute, 0, $month_start, $day, $year_start);

            $this->current_ptr++;
        }
        $this->current_group++;
    }

    protected function get_time_range( ) {
// Ignore observation time. This information is found in the first line of the NWS file.
// Format is ddhhmmZ where dd = day, hh = hours, mm = minutes in UTC time.

        $found = 0;

//    date_default_timezone_set('UTC');

        $month_timestamp = date('m',$this->timestamp);
        $day_timestamp = date('d',$this->timestamp);
        $year_timestamp = date('Y',$this->timestamp);
        $hour_timestamp = date('H',$this->timestamp);
        $min_timestamp = date('i',$this->timestamp);

        $current_start_hour = $hour_timestamp;
        $current_start_min = $min_timestamp;
        $current_start_month = $month_timestamp;
        $current_start_year = $year_timestamp;

        $current_end_hour = $hour_timestamp;
        $current_end_min = $min_timestamp;
        $current_end_month = $month_timestamp;
        $current_end_year = $year_timestamp;
//    $day_start = $day_timestamp;
//    $day_end = $day_timestamp;


//print "$this->current_group_text\n";

        if (preg_match('#^FM([0-9]{2})([0-9]{2})([0-9]{2})$#',$this->current_group_text,$pieces)) {
            $this->wxInfo['ITEMS'][$this->tend]['CODE_DATE_RANGE'] = $this->current_group_text;

            $get_start_hour = $pieces[2];
            $get_start_minute = $pieces[3];
            $get_start_day = $pieces[1];

            $get_end_day = date("d",$this->max_date_end);
            $get_end_hour = date("G",$this->max_date_end);
            $get_end_minute = date("i",$this->max_date_end);

            $found = 1;
            $this->current_ptr++;
        } else if (preg_match('#^([0-9]{2})([0-9]{2})([0-9]{2})$#',$this->current_group_text,$pieces)) {
            $this->wxInfo['ITEMS'][$this->tend]['CODE_DATE_RANGE'] = $this->current_group_text;

            $get_start_hour = $pieces[2];
            $get_start_minute = $pieces[3];
            $get_start_day = $pieces[1];

            $get_end_day = date("d",$this->max_date_end);
            $get_end_hour = date("G",$this->max_date_end);

            if ($day_timestamp>$start_day) {
                $current_start_month++;
                if ($current_start_month>12) {
                    $current_start_month=1;
                    $current_start_year++;
                }
            }


            $found = 1;
            $this->current_ptr++;
        } else if (preg_match('#^([0-9]{2})([0-9]{2})/([0-9]{2})([0-9]{2})$#',$this->current_group_text,$pieces)) {
            $get_start_day = $pieces[1];
            $get_start_hour = $pieces[2];
            $get_start_minute = 0;
            $get_end_day = $pieces[3];
            $get_end_hour = $pieces[4];
            $get_end_minute = 0;

            $this->wxInfo['ITEMS'][$this->tend]['CODE_DATE_RANGE'] = $this->current_group_text;

            $found = 1;
            $this->current_ptr++;
        }
        if ($found==1) {


//        print "get_start_day: $get_start_day<br>";
//        print "get_start_hour: $get_start_hour<br>";
//        print "get_end_day: $get_end_day<br>";
//        print "get_end_time: $get_end_hour<br>";

            if ($day_timestamp>$get_start_day) {
                $current_start_month++;
                if ($current_start_month>12) {
                    $current_start_month=1;
                    $current_start_year++;
                }
            }

            if ($day_timestamp>$get_end_day) {
                $current_end_month++;
                if ($current_end_month>12) {
                    $current_end_month=1;
                    $current_end_year++;
                }
            }

            if ($get_start_hour==24) {
                $get_start_hour=23;
                $get_start_minute=59;
            }

            if ($get_end_hour==24) {
                $get_end_hour=23;
                $get_end_minute=59;
            }


            $this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_START_TIMESTAMP'] = mktime( $get_start_hour, $get_start_minute, 0, $current_start_month, $get_start_day, $current_start_year);
            $this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_END_TIMESTAMP'] = mktime( $get_end_hour, $get_end_minute, 0, $current_end_month, $get_end_day, $current_end_year);


// Calcule la date
//            $timestamp = strtotime($timestamp);
//            $month = date('m',$timestamp);
//            $startday = date('d',$timestamp);
//            $year = date('Y',$timestamp);

//        $startday = abs($this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_STARTDAY']-$startday);
//        $endday = abs($this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_ENDDAY']-$startday);

            $this->max_date_end = max($this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_END_TIMESTAMP'],$this->max_date_end);

//        $this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_START_DATETIME'] = date('Y-m-d H:i:s', $time_start);
//        $this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_END_DATETIME'] = date('Y-m-d H:i:s', $time_end);
//            print "DATE_RANGE_END_TIMESTAMP: ".$this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_END_TIMESTAMP']." DATE_RANGE_END_TEXT: ".    date("Y-m-d H:i",$this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_END_TIMESTAMP'])."\n";

//print "debut: ".$this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_START_TIMESTAMP']."\n";
//print "fin: ".$this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_END_TIMESTAMP']."\n";

            /*
        print "before ".date('Y-m-d H:i:s',$timestamp )."\n";
        print "$day\n";
        print "startday ".$startday."\n";
        print "endday ".$endday."\n";
        print "result start ".$this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_START_DATETIME']."\n";
        print "result end   ".$this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_END_DATETIME']."\n";
        print "\n\n";
        exit;
            */


// $this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_START_DATETIME'] = '0000-01-'.$this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_STARTDAY'].' '.$this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_STARTTIME'].':00';
//$this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_END_DATETIME'] = '0000-01-'.$this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_ENDDAY'].' '.$this->wxInfo['ITEMS'][$this->tend]['DATE_RANGE_ENDTIME'].':00';
        }

        $this->current_group++;
    }


    protected function get_station_type( ) {
// Ignore station type if present.
        if ($this->current_group_text == 'AUTO' || $this->current_group_text == 'COR') {
            $this->wxInfo['CODE_STATION_TYPE'] = $this->current_group_text;
            $this->varConcatenate($this->wxInfo, 'IGNORES', $this->current_group_text);
            $this->current_ptr++;
        }
        $this->current_group++;
    }



    protected function get_wind( ) {
// Decodes wind direction and speed information.
// Format is dddssKT where ddd = degrees from North, ss = speed, KT for knots,
// or dddssGggKT where G stands for gust and gg = gust speed. (ss or gg can be a 3-digit number.)
// KT can be replaced with MPH for meters per second or KMH for kilometers per hour.

        if (preg_match('#^([0-9G]{5,10}|VRB[0-9]{2,3})(KT|MPS|KMH)$#',$this->current_group_text,$pieces)) {
            $this->wxInfo['ITEMS'][$this->tend]['CODE_WIND'] = $this->current_group_text;
            $this->current_group_text = $pieces[1];
            $unit = $pieces[2];
            if ($this->current_group_text == '00000') {
                $this->wxInfo['ITEMS'][$this->tend]['WIND_SPEED'] = '0';  // no wind
            }
            else {
                preg_match('#([0-9]{3}|VRB)([0-9]{2,3})G?([0-9]{2,3})?#',$this->current_group_text,$pieces);
                if ($pieces[1] == 'VRB') {
                    $direction = 'VAR';
                    $this->wxInfo['ITEMS'][$this->tend]['WIND_DIRECTION_TEXT'] = $direction;
                }
                else {
                    $angle = (integer) $pieces[1];
                    $compass = array('N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW');
                    $direction = $compass[round($angle / 22.5) % 16];
                    $this->wxInfo['ITEMS'][$this->tend]['WIND_DIRECTION'] = round($angle / 22.5)*22.5;
                    $this->wxInfo['ITEMS'][$this->tend]['WIND_DIRECTION_TEXT'] = $direction;
//                $this->wxInfo['WIND_DIRECTION_TEXT'] =round($angle / 22.5);
                }

                if (isset($pieces[3]) && trim($pieces[3])!='') $this->wxInfo['ITEMS'][$this->tend]['WIND_GUST'] = $this->convertSpeed($pieces[3], $unit);
                $this->wxInfo['ITEMS'][$this->tend]['WIND_SPEED'] = $this->convertSpeed($pieces[2], $unit);
            }
            $this->current_ptr++;
        }
        $this->current_group++;
    }

    protected function get_var_wind( ) {
// Ignore variable wind direction information if present.
// Format is fffVttt where V stands for varies from fff degrees to ttt degrees.
        if (preg_match('#([0-9]{3})V([0-9]{3})#',$this->current_group_text,$pieces)) {
            $this->wxInfo['ITEMS'][$this->tend]['CODE_VAR_WIND'] = $this->current_group_text;
            $this->wxInfo['ITEMS'][$this->tend]['WIND_VARIABILITY_FROM'] = $pieces[1];
            $this->wxInfo['ITEMS'][$this->tend]['WIND_VARIABILITY_TO'] = $pieces[2];
            $this->current_ptr++;
        }
        $this->current_group++;
    }

    protected function get_visibility( ) {
// Decodes visibility information. This function will be called a second time
// if visibility is limited to an integer mile plus a fraction part.
// Format is mmSM for mm = statute miles, or m n/dSM for m = mile and n/d = fraction of a mile,
// or just a 4-digit number nnnn (with leading zeros) for nnnn = meters.

        static $integerMile = '';
        $this->wxInfo['ITEMS'][$this->tend]['CODE_HVISIBILITY'] = $this->current_group_text;
        if (strlen($this->current_group_text) == 1) {  // visibility is limited to a whole mile plus a fraction part
            $integerMile = $this->current_group_text . ' ';
            $visibility_code = $this->current_group_text;
            $this->current_ptr++;
        }
        elseif (substr($this->current_group_text,-2) == 'SM') {  // visibility is in miles
            $this->current_group_text = substr($this->current_group_text,0,strlen($this->current_group_text)-2);
            if (substr($this->current_group_text,0,1) == 'M') {
//$prefix = 'less than ';
                $this->current_group_text = substr($this->current_group_text, 1);
                $this->wxInfo['ITEMS'][$this->tend]['HVISIBILITY_QUALIFIER'] = -1;
            } else if (substr($this->current_group_text,0,1) == 'P') {
//$prefix = 'plus than ';
                $this->current_group_text = substr($this->current_group_text, 1);
                $this->wxInfo['ITEMS'][$this->tend]['HVISIBILITY_QUALIFIER'] = 1;
            } else {
//$prefix = '';
                $this->wxInfo['ITEMS'][$this->tend]['HVISIBILITY_QUALIFIER'] = 0;
            }
            if (($integerMile == '' && preg_match('#[/]#',$this->current_group_text,$pieces)) || $this->current_group_text == '1') $unit = ' mile';
            else $unit = ' miles';

            $this->wxInfo['ITEMS'][$this->tend]['HVISIBILITY'] = convertLength($this->current_group_text, 'Mile');
            $this->current_ptr++;
            $this->current_group++;
        }
        elseif (substr($this->current_group_text,-2) == 'KM') {  // unknown (Reported by NFFN in Fiji)
            $this->current_ptr++;
            $this->current_group++;
        }
        elseif (preg_match('#^([0-9]{4})(NDV)?$#',$this->current_group_text,$pieces)) {  // visibility is in meters
            if ($this->current_group_text == '9999') {
                $this->wxInfo['ITEMS'][$this->tend]['HVISIBILITY'] = convertLength(10000, 'Meter');
                $this->wxInfo['ITEMS'][$this->tend]['HVISIBILITY_QUALIFIER'] = 1;
            } else if ($this->current_group_text == '0000') {
                $this->wxInfo['ITEMS'][$this->tend]['HVISIBILITY'] = convertLength(50, 'Meter');
                $this->wxInfo['ITEMS'][$this->tend]['HVISIBILITY_QUALIFIER'] = -1;
            } else {
                $distance = convertLength($this->current_group_text, 'Meter');
                $this->wxInfo['ITEMS'][$this->tend]['HVISIBILITY'] =$distance;
                $this->wxInfo['ITEMS'][$this->tend]['HVISIBILITY_QUALIFIER'] = 0;
            }
            $this->current_ptr++;
            $this->current_group++;
        }
        elseif ($this->current_group_text == 'CAVOK') {  // good weather
            $this->wxInfo['ITEMS'][$this->tend]['HVISIBILITY'] = convertLength(10000, 'Meter');
            $this->wxInfo['ITEMS'][$this->tend]['HVISIBILITY_QUALIFIER'] = 1;
//$this->wxInfo['ITEMS'][$this->tend]['CONDITIONS'] = '';
            $this->wxInfo['ITEMS'][$this->tend]['CLOUDS'] = $this->get_i18nClouds('CLR');
            $this->wxInfo['ITEMS'][$this->tend]['CODE_HVISIBILITY'] = $this->current_group_text;
            $this->current_ptr++;
            $this->current_group++;
            if ($this->mode=='METAR') {
                $this->current_group += 3;  // can skip the next 3 groups
            }
        }
        else {
            $this->wxInfo['ITEMS'][$this->tend]['CODE_HVISIBILITY'] = NULL;
            $this->current_group++;
        }
    }

    protected function get_runway() {
// Ignore runway information if present. Maybe called a second time.
// Format is Rrrr/vvvvFT where rrr = runway number and vvvv = visibility in feet.
        if (substr($this->current_group_text,0,1) == 'R') {
            $this->varConcatenate($this->wxInfo,'CODE_RUNWAY', $this->current_group_text);
            $this->varConcatenate($this->wxInfo, 'IGNORES', $this->current_group_text);

            $this->current_ptr++;
        }
        else $this->current_group++;
    }

    protected function get_conditions( ) {
// Decodes current weather conditions. This function maybe called several times
// to decode all conditions. To learn more about weather condition codes, visit section
// 12.6.8 - Present Weather Group of the Federal Meteorological Handbook No. 1 at
// www.nws.noaa.gov/oso/oso1/oso12/fmh1/fmh1ch12.htm
        if (preg_match('#^(-|\+|VC)?(NSW|TS|SH|FZ|BL|DR|MI|BC|PR|RA|DZ|SN|SG|GR|GS|PE|IC|UP|BR|FG|FU|VA|DU|SA|HZ|PY|PO|SQ|FC|SS|DS|WS//)+$#',$this->current_group_text,$pieces)) {
            $this->varConcatenate($this->wxInfo['ITEMS'][$this->tend],'CODE_CONDITIONS', $this->current_group_text);
            if (!isset($this->wxInfo['ITEMS'][$this->tend]['CONDITIONS'])) {
//$this->wxInfo['ITEMS'][$this->tend]['CONDITIONS'] = '';
                $join = '';
            }
            else {
                $join = ', ';
            }
            if (substr($this->current_group_text,0,1) == '-') {
                $prefix = $this->get_i18nCondition('-');
                $this->current_group_text = substr($this->current_group_text,1);
            }
            elseif (substr($this->current_group_text,0,1) == '+') {
                $prefix = $this->get_i18nCondition('+');
                $this->current_group_text = substr($this->current_group_text,1);
            }
            else $prefix = '';  // moderate conditions have no descriptor
            while ($code = substr($this->current_group_text,0,2)) {
                if (!isset($this->wxInfo['ITEMS'][$this->tend]['CONDITIONS'])) {
                    $this->wxInfo['ITEMS'][$this->tend]['CONDITIONS'] = '';
                }
                $this->wxInfo['ITEMS'][$this->tend]['CONDITIONS'] .= $join . $this->get_i18nCondition($code) . ' ';
                $this->current_group_text = substr($this->current_group_text,2);
            }
            if (strlen($prefix)>0) {
                $this->wxInfo['ITEMS'][$this->tend]['CONDITIONS'] .= $prefix ;
            }
            $this->current_ptr++;
        }
        else {
            if (isset($this->wxInfo['ITEMS'][$this->tend]['CONDITIONS'])) {
//$this->wxInfo['ITEMS'][$this->tend]['CONDITIONS'] = '';
                $this->wxInfo['ITEMS'][$this->tend]['CONDITIONS'] = trim($this->wxInfo['ITEMS'][$this->tend]['CONDITIONS']);
                if ($this->wxInfo['ITEMS'][$this->tend]['CONDITIONS']=='') unset($this->wxInfo['ITEMS'][$this->tend]['CONDITIONS']);
            }
            $this->current_group++;
        }
    }

    protected function get_windshear( ) {
        if (preg_match('#^WS#',$this->current_group_text,$pieces)) {
            $this->varConcatenate($this->wxInfo,'IGNORES', $this->current_group_text);
            $this->current_ptr++;
        }
        $this->current_group++;
    }

    protected function get_remark( ) {
        if (preg_match('#^RMK#',$this->current_group_text,$pieces)) {
            $this->wxInfo['REMARK'] = '';
            $this->current_ptr++;
        } else if (isset($this->wxInfo['REMARK']) ) {
            $this->varConcatenate($this->wxInfo,'REMARK', $this->current_group_text);
            $this->current_ptr++;
        }
        else {
            $this->current_group++;
        }
    }

    protected function get_end( ) {
        $this->current_group = 1;
        $this->current_ptr++;

    }


    protected function get_tend( ) {
        if ($this->current_group_text=='NOSIG') {
            $this->wxInfo['ITEMS'][$this->tend]['CODE_TEND'] = $this->current_group_text;
            $this->wxInfo['ITEMS'][$this->tend]['TEND'] = "aucun changement significatif n'est a prevoir";
            $this->tend++;
            $this->current_ptr++;
            $this->current_group++;
            return;
        }



        if (substr($this->current_group_text,0,2)=='FM') {
            $this->tend++;
            if ($this->mode=='METAR') {
                $this->current_group=1;
            } else {
                $this->current_group=3;

            }
            return;
        }

        if ($this->current_group_text=='TEMPO' || $this->current_group_text=='BECMG' ) {
            $this->tend++;
            $this->current_ptr++;
            $this->wxInfo['ITEMS'][$this->tend]['CODE_TEND'] = $this->current_group_text;
            if ($this->mode=='METAR') {
                $this->current_group=1;
            } else {
                $this->current_group=3;

            }
            return;
        }

        $this->current_group++;
    }


    protected function get_recent_conditions( ) {
        if (substr($this->current_group_text,0,2)=='RE') {
            $this->current_group_text=substr($this->current_group_text,-2);
            $this->wxInfo['ITEMS'][$this->tend]['CONDITIONS'] .= $this->get_i18nCondition('Recent');
            $this->get_conditions();
            $this->current_ptr++;
            $this->current_group++;
        } else {
            $this->current_group++;
        }

    }


    protected function get_cloud_cover( ) {
// Decodes cloud cover information. This function maybe called several times
// to decode all cloud layer observations. Only the last layer is saved.
// Format is SKC or CLR for clear skies, or cccnnn where ccc = 3-letter code and
// nnn = altitude of cloud layer in hundreds of feet. 'VV' seems to be used for
// very low cloud layers. (Other conversion factor: 1 m = 3.28084 ft)

// Ignore this group
        if (preg_match('#(PROB[0-9]{2})|(TEMPO)|(BECMG)#',$this->current_group_text,$pieces)
                ||preg_match('#^(TX|TN)[0-9]{2}/[0-9]{4}Z#',$this->current_group_text,$pieces)
                ||preg_match('#^FM[0-9]{6}#',$this->current_group_text,$pieces)
                ||preg_match('#^WS[0-9]{3}#',$this->current_group_text,$pieces)
                ||preg_match('#^QNH[0-9]{4}INS$#',$this->current_group_text,$pieces)) {
            $this->current_group++;
            return;
        }


// Clear sky or particular sky
        if ($this->current_group_text == 'SKC' || $this->current_group_text == 'CLR' || $this->current_group_text == 'NSC'||$this->current_group_text == 'NCD') {
            $this->wxInfo['ITEMS'][$this->tend]['CLOUDS'] = $this->get_i18nClouds($this->current_group_text);
            $this->wxInfo['ITEMS'][$this->tend]['CODE_CLOUDS'] = $this->current_group_text;
            $this->current_ptr++;
            $this->current_group++;
            return;
        }


        if (preg_match('#^(//////(CB|TCU)?)#',$this->current_group_text,$pieces)) {
            $this->varConcatenate($this->wxInfo['ITEMS'][$this->tend],'CODE_CLOUDS', $this->current_group_text);
            $this->current_ptr++;
            return;
        }

        if (preg_match('#^([A-Z]{2,3})([0-9]{3})?(///)?#',$this->current_group_text,$pieces)) {  // codes for CB and TCU are ignored
            $this->varConcatenate($this->wxInfo['ITEMS'][$this->tend],'CODE_CLOUDS', $this->current_group_text);
            if (isset($this->wxInfo['ITEMS'][$this->tend]['CLOUDS'])) {
                $this->wxInfo['ITEMS'][$this->tend]['CLOUDS'] .= ', ';
            } else {
                $this->wxInfo['ITEMS'][$this->tend]['CLOUDS'] = '';
            }
            $this->wxInfo['ITEMS'][$this->tend]['CLOUDS'] .= $this->get_i18nClouds($pieces[1]);
            $this->wxInfo['ITEMS'][$this->tend]['CLOUDS'] .= '('.convertLength((integer) 100 * $pieces[2], 'Meter').' m)';
            if ($pieces[1] == 'VV') {
                $altitude = (integer) 100 * $pieces[2];  // units are feet
                $this->wxInfo['ITEMS'][$this->tend]['VVISIBILITY'] = $altitude;
            }
            $this->current_ptr++;
            return;
        }


        if (isset($this->wxInfo['ITEMS'][$this->tend]['CODE_CLOUDS'])) {
            $code_clouds = explode(' ',$this->wxInfo['ITEMS'][$this->tend]['CODE_CLOUDS']);
            if (isset($code_clouds[0])) $this->wxInfo['ITEMS'][$this->tend]['CODE_CLOUD1'] = $code_clouds[0];
            if (isset($code_clouds[1])) $this->wxInfo['ITEMS'][$this->tend]['CODE_CLOUD2'] = $code_clouds[1];
            if (isset($code_clouds[2])) $this->wxInfo['ITEMS'][$this->tend]['CODE_CLOUD3'] = $code_clouds[2];
            if (isset($code_clouds[3])) $this->wxInfo['ITEMS'][$this->tend]['CODE_CLOUD4'] = $code_clouds[3];
            if (isset($code_clouds[4])) $this->wxInfo['ITEMS'][$this->tend]['CODE_CLOUD5'] = $code_clouds[4];
        }
        $this->current_group++;
        return;

    }

    protected function get_heat_index($tempF, $rh) {
// Calculate Heat Index based on temperature in F and relative humidity (65 = 65%)
        if ($tempF > 79 && $rh > 39) {
            $hiF = -42.379 + 2.04901523 * $tempF + 10.14333127 * $rh - 0.22475541 * $tempF * $rh;
            $hiF += -0.00683783 * pow($tempF, 2) - 0.05481717 * pow($rh, 2);
            $hiF += 0.00122874 * pow($tempF, 2) * $rh + 0.00085282 * $tempF * pow($rh, 2);
            $hiF += -0.00000199 * pow($tempF, 2) * pow($rh, 2);
            $hiF = round($hiF);
            $hiC = round(($hiF - 32) / 1.8);
            $this->wxInfo['ITEMS'][$this->tend]['HEATINDEXC'] = $hiC;
            $this->wxInfo['ITEMS'][$this->tend]['HEATINDEXF'] = $hiF;
        }
    }


    protected function get_wind_chill($tempF) {
        $unit_wind_speed = get_unitWindSpeed();
        $windspeedunit = get_unitWindConvert();

// Calculate Wind Chill Temperature based on temperature in F and
// wind speed in miles per hour
        if ($tempF < 51 && $this->wxInfo['ITEMS'][$this->tend]['WIND_SPEED'] != 0) {
            $windspeed = $this->wxInfo['ITEMS'][$this->tend]['WIND_SPEED']*$unit_wind_speed[$windspeedunit];
            if ($windspeed > 3) {
                $chillF = 35.74 + 0.6215 * $tempF - 35.75 * pow($windspeed, 0.16) + 0.4275 * $tempF * pow($windspeed, 0.16);
                $chillF = round($chillF);
                $chillC = convertTempToC($chillF);
//            $this->wxInfo['ITEMS'][$this->tend]['WINDCHILL_F'] = $chillF;
                $this->wxInfo['ITEMS'][$this->tend]['WINDCHILL_C'] = $chillC;
            }
        }
    }

    protected function get_temp_maxmin( ) {
        if (preg_match('#^TX(M?[0-9]{2})/([0-9]{2})([0-9]{2})Z#',$this->current_group_text,$pieces)) {
            $tempC = (integer) strtr($pieces[1], 'M', '-');
            $tempF = round(1.8 * $tempC + 32);
            $this->wxInfo['ITEMS'][$this->tend]['MAX_TEMPERATURE_C'] = $tempC;
//        $this->wxInfo['ITEMS'][$this->tend]['MAX_TEMPERATURE_F'] = $tempF;
            $this->wxInfo['ITEMS'][$this->tend]['MAX_TEMPERATURE_DAY'] = (int)$pieces[2];
            $this->wxInfo['ITEMS'][$this->tend]['MAX_TEMPERATURE_TIME'] = (int)$pieces[3];
//$this->wxInfo['ITEMS'][$this->tend]['MAX_WINDCHILL_C'] = $tempC;
//$this->wxInfo['ITEMS'][$this->tend]['MAX_WINDCHILL_F'] = $tempF;
//get_wind_chill($tempF, $this->wxInfo);
            if (strlen($pieces[2]) != 0 && $pieces[2] != 'XX') {
                $dewC = (integer) strtr($pieces[2], 'M', '-');
                $dewF = convertTempToF($dewC);
                $this->wxInfo['ITEMS'][$this->tend]['MAX_DEWTEMPERATURE_C'] = $dewC;
//            $this->wxInfo['ITEMS'][$this->tend]['MAX_DEWTEMPERATURE_F'] = $dewF;
                $rh = round(100 * pow((112 - (0.1 * $tempC) + $dewC) / (112 + (0.9 * $tempC)), 8),1);
                $this->wxInfo['ITEMS'][$this->tend]['MAX_HUMIDITY'] = $rh;
//get_heat_index($tempF, $rh, $this->wxInfo);
            }
            $this->wxInfo['ITEMS'][$this->tend]['CODE_MAX_TEMPERATURE'] = $this->current_group_text;
            $this->current_ptr++;
        } else  if (preg_match('#^TN(M?[0-9]{2})/([0-9]{2})([0-9]{2})Z#',$this->current_group_text,$pieces)) {
            $tempC = (integer) strtr($pieces[1], 'M', '-');
            $tempF = round(1.8 * $tempC + 32);
            $this->wxInfo['ITEMS'][$this->tend]['MIN_TEMPERATURE_C'] = $tempC;
            $this->wxInfo['ITEMS'][$this->tend]['MIN_TEMPERATURE_F'] = $tempF;
            $this->wxInfo['ITEMS'][$this->tend]['MIN_TEMPERATURE_DAY'] = (int)$pieces[2];
            $this->wxInfo['ITEMS'][$this->tend]['MIN_TEMPERATURE_TIME'] = (int)$pieces[3];
//$this->wxInfo['ITEMS'][$this->tend]['MAX_WINDCHILL_C'] = $tempC;
//$this->wxInfo['ITEMS'][$this->tend]['MAX_WINDCHILL_F'] = $tempF;
//get_wind_chill($tempF, $this->wxInfo);
            if (strlen($pieces[2]) != 0 && $pieces[2] != 'XX') {
                $dewC = (integer) strtr($pieces[2], 'M', '-');
                $dewF = convertTempToF($dewC);
                $this->wxInfo['ITEMS'][$this->tend]['MIN_DEWTEMPERATURE_C'] = $dewC;
//            $this->wxInfo['ITEMS'][$this->tend]['MIN_DEWTEMPERATURE_F'] = $dewF;
                $rh = round(100 * pow((112 - (0.1 * $tempC) + $dewC) / (112 + (0.9 * $tempC)), 8),1);
                $this->wxInfo['ITEMS'][$this->tend]['MIN_HUMIDITY'] = $rh;
//get_heat_index($tempF, $rh, $this->wxInfo);
            }
            $this->wxInfo['ITEMS'][$this->tend]['CODE_MIN_TEMPERATURE'] = $this->current_group_text;
            $this->current_ptr++;
            $this->current_group++;
        } else {
            $this->current_group++;
        }

    }


    protected function get_temperature( ) {
// Decodes temperature and dew point information. Relative humidity is calculated. Also,
// depending on the temperature, Heat Index or Wind Chill Temperature is calculated.
// Format is tt/dd where tt = temperature and dd = dew point temperature. All units are
// in Celsius. A 'M' preceeding the tt or dd indicates a negative temperature. Some
// stations do not report dew point, so the format is tt/ or tt/XX.

        if (preg_match('#^(M?[0-9]{2})/(M?[0-9]{2}|[X]{2})?$#',$this->current_group_text,$pieces)) {
            $tempC = (integer) strtr($pieces[1], 'M', '-');
            $tempF = round(1.8 * $tempC + 32);
            $this->wxInfo['ITEMS'][$this->tend]['TEMPERATURE_C'] = $tempC;
//        $this->wxInfo['ITEMS'][$this->tend]['TEMPERATURE_F'] = $tempF;
            $this->wxInfo['ITEMS'][$this->tend]['WINDCHILL_C'] = $tempC;
//        $this->wxInfo['ITEMS'][$this->tend]['WINDCHILL_F'] = $tempF;
            $this->get_wind_chill($tempF);
            if (strlen($pieces[2]) != 0 && $pieces[2] != 'XX') {
                $dewC = (integer) strtr($pieces[2], 'M', '-');
                $dewF = convertTempToF($dewC);
                $this->wxInfo['ITEMS'][$this->tend]['DEWTEMPERATURE_C'] = $dewC;
//            $this->wxInfo['ITEMS'][$this->tend]['DEWTEMPERATURE_F'] = $dewF;
                $rh = round(100 * pow((112 - (0.1 * $tempC) + $dewC) / (112 + (0.9 * $tempC)), 8),1);
                $this->wxInfo['ITEMS'][$this->tend]['HUMIDITY'] = $rh;
                $this->get_heat_index($tempF, $rh);
            }
            $this->wxInfo['ITEMS'][$this->tend]['CODE_TEMPERATURE'] = $this->current_group_text;
            $this->current_ptr++;
            $this->current_group++;
        }
        else {
            $this->current_group++;
        }
    }


    protected function get_probality( ) {
// Decodes temperature and dew point information. Relative humidity is calculated. Also,
// depending on the temperature, Heat Index or Wind Chill Temperature is calculated.
// Format is tt/dd where tt = temperature and dd = dew point temperature. All units are
// in Celsius. A 'M' preceeding the tt or dd indicates a negative temperature. Some
// stations do not report dew point, so the format is tt/ or tt/XX.

        if (preg_match('#^PROB([0-9]{2})#',$this->current_group_text,$pieces)) {
            $this->tend++;
            $this->wxInfo['ITEMS'][$this->tend]['CODE_PROBABILITY'] = $this->current_group_text;
            $this->wxInfo['ITEMS'][$this->tend]['PROBABILITY'] = $pieces[1];
            $this->tend--;
            $this->current_ptr++;
            $this->current_group++;
            return;
        }

        $this->current_group++;

    }

    protected function get_barometer( ) {
// Decodes altimeter or barometer information.
// Format is Annnn where nnnn represents a real number as nn.nn in inches of Hg,
// or Qpppp where pppp = hectoPascals.
// Some other common conversion factors:
//   1 millibar = 1 hPa
//   1 in Hg = 0.02953 hPa
//   1 mm Hg = 25.4 in Hg = 0.750062 hPa
//   1 lb/sq in = 0.491154 in Hg = 0.014504 hPa
//   1 atm = 0.33421 in Hg = 0.0009869 hPa
        if (preg_match('#^(A|Q|QNH)([0-9]{4})(INS)?#',$this->current_group_text,$pieces)) {
            $this->wxInfo['ITEMS'][$this->tend]['CODE_BAROMETER'] = $this->current_group_text;
            if ($pieces[1] == 'A' || preg_match('#^(QNH)([0-9]{4})INS$#',$this->current_group_text,$pieces1)) {
                $pressureIN = substr($pieces1[2],0,2) . '.' . substr($pieces1[2],2);  // units are inches Hg
                $pressureHPA = round($pressureIN / 0.02953);                        // convert to hectoPascals
            }
            else {
                $pressureHPA = (integer)$pieces[2];              // units are hectoPascals
                $pressureIN = round(0.02953 * $pressureHPA,2);    // convert to inches Hg
            }
            $this->wxInfo['ITEMS'][$this->tend]['BAROMETER_IN'] = $pressureIN;
            $this->wxInfo['ITEMS'][$this->tend]['BAROMETER_HPA'] = $pressureHPA;
            $this->current_ptr++;
            $this->current_group++;
        }
        else {
            $this->current_group++;
        }
    }




    function get_i18nWindForce($km) {
        static $wind_force = array (
        0 => array (1,'Calme'),
        1 => array (5,'Très légère brise'),
        2 => array (11,'Légère brise'),
        3 => array (19,'Petite brise'),
        4 => array (28,'Jolie brise'),
        5 => array (38,'Bonne brise'),
        6 => array (49,'Vent frais'),
        7 => array (61,'Grand vent frais'),
        8 => array (74,'Coup de vent'),
        9 => array (88,'Fort coup de vent'),
        10 => array (102,'Tempete'),
        11 => array (117,'Violente tempête'),
        12 => array (400,'Ouragan'));

    }


    function get_i18nClouds($i18n_value) {
        static $cloudCode = array(
//'///' => 'can not determine visibility',
        'SKC' => 'Ciel clair',
        'CLR' => 'Ciel clair',
        'FEW' => 'Quelques nuages(12%>25%)',
        'SCT' => 'Assez nuageux(25%>50%)',
        'BKN' => 'Tres nuageux (50%>75%)',
        'OVC' => 'Couvert (100%)',
        'VV'  => 'Brouillard',
        'NSC' => 'Peu de nuage',
        'NCD' => 'Peu de nuage'
        );

        return $cloudCode[$i18n_value];
    }

    function get_i18nCondition ($i18n_value) {
        static $wxCode = array(
        'NSW' => 'Pas de changement significatif',
        'Recent' => '(Récent)',
        '-' => '(faible)',
        '+' => '(fort)',
        '//' => '',
        'VC' => '(voisinage)',
        'MI' => 'Mince',
        'PR' => 'Partiel',
        'BC' => 'Banc',
        'DR' => '(Bas)',
        'BL' => '(Élevé)',
        'SH' => 'Averse',
        'TS' => 'Orage',
        'FZ' => 'Verglaçant(e)',
        'DZ' => 'Bruine',
        'RA' => 'Pluie',
        'SN' => 'Neige légère',
        'SG' => 'neige en grains',
        'IC' => 'Cristaux de glace',
        'PE' => 'Granules de glace',
        'GR' => 'Grêle',
        'GS' => 'Neige roulée',  // and/or snow pellets
        'UP' => 'Inconnue',
        'BR' => 'Brume',
        'FG' => 'Brouillard',
        'FU' => 'Fumée',
        'VA' => 'Cendres volcaniques',
        'DU' => 'Poussière',
        'SA' => 'Sable',
        'HZ' => 'Brume sèche',
        'PY' => 'Vapeur ?',
        'PO' => 'Tourbillons de poussière/sable',
        'SQ' => 'Grains',
        'FC' => 'Entonnoir nuageux (tornade ou trombe marine)',
        'SS' => 'Tempête de sable',
        'WS'=> 'Cisaillement du vent');

        return $wxCode[$i18n_value];
    }






    function racine($nbr, $racine) {
        return pow ((float)$nbr, (1/$racine));
    }

    /*
 * Convert Km wind speed to wind force
    */

    function convertWindspeedToForce($km) {
        $force = round($this->racine(($km*$km/9),3));
        return $force;
    }


    /*
 * Convert a unit speed
    */
    function convertSpeed($wind, $unit) {
        $unit_speed = get_unitSpeed();
// Convert wind speed into miles per hour.
// Some other common conversion factors (to 6 significant digits):
//   1 mi/hr = 1.15080 knots  = 0.621371 km/hr = 2.23694 m/s
//   1 ft/s  = 1.68781 knots  = 0.911344 km/hr = 3.28084 m/s
//   1 knot  = 0.539957 km/hr = 1.94384 m/s
//   1 km/hr = 1.852 knots    = 3.6 m/s
//   1 m/s   = 0.514444 knots = 0.277778 km/s

        $speed = round($wind * $unit_speed[$unit],1);
        return $speed;
    }

    /**
     *  dispatch METAR or TAF buletin in multiline
     */
    function formatMetarTaf($metar,$replace) {
        $result = preg_replace('/((PROB[0-9]{2} +(.*?) +)|(FM[0-9]{6}|TEMPO|BECMG))/',"$replace$1",$metar);

        return $result;
    }


    function varConcatenate(&$obj,$varname,$content) {

        if (strlen($content)>0) {
            if (isset($obj["$varname"])) {
                $obj["$varname"] .= ' '.$content;
            }
            else {
                $obj["$varname"] = $content;
            }
        }
    }

}



?>
