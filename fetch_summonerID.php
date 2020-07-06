<?php
$time_start = microtime(true);
$key = 'Your API Key';
$region = 'euw1';
$tier = '';
$division = '';
$intToTier = array('PLATINUM','DIAMOND');
$intToDivision = array('I','II', 'III', 'IV');
$DivisionIndex = 0;
$tierIndex = 0;
$page = 'Page_1';
$curls = array();
$results = array();

// build api calls
for ($i=0; $i <8 ; $i++) {
$tier = $intToTier[$tierIndex];
$division = $intToDivision[$DivisionIndex];
$curls[$i]= curl_init("https://{$region}.api.riotgames.com/tft/league/v1/entries/{$tier}/{$division}?page=2&api_key={$key}");
curl_setopt($curls[$i], CURLOPT_RETURNTRANSFER, 1);
if($DivisionIndex != 3) {
  $DivisionIndex++;
} else {
  $tierIndex++;
  $DivisionIndex = 0;
}
}

// build multi-curl handle
$mh = curl_multi_init();
foreach ($curls as $call) {
  curl_multi_add_handle($mh,$call);
}

// execute multi-curl
$running = null;
do {
  curl_multi_exec($mh,$running);
  curl_multi_select($mh);

} while($running > 0);


// build results and close multi_curl handle
foreach ($curls as $index => $call) {
  $results[$index] = json_decode(curl_multi_getcontent($call),true);
  curl_multi_remove_handle($mh,$call);
  curl_close($call);
}

// build JSON files
$sumID= array();
foreach ($results as $index => $result) {
  $curTier = $result[0]['tier'];
  $curDiv = $result[0]['rank'];

  foreach ($result as $key ) {
    $sumID[] = $key['summonerId'];
  }
   $fp = fopen('summonerID_'.$curTier.'_'.$curDiv.'_'.$page.'.json', 'w');
   fwrite($fp, json_encode($sumID));
   fclose($fp);
}


// time elapsed
$time_stop = microtime(true);
$time_total = $time_stop - $time_start;
$time_total=  number_format($time_total,1,',', ' ');
print("<tr><td> Time elapsed: " ."$time_total"." seconds</td></tr>");
 ?>
