<?php
header("Content-type: text/plain");
 // Generate placefiles, whatever
 // Inspiration: http://grlevel3.tornadocentral.com/metars.php?state=IA

echo 'Title: KELO WeatherNet
Refresh: 1 
Color: 200 200 255
IconFile: 1, 18, 32, 2, 31, "https://mesonet.agron.iastate.edu/request/grx/windbarbs.png" 
IconFile: 2, 15, 15, 8, 8, "https://mesonet.agron.iastate.edu/request/grx/cloudcover.png" 
Font: 1, 11, 1, "Courier New"

';
include("../../../config/settings.inc.php");
include("../../../include/mlib.php");

function s2icon($s)
{
  if ($s < 2.5) return "1,21";
  if ($s < 5) return "1,1";
  if ($s < 10) return "1,2";
  if ($s < 15) return "1,3";
  if ($s < 20) return "1,4";
  if ($s < 25) return "1,5";
  if ($s < 30) return "1,6";
  if ($s < 35) return "1,7";

  if ($s < 40) return "1,8";
  if ($s < 45) return "1,9";
  if ($s < 50) return "1,10";
  if ($s < 55) return "1,11";
  if ($s < 60) return "1,12";
  if ($s < 65) return "1,13";
  if ($s < 70) return "1,14";

  if ($s < 75) return "1,15";
  if ($s < 80) return "1,16";
  if ($s < 85) return "1,17";
  if ($s < 80) return "1,18";
  if ($s < 100) return "1,19";
  return "1,20";

}

$now = time();
$jdata = file_get_contents("http://iem.local/api/1/currents.json?network=KELO");
$jobj = json_decode($jdata, $assoc=TRUE);

while (list($bogus, $iemob) = each($jobj["data"]) ){
    $mydata = $iemob;
    $tdiff = $now - strtotime($mydata["local_valid"]);
    if ($tdiff > 3600) continue;
    if ($mydata["sknt"] < 2.5) $mydata["drct"] = 0;
    
    echo "Object: ".$mydata["lat"].",".$mydata["lon"]."
  Threshold: 999
  Icon: 0,0,". $mydata["drct"] .",". s2icon( floatval($mydata["sknt"]) ) ."
  Icon: 0,0,000,2,13,\"".$mydata["name"]." @ ". strftime("%d %b %I:%M %p", strtotime($mydata['local_valid'])) ."\\nTemp: ".$mydata["tmpf"]."F (Dew: ".$mydata["dwpf"]."F)\\nWind: ". drct2txt($mydata["drct"]) ." @ ". intval($mydata["sknt"]) ."kt\\n\"
  Threshold: 150
  Text:  -17, 13, 1, \" ".$mydata["tmpf"]." \"
  Text:  -17, -13, 1, \" ".$mydata["dwpf"]." \"
 End:
      
";
}

?>
