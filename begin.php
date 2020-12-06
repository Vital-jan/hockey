<?

  $file_read = file_get_contents('game.json');

  if (!$file_read) {
    $response = ["error"=>'unable to read file'];
    exit ($response);
  }
  
  $json = json_decode($file_read, true);

  if ($json['player1'] == 1 && $json['player2'] == 0 ) 
    { // зарегистрирован 1 игрок. 
      $json['player1'] = 1;
      $json['player2'] = 1;
      $json['player'] = 2; // текущий игрок будет вторым
      file_put_contents('game.json', json_encode($json));
      exit (json_encode(['player'=> 2]));
    }
    // зарегистрированы оба игрока или любое другое значение
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

