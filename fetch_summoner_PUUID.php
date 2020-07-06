<?php
set_time_limit(0);
$key = "Your API Key";
$intToTier = array('BRONZE','SILVER','GOLD','PLATINUM','DIAMOND');
$intToDivision = array('I','II', 'III', 'IV');
$DivisionIndex = 0;
$tierIndex = 0;
$page = 'Page_1';

while ($tierIndex < 5 && $DivisionIndex < 4) {
  $tier = $intToTier[$tierIndex];
  $division = $intToDivision[$DivisionIndex];
  $response = array();
  $ids = file_get_contents('./summoner_ID_data/summonerID_'.$tier.'_'.$division.'_'.$page.'.json');
  $formatids = json_decode($ids,true);

  foreach ($formatids as $index => $value) {
    $summonerID = $value;
    $url = "https://euw1.api.riotgames.com/tft/summoner/v1/summoners/" . $summonerID . "?api_key=" . $key ."";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT,1);
    array_push($response, json_decode(curl_exec($ch),true)['puuid']);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code == 429 ) {
      sleep(120);
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_FRESH_CONNECT,1);
      array_push($response, json_decode(curl_exec($ch),true)['puuid']);
      curl_close($ch);
    }
  }
  $fp = fopen('summonerPUUID_'.$tier.'_'.$division.'_'.$page.'.json', 'w');
  fwrite($fp, json_encode($response));
  fclose($fp);

  if($DivisionIndex != 3) {
    $DivisionIndex++;
  } else {
    $tierIndex++;
    $DivisionIndex = 0;
  }
}

?>
