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

const waitBack = () => {
  fetch('waitback.php', { // проверка передачи мяча назад
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