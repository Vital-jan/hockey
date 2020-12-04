<?
  // $file = fopen("game.json", "w", );
  // $json = json_decode(file_get_contents("game.json"), true);
  // echo $json["game_id"];
  // var_dump($json);
  
  $file_read = file_get_contents('game.json', true);
  $response = ["error"=>'unable to read file'];
  if ($file_read) $response = $file_read;
  exit ($response);
?>

