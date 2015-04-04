<?php

require_once(dirname(__FILE__).'/../../lib/lib.metar-taf.php');

class unittest_decode {

//    function unittest_decode() {
//        date_default_timezone_set('UTC');
//        setlocale(LC_ALL, 'fr_FR.UTF-8');
//    }

    /**
     * @assert ("VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "VHHH"
     */
    public function decode_taf_station($message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime('2010-04-04'))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['STATION'];
    }

    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "1268388000"
     */
    public function decode_taf_date($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime('2010-03-12'))),$message);
        $decode_result = $lib->getResult();
        return$decode_result['ITEMS'][$array]['DATE_TIMESTAMP'];
    }


    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "1212/1318"
     * @assert (1,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "1212/1218"
     * @assert (2,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "1303/1309"
     */
    public function decode_taf_code_date_range($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['CODE_DATE_RANGE'];
    }



    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "2010-03-12 12:00"
     * @assert (1,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "2010-03-12 12:00"
     * @assert (2,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "2010-03-13 03:00"
     *
     */
    public function decode_taf_range_start($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return date("Y-m-d H:i",$decode_result['ITEMS'][$array]['DATE_RANGE_START_TIMESTAMP']);
    }

    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "2010-03-13 18:00"
     * @assert (1,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "2010-03-12 18:00"
     * @assert (2,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "2010-03-13 09:00"
     * @assert (1,"2010-04-07","KLAX 071725Z 0718/0824 VRB03KT P6SM SKC FM072000 27013KT P6SM SKC FM080400 VRB03KT P6SM SKC FM082000 25010KT P6SM SKC") == "2010-04-08 23:59"
     */
    public function decode_taf_range_end($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return  date("Y-m-d H:i",$decode_result['ITEMS'][$array]['DATE_RANGE_END_TIMESTAMP']);
    }



    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == 18.5
     * @assert (2,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == 9.3
     */
    public function decode_taf_wind_speed($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['WIND_SPEED'];
    }

    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == 90
     */
    public function decode_taf_wind_direction($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['WIND_DIRECTION'];
    }

    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "E"
     * @assert (2,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "VAR"
     */
    public function decode_taf_wind_direction_text($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime('$date'))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['WIND_DIRECTION_TEXT'];
    }

    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == 7000
     * @assert (1,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == 3500
     */
    public function decode_taf_visibility($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['HVISIBILITY'];
    }

    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == 0
     * @assert (1,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == 0
     */
    public function decode_taf_visibility_quantifier($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['HVISIBILITY_QUALIFIER'];
    }

    /**
     * @assert (1,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "BR -RA"
     */

    public function decode_taf_code_conditions($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['CODE_CONDITIONS'];
    }



    /**
     * @assert (1,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "Brume , Pluie (faible)"
     */

    public function decode_taf_conditions($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['CONDITIONS'];
    }



    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "FEW010 SCT020 BKN060"
     */
    public function decode_taf_code_clouds($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['CODE_CLOUDS'];
    }

    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "TX21/1306Z"
     */
    public function decode_taf_code_max_temp($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['CODE_MAX_TEMPERATURE'];
    }

    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == "TN16/1222Z"
     */
    public function decode_taf_code_min_temp($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['CODE_MIN_TEMPERATURE'];
    }

    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == 21
     */
    public function decode_taf_max_temp($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['MAX_TEMPERATURE_C'];
    }

    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == 13
     */
    public function decode_taf_max_day($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['MAX_TEMPERATURE_DAY'];
    }

    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == 06
     */
    public function decode_taf_max_time($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['MAX_TEMPERATURE_TIME'];
    }


    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == 16
     */
    public function decode_taf_min_temp($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['MIN_TEMPERATURE_C'];
    }

    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == 12
     */
    public function decode_taf_min_day($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime('2010-04-04'))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['MIN_TEMPERATURE_DAY'];
    }

    /**
     * @assert (0,"2010-03-12","VHHH 121100Z 1212/1318 09010KT 7000 FEW010 SCT020 BKN060 TX21/1306Z TN16/1222Z TEMPO 1212/1218 3500 BR -RA TEMPO 1303/1309 VRB05KT") == 22
     */
    public function decode_taf_min_time($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime($date))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['MIN_TEMPERATURE_TIME'];
    }



    /**
     */
    public function decode_taf_barometer($array,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime('2010-04-04'))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][$array]['BAROMETER_HPA'];
    }

    /**
     */
    public function decode_taf_ignore($array,$message) {
        $lib = new metar_taf();
        $lib->decode_taf(strtotime(date("Y-m-d",strtotime('2010-04-04'))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['IGNORES'];
    }
}
?>
