<?
// get player 2 logged in
  if (file_exists('game.json')) {
    $json = json_decode(file_get_contents('game.json'), true);
    // print_r( $json.player2);
    exit (json_encode(['player2'=> $json['player2']]));
  }
  echo "file game.json not exist";
?>
