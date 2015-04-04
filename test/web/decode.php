<?php
/*
 * lib.metar-taf.php by Bruno Adele (bruno.adele@jesuislibre.org) (2010) in GPL Licence
 *
 * This web page decode the TAF buletin
 * Sample: decode.php?type=metar&message=LFMT%20011100Z%200112/0212%2012005KT%209999%20BKN045%20BKN100%20TEMPO%200113/0116%208000%20SHRA%20BKN023%20BKN080%20BECMG%200116/0118%2032015KT%20CAVOK%20TEMPO%200117/0121%2032014G24KT
*/

require_once(dirname(__FILE__).'/../../lib/lib.metar-taf.php');

// Parameters
$width_title = 18;
$width = 80;

if (isset($_GET["message"])) $message = $_GET["message"];
if (isset($_GET["type"])) $type = strtoupper($_GET["type"]);

$lib = new metar_taf();

if ($type=='METAR') {
    $lib->decode_metar(strtotime(date("Y-m-d",strtotime('2010-03-12'))),$message);
} else {
    $lib->decode_taf(strtotime(date("Y-m-d",strtotime('2010-03-12'))),$message);
}
$decode_result = $lib->getResult();
?>

<h1>Decoded <? echo $type ?> message</h1>
<pre><? echo $lib->formatMetarTaf($message, "\n    ")?></pre>

<pre>
    <?
    echo "\n";
    echo getAlignText('Station : ',$width_title,STR_PAD_LEFT);
    echo getAlignText($decode_result['STATION'],$width,STR_PAD_RIGHT);
    echo '<-- '.$decode_result['STATION']."\n";

    $count = count($decode_result['ITEMS']);
    for ($x=0;$x<$count;$x++) {
        // Hour
        if (isset($decode_result['ITEMS'][$x]['CODE_TIME'])) {
            echo getAlignText('Heure : ',$width_title,STR_PAD_LEFT);
            echo getAlignText("Buletin effectue le ".date("d",$decode_result['ITEMS'][$x]['DATE_RANGE_START_TIMESTAMP']).' du mois a '. date("H:i",$decode_result['ITEMS'][$x]['DATE_RANGE_START_TIMESTAMP']).' UTC',$width,STR_PAD_RIGHT);
            echo '<-- '.$decode_result['ITEMS'][$x]['CODE_TIME']." \n";
        }

        // Probability
        if (isset($decode_result['ITEMS'][$x]['PROBABILITY'])) {
            echo getAlignText('Probalite : ',$width_title,STR_PAD_LEFT);
            echo getAlignText($decode_result['ITEMS'][$x]['PROBABILITY']. ' %',$width,STR_PAD_RIGHT);
            echo '<-- '.$decode_result['ITEMS'][$x]['CODE_PROBABILITY']." \n";

        }

        if (isset($decode_result['ITEMS'][$x]['CODE_TEND'])) {
            echo getAlignText('Tendance : ',$width_title,STR_PAD_LEFT);
            echo getAlignText("",$width,STR_PAD_RIGHT);
            echo '<-- '.$decode_result['ITEMS'][$x]['CODE_TEND']." \n";

        }

        echo getAlignText('Prevision : ',$width_title,STR_PAD_LEFT);
        echo getAlignText("debutant le ".date("d \a H:i\h",$decode_result['ITEMS'][$x]['DATE_RANGE_START_TIMESTAMP']). ' et se terminant le '.date("d \a H:i\h",$decode_result['ITEMS'][$x]['DATE_RANGE_END_TIMESTAMP']),$width,STR_PAD_RIGHT);
        echo '<-- '.$decode_result['ITEMS'][$x]['CODE_DATE_RANGE']." \n";
//        }

        // Wind
        if (isset($decode_result['ITEMS'][$x]['CODE_WIND'])) {
            if ($decode_result['ITEMS'][$x]['WIND_SPEED']!=0) {
                $vent = $decode_result['ITEMS'][$x]['WIND_DIRECTION_TEXT'].' a une vitesse de '. $decode_result['ITEMS'][$x]['WIND_SPEED'].' km/h (force '.$lib->convertWindspeedToForce($decode_result['ITEMS'][$x]['WIND_SPEED']).')';
                if (isset($decode_result['ITEMS'][$x]['WIND_GUST'])) {
                    $vent.= ', raffale de '. $decode_result['ITEMS'][$x]['WIND_GUST'].' km/h (force '.convertWindspeedToForce($decode_result['ITEMS'][$x]['WIND_GUST']).')';
                }
                echo getAlignText('Vent : ',$width_title,STR_PAD_LEFT);
                echo getAlignText("Vent du ".$vent,$width,STR_PAD_RIGHT);
                echo '<-- '.$decode_result['ITEMS'][$x]['CODE_WIND']." \n";
            } else {
                echo getAlignText('Vent : ',$width_title,STR_PAD_LEFT);
                echo getAlignText("Pas de vent",$width,STR_PAD_RIGHT);
                echo '<-- '.$decode_result['ITEMS'][$x]['CODE_WIND']." \n";

            }
        }

        // Visibility
        if (isset($decode_result['ITEMS'][$x]['HVISIBILITY'])) {
            echo getAlignText('Visibilite : ',$width_title,STR_PAD_LEFT);
            if ($decode_result['ITEMS'][$x]['HVISIBILITY_QUALIFIER']==1) {
                $vis_qualifier = "superieure a";
            } else if ($decode_result['ITEMS'][$x]['HVISIBILITY_QUALIFIER']==-1) {
                $vis_qualifier = "inferieure a";
            } else {
                $vis_qualifier="juqu'a";
            }

            if ($decode_result['ITEMS'][$x]['HVISIBILITY']>1000) {
                $vis_dist = round ($decode_result['ITEMS'][$x]['HVISIBILITY']/1000,1).' km';
            } else {
                $vis_dist = $decode_result['ITEMS'][$x]['HVISIBILITY']. ' m';
            }


            echo getAlignText("Une visibilite $vis_qualifier $vis_dist",$width,STR_PAD_RIGHT);
            echo '<-- '.$decode_result['ITEMS'][$x]['CODE_HVISIBILITY']." \n";
        }

        // Condition
        if (isset ($decode_result['ITEMS'][$x]['CODE_CONDITIONS'])) {
            echo getAlignText('Meteo : ',$width_title,STR_PAD_LEFT);
            echo getAlignText($decode_result['ITEMS'][$x]['CONDITIONS'],$width,STR_PAD_RIGHT);
            echo '<-- '.$decode_result['ITEMS'][$x]['CODE_CONDITIONS']." \n";
        }

        // Clouds
        if (isset($decode_result['ITEMS'][$x]['CODE_CLOUDS'])) {
            $code_clouds = explode(' ',$decode_result['ITEMS'][$x]['CODE_CLOUDS']);
            $clouds = explode (',',$decode_result['ITEMS'][$x]['CLOUDS']);

            for ($i=0;$i<count($code_clouds);$i++) {
                $cloud_title='';
                if ($i==0) {
                    $cloud_title='Nuages : ';
                }
                echo getAlignText($cloud_title,$width_title,STR_PAD_LEFT);
                echo getAlignText(trim($clouds[$i]),$width,STR_PAD_RIGHT);
                echo '<-- '.$code_clouds[$i]."\n";
            }
        }



        // Temp. Max/Min
        if (isset($decode_result['ITEMS'][$x]['MAX_TEMPERATURE_C'])) {
            echo getAlignText('Temp. Maxi/Min : ',$width_title,STR_PAD_LEFT);
            echo getAlignText($decode_result['ITEMS'][$x]['MAX_TEMPERATURE_C']. ' deg le '.$decode_result['ITEMS'][$x]['MAX_TEMPERATURE_DAY'].' a '.$decode_result['ITEMS'][$x]['MAX_TEMPERATURE_TIME'].':00 / '.$decode_result['ITEMS'][$x]['MIN_TEMPERATURE_C'].' deg le '.$decode_result['ITEMS'][$x]['MIN_TEMPERATURE_DAY'].' a '.$decode_result['ITEMS'][$x]['MIN_TEMPERATURE_TIME'].':00',$width,STR_PAD_RIGHT);
            echo '<-- '.$decode_result['ITEMS'][$x]['CODE_MAX_TEMPERATURE'].' '.$decode_result['ITEMS'][$x]['CODE_MIN_TEMPERATURE']." \n";

        }

        // Barometer
        if (isset($decode_result['ITEMS'][$x]['CODE_BAROMETER'])) {
            echo getAlignText('Pression : ',$width_title,STR_PAD_LEFT);
            echo getAlignText("pression de ".$decode_result['ITEMS'][$x]['BAROMETER_HPA'].' hPa,',$width,STR_PAD_RIGHT);
            echo '<-- '.$decode_result['ITEMS'][$x]['CODE_BAROMETER']." \n";
        }
        print "\n";

    }

    // tend
    if (isset($decode_result['ITEMS'][$x]['TEND']) && strlen($decode_result['ITEMS'][$x]['TEND'])>0) {
        echo getAlignText('Tendence : ',$width_title,STR_PAD_LEFT);
        echo getAlignText($decode_result['ITEMS'][$x]['TEND'],$width,STR_PAD_RIGHT);
        echo '<-- '.$decode_result['ITEMS'][$x]['CODE_TEND']." \n";
    }

    // Remark
    if (isset($decode_result['REMARK'])) {
        echo getAlignText('Remarque : ',$width_title,STR_PAD_LEFT);
        echo getAlignText(trim($decode_result['REMARK']),$width,STR_PAD_RIGHT);
        echo '<-- '.trim($decode_result['REMARK'])." \n";
    }

    // Ignore
    if (isset($decode_result['IGNORES'])) {
        echo getAlignText('Ignore : ',$width_title,STR_PAD_LEFT);
        echo getAlignText(trim($decode_result['IGNORES']),$width,STR_PAD_RIGHT);
        echo '<-- '.trim($decode_result['IGNORES'])." \n";
    }

    // No Parsed
    if (isset($decode_result['CODE_NOPARSED'])) {
        echo getAlignText('Non Parse : ',$width_title,STR_PAD_LEFT);
        echo getAlignText($decode_result['CODE_NOPARSED'],$width,STR_PAD_RIGHT);
        echo '<-- '.$decode_result['CODE_NOPARSED']." \n";
    }

    /*
    * Align text
    */
    function getAlignText($text,$size,$align) {
        return str_pad($text, $size,' ',$align);
    }

    var_dump($decode_result);exit;

    ?>
</pre>
