<?
  $json = [
    'game_id' => 0,
    'player' => 3 - $_POST["player"] * 1,
    'x'=>$_POST["x"],
    'dx'=>$_POST["dx"],
    'dy'=>$_POST["dy"],
    'width'=>$_POST['width'],
    'player1'=>1,
    'player2'=>2,
  ];
  $file_save = file_put_contents('game.json', json_encode($json));
  $response = [
    "file"=> $file_save,
    'player'=> 3 - (int)$_POST["player"],
    'x'=> $_POST["x"],
    'dx'=> $_POST["dx"],
    'dy'=> $_POST["dy"],
  ];
  exit (json_encode($response));
?>

