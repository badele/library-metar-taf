<?php

require_once(dirname(__FILE__).'/../../lib/lib.metar-taf.php');

class unittest_decode {

    /**
     * @assert ("LFMT 290600Z 03006KT 0500 R31/1100V1500D FG VV/// 10/10 Q1012") == "LFMT"
     */
    public function decode_metar_station($message) {
        $lib = new metar_taf();
        $lib->decode_metar(strtotime(date("Y-m-d",strtotime('2010-04-04'))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['STATION'];
    }

    /**
     * @assert (0,"2010-03-12","LFMT 290600Z 03006KT 0500 R31/1100V1500D FG VV/// 10/10 Q1012") == "1269835200"
     */
    public function decode_metar_date($array,$date,$message) {
        $lib = new metar_taf();
        $lib->decode_metar(strtotime(date("Y-m-d",strtotime('2010-03-12'))),$message);
        $decode_result = $lib->getResult();
        return$decode_result['ITEMS'][$array]['DATE_TIMESTAMP'];
    }



    /**
     * @assert ("LFMT 290600Z 03006KT 0500 R31/1100V1500D FG VV/// 10/10 Q1012") == "LFMT"
     */
    public function decode_metar_range_start($message) {
        $lib = new metar_taf();
        $lib->decode_metar(strtotime(date("Y-m-d",strtotime('2010-04-04'))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['STATION'];
    }

    /**
     * @assert ("LFMT 290600Z 03006KT 0500 R31/1100V1500D FG VV/// 10/10 Q1012") == 11.1
     */
    public function decode_metar_wind_speed($message) {
        $lib = new metar_taf();
        $lib->decode_metar(strtotime(date("Y-m-d",strtotime('2010-04-04'))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][0]['WIND_SPEED'];
    }

    /**
     * @assert ("LFMT 290600Z 03006KT 0500 R31/1100V1500D FG VV/// 10/10 Q1012") == 22.5
     */
    public function decode_metar_wind_direction($message) {
        $lib = new metar_taf();
        $lib->decode_metar(strtotime(date("Y-m-d",strtotime('2010-04-04'))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][0]['WIND_DIRECTION'];
    }

    /**
     * @assert ("LFMT 290600Z 03006KT 0500 R31/1100V1500D FG VV/// 10/10 Q1012") == "NNE"
     */
    public function decode_metar_wind_direction_text($message) {
        $lib = new metar_taf();
        $lib->decode_metar(strtotime(date("Y-m-d",strtotime('2010-04-04'))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][0]['WIND_DIRECTION_TEXT'];
    }

    /**
     * @assert ("LFMT 290600Z 03006KT 0500 R31/1100V1500D FG VV/// 10/10 Q1012") == "500"
     */
    public function decode_metar_visibility($message) {
        $lib = new metar_taf();
        $lib->decode_metar(strtotime(date("Y-m-d",strtotime('2010-04-04'))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][0]['HVISIBILITY'];
    }

    /**
     * @assert ("LFMT 290600Z 03006KT 0500 R31/1100V1500D FG VV/// 10/10 Q1012") == "Brouillard"
     */
    public function decode_metar_conditions($message) {
        $lib = new metar_taf();
        $lib->decode_metar(strtotime(date("Y-m-d",strtotime('2010-04-04'))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][0]['CONDITIONS'];
    }

    /**
     * @assert ("LFMT 290600Z 03006KT 0500 R31/1100V1500D FG VV/// 10/10 Q1012") == "Brouillard(0 m)"
     */
    public function decode_metar_clouds($message) {
        $lib = new metar_taf();
        $lib->decode_metar(strtotime(date("Y-m-d",strtotime('2010-04-04'))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][0]['CLOUDS'];
    }

    /**
     * @assert ("LFMT 290600Z 03006KT 0500 R31/1100V1500D FG VV/// 10/10 Q1012") == 1012
     */
    public function decode_metar_barometer($message) {
        $lib = new metar_taf();
        $lib->decode_metar(strtotime(date("Y-m-d",strtotime('2010-04-04'))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['ITEMS'][0]['BAROMETER_HPA'];
    }

    /**
     * @assert ("LFMT 290600Z 03006KT 0500 R31/1100V1500D FG VV/// 10/10 Q1012") == "R31/1100V1500D"
     */
    public function decode_metar_ignore($message) {
        $lib = new metar_taf();
        $lib->decode_metar(strtotime(date("Y-m-d",strtotime('2010-04-04'))),$message);
        $decode_result = $lib->getResult();
        return $decode_result['IGNORES'];
    }






}
?>
