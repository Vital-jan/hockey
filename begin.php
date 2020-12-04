<?
  $file = fopen("game.json", "r", );
  $json = json_decode(file_get_contents("game.json"), true);
  echo $json["game_id"];
  // var_dump($json);
?>