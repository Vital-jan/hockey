const pass = () => { // передача мяча на другое поле
  let data = new FormData;
  data.append('player', Game.currentPlayer);
  data.append('dx', ball.dx);
  data.append('dy', ball.dy);
  data.append('x', ball.x);
  data.append('width', scr.width);

  fetch('pass.php', {
    method: "POST",
    body: data
  }) 
    .then(function(response){
        if (response.status == 200) {}// удачный ajax запрос
         else {}// неудачный ajax запрос
        return response.json();
    })
    .then(function(response){
      Game.currentPlayer = response.player;
      console.log('new player: ', Game.currentPlayer);
    })
    .catch(function(error) {
      alert('fetch error!' + error)
    });
  };

const begin = () => { // начало игры, регистрация
  fetch('begin.php', { // check to login 1 or 2 player
    method: "POST",
  }) 
  .then(function(response){
    if (response.status == 200) {}// удачный ajax запрос
    else {}// неудачный ajax запрос
    return response.json();
  })
  .then(function(json) {
    Game.currentPlayer = 1;
    Game.player = json.player;
    console.log('i am player: ', Game.player);

    let player2Waiting = setInterval (() => { // wait to player 2 connect
      console.log('waiting to 2-nd player connect.....')
      fetch('wait2player.php', { // check to login 1 or 2 player
        method: "POST",
      })
      .then((response)=>{return response.json()})
      .then((response)=>{
        if (response.player2) {
          clearInterval(player2Waiting);
          console.log('player 2 logged in. start game.')
          reset();
          start();
        };
      })
      .catch (()=>{alert('wait2player.php error' + error)})

    }, Game.requestInterval);

    })
    .catch(function(error) {
        alert('begin.php error!' + error)
    });
}

const wait = () => {
  fetch('wait.php', { // проверка передачи мяча назад
    method: "POST",
  }) 
  .then(function(response){
    if (response.status == 200) {}// удачный ajax запрос
    else {}// неудачный ajax запрос
    return response.json();
  })
  .then(function(response) {
  if (response.player == Game.player) {
    Game.currentPlayer = response.player;
    let k = scr.width / +response.width; // коєф. разницы размера экранов
    ball.dy = Math.round(-response.dy * k);
    ball.dx = Math.round(-response.dx * k);
    ball.y = +ball.radius;
    ball.x = Math.round(+response.x * k);
    console.log(response.width)
    
    // - добавить пересчет координат x пропорционально
      
    Game.active = true;
      ball.elem.style.display = "block";
    }
  })
  .catch(function(error) {
    alert('wait error!' + error)
  });

}