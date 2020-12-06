<?
// start game. login player 1 or 2.
  if (file_exists('game.json')) {$json = json_decode(file_get_contents('game.json'), true);}
  else {
    $json = [
      "game_id"=>0,
      "player"=>1,
      "x"=>"0",
      "dx"=>"0",
      "dy"=>"0",
      "width"=>"0",
      "player1"=>1,
      "player2"=>0
    ];
  }

  if ($json['player1'] == 1 && $json['player2'] == 0 ) 
    { // если залогинен только инрок 1
      $json['player1'] = 1;
      $json['player2'] = 1;
      $json['player'] = 2; // текущий игрок будет №2
      file_put_contents('game.json', json_encode($json));
      exit (json_encode(['player'=> 2]));
    }
    // если залогинены оба игрока
    $json['player1'] = 1;
    $json['player2'] = 0;
    $json['player'] = 1; // текущий игрок будет первым
    $save = file_put_contents('game.json', json_encode($json));
    exit (json_encode(['player'=> 1]));
?>
<!-- if(is_readable($path))
    echo 'Есть права на чтение.';

if(is_writable($path))
    echo 'Есть права на запись.'; -->

