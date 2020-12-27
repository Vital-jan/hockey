<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0 maximum-scale=1.0, user-scalable=no">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache">
<meta name="description" content="">
<meta name="author" content="Vitalii Kolomiiets, Kyiv, Ukraine, vitaljan@gmail.com">
<title></title>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="styles.css">
</head>
<?
if (file_exists('game.json')) {
  $json = json_decode(file_get_contents('game.json'), true);
}
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
    file_put_contents('game.json', json_encode($json));
  }

  $player = null;

  if ($json['player1'] == 1 && $json['player2'] == 0 ) 
    { // если залогинен только игрок 1
      $json['player1'] = 1;
      $json['player2'] = 1;
      $json['player'] = 2; // текущий игрок будет №2
      $save = file_put_contents('game.json', json_encode($json));
      $player = 2;
    }
    else
    {
      // если залогинены оба игрока или ни одного:
      $json['player1'] = 1;
      $json['player2'] = 0;
      $json['player'] = 1; // текущий игрок будет первым
      $save = file_put_contents('game.json', json_encode($json));
      $player = 1;
    }
?>
<? if ($player == 1):?>
<body data-player = 1>
  <div class="player1">
    <h1>Welcome to online AEROHOCKEY play area!</h1>
    <h2>Waiting to 2-nd player connect ...</h2>
    <h2>
      <img src="loading.gif" alt="">
    </h2>
  </div>
</body>
<?endif;?>

<? if ($player == 2):?>
<body data-player = 2>
</body>
<?endif;?>

<script src='calc.js'></script>
<script src='api.js'></script>
<script>

class Obj {
  constructor (x, y, w, h, r, parent, className = '', fixed = false, transparency = false) {
    this.x = x;
    this.y = y;
    this.dx = 0;
    this.dy = 0;
    this.el; // DOM element
    this.fixed = fixed;
    this.transparency = transparency;
    this.create(parent, className).setSize(w, h, r); // создаем DOM элемент и уст. его размеры
  }

  create (parent, className) {
    this.el = document.createElement('div');
    parent.append (this.el);
    if (className) className.split(' ').forEach ((i)=>this.el.classList.add(i));
    return this;
  }
  
  show () {
    this.el.style.left = this.x + 'px';
    this.el.style.top = this.y + 'px';
    this.el.style.width = this.width + 'px';
    this.el.style.height = this.height + 'px';
    this.el.style.visibility = 'visible';
    return this;
  };
  
  hide () {
    this.el.style.visibility = 'hidden';
    return this;
  };
  
  setSize (w, h, r) {
    this.radius = r;
    this.width = w;
    this.height = h;
    if (r) {
      this.width = r * 2;
      this.height = r * 2;
    }
    if (this.radius) this.el.style.borderRadius = '50%';
    return this;
  };

  reset () {
    this.x = 0;
    this.y = 0;
    this.dx = 0;
    this.dy = 0;
    return this;
  };

  move () {
    if (this.fixed) return;
    this.x += this.dx;
    this.y += this.dy;
    this.el.style.left = this.x + 'px';
    this.el.style.top = this.y + 'px';
    return this;
  };
}

class Game extends Obj{
  constructor (player) {
    // устанавливаем значения параметров:
    super(0, 0, 0, 0, 0, document.body, 'field');
    this.gameInterval = 20; // интервал игры в мс
    this.requestInterval = 500; // частота запросов к серверу в мс
    this.timeout = 20000; // таймаут окончания игры в мс
    this.maxSpeed = 15; // ограничение скорости удара (px/gameInterval)
    this.friction = 0.995; // замедление, px/game.gameInterval;
    // переменные:
    this.currentPlayer = 1; // игрок, владеющий мячом
    this.active = false; // мяч перемещается на своем поле
    this.player = player; // номер игрока
    this.waitTime = 0; // счетчик времени ожидания ответа сервера после отправки запроса
    this.sendRequest = false; // запрос был отправлен при выходе мяча за пределы поля
    this.obs = []; // массив объектов игры
    this.x = 0;
    this.y = 0;
    this.create(document.body, 'field').setSize(); // создаем DOM элемент и устанавливаем размеры игрового поля
  }

  setSize () {
    this.borderWidth = Math.round(window.innerWidth * 0.03);
    this.width = Math.min (window.innerWidth, window.innerHeight);
    this.height = this.width;
    return this;
  }

  show () {
    super.show();
    this.el.style.borderBottomLeftRadius = this.borderWidth + 'px';
    this.el.style.borderBottomRightRadius = this.borderWidth + 'px';
  }
}

class Border extends Obj {
  constructor (x, y, w, h, r, parent, className) {
    super(x, y, w, h, r, parent, className);
    this.fixed = true;
  }
}

class Ball extends Obj {
  // constructor (x, y, w, h, r, parent, className) {
  //   super(x, y, w, h, r, parent, className);
  // };
}

class Bit extends Obj {
  constructor (x, y, w, h, r, parent, className) {
    super(x, y, w, h, r, parent, className);
    this.hold = false;
  }

  move (dx, dy) {
    if (!this.hold) return;
    this.x += dx;
    this.y += dy;
    this.el.style.left = this.x + 'px';
    this.el.style.top = this.y + 'px';
  }
}

class Mouse {
  constructor () {
    this.x = 0;
    this.y = 0;
    this.dx = 0;
    this.dy = 0;
    this.down = false;
    this.lastX = 0;
    this.lastY = 0;
  }
  
  save () {
    this.lastX = this.x;
    this.lastY = this.y;
  }
  refresh () {
    this.dx = this.x - this.lastX;
    this.dy = this.y - this.lastY;
  }
}
// ======================= Start ========================

const startGame = (player)=>{
  console.log(`start game. Player${player}`)
  
  let mouse = new Mouse;
  window.addEventListener('mousemove', (event)=>{
console.log('down:',mouse.down, 'mousein:', bit.mouseIn, 'hold:',bit.hold)
    mouse.refresh();
    if (bit.hold) bit.move(mouse.dx, mouse.dy);
    mouse.save();
    
    mouse.x = event.pageX;
    mouse.y = event.pageY;
  });

  window.addEventListener('mousedown', (event)=>{
    mouse.down = true;
    console.log('down:',mouse.down)
    if (bit.mouseIn) bit.hold = true;
    mouse.x = event.pageX;
    mouse.x = event.pageX;
    mouse.y = event.pageY
    // mouse.lastX = mouse.x;
    // mouse.lastY = mouse.y;
  });

  window.addEventListener('mouseup', (event)=>{
    mouse.down = false;
    bit.hold = false;
  });

  document.body.innerHTML = '';
  let game = new Game;
  game.show();
  // создаем объекты:
  let gateWidth = Math.round(game.width / 3);
  obj = new Border (0, 0, game.borderWidth, game.height, 0, game.el, 'border');
  game.obs.push(obj);
  obj = new Border (game.width - game.borderWidth, 0, game.borderWidth, game.height, 0, game.el, 'border');
  game.obs.push(obj);
  obj = new Border (0, game.height - game.borderWidth, (game.width - gateWidth) / 2, game.borderWidth, 0, game.el, 'border');
  game.obs.push(obj);
  obj = new Border (game.width - (game.width - gateWidth) / 2, game.height - game.borderWidth, (game.width - gateWidth) / 2, game.borderWidth, 0, game.el, 'border');
  game.obs.push(obj);
  obj = new Border (game.width - (game.width - gateWidth) / 2 - game.borderWidth, game.height - game.borderWidth, 0,0, game.borderWidth, game.el, 'border');
  game.obs.push(obj);
  obj = new Border ((game.width - gateWidth) / 2 - game.borderWidth, game.height - game.borderWidth, 0, 0, game.borderWidth, game.el, 'border');
  game.obs.push(obj);
  let ballRadius = game.width * 0.03;
  let ball = new Ball (game.width / 2 - ballRadius, game.height / 2, ballRadius, ballRadius, ballRadius, game.el, 'ball');
  game.obs.push(ball);
  let bitRadius = ballRadius * 1.1;
  let bit = new Bit (game.width / 2 - bitRadius, game.height - 3 * bitRadius, bitRadius, bitRadius, bitRadius, game.el, 'bit');
  game.obs.push(bit);

  bit.el.addEventListener('mouseenter', ()=>{
    bit.mouseIn = true;
  })
  bit.el.addEventListener('mouseleave', ()=>{
    bit.mouseIn = false;
    console.log('leave')
  })

  game.obs.forEach((i)=>i.show());

  // ================= Main game loop =======================
  // temporarity for debug:
    game.active = true;

  let gameIterval = setInterval(()=>{
    if (!game.active) return;
  }, game.gameInterval);
  // ==========  ball comeback waiting loop ===========
  let passInterval = setInterval (()=>{
    if (game.active) return;
    console.log('wait pass')
  }, game.requestInterval);
}

window.onload = ()=>{
  let player = document.body.dataset.player;

  if (player == 1) {
    console.log('I`m player 1. Connecting to player 2 log in');
    let waitPlayer2 = setInterval(()=>
    {
      console.log('waiting...');
      fetch('wait2player.php', { // check to login 1 or 2 player
        method: "POST",
      }) 
      .then(function(response){
        if (response.status == 200) {}// удачный ajax запрос
        else {alert('Fetch error!')}// неудачный ajax запрос
        return response.json(); // принимаем значение номера игрока (1 или 2)
      })
      .then(function(json) {
        console.log(json.player2)
        if (json.player2) {
          clearInterval(waitPlayer2);
          startGame(player);
        }
      })
      .catch(function(error) {
        alert('wait2player.php error!' + error)
      });
    }, 500);
  }

  if (player == 2) { 
    console.log("I`m player 2. Start game.")
    startGame(player);
  }
};

  // Важно!!!
  // В системе координат мы оперируем не центром DOM элемента (даже если окружность),
  // а верхним левым его углом !
/*
  let scr = {
    borderWidth: 0,
    width: 0,
    height: 0
  }; 

  let gate = {
    gateWidth: 100, // ширина ворот
    gateLeft: 0, // положение ворот
  }

  let bit = {
    x: 0,
    y: 0,
    radius: 40,
    elem: document.querySelector('.bit')
  };

  let mouse = {
    down: false,
    x: 0,
    y: 0,
    v: 0, // скорость мышки в px/game.gameInterval (скаляр)
    lastTimeX: 0, // координаты мыши в прошлый период игрового интервала
    lastTimeY: 0,
    dx: 0, // вектор скорости мыши в px
    dy: 0
  }

  let ball = {
    x: 0,
    y: 0,
    dx: 0,
    dy: 0,
    radius: 20,
    elem: document.querySelector('.ball')
  };  
  
  
  const setSizes = ()=>{ // размеры поля, мяча, ракетки
    scr.borderWidth = Math.round(window.innerWidth * 0.03);
    let min = Math.min (window.innerWidth, window.innerHeight);
    scr.width = min;
    scr.height = min;
    document.querySelector('.main').style.width = min + 'px';
    document.querySelector('.main').style.height = min + 'px';
    document.querySelector('.field').style.width = min + 'px';
    document.querySelector('.field').style.height = min - scr.borderWidth + 'px';
    document.querySelector('.border.left').style.width = scr.borderWidth + 'px';
    document.querySelector('.border.left').style.height = scr.height - scr.borderWidth + 'px';
    document.querySelector('.border.right').style.height = scr.height - scr.borderWidth + 'px';
    document.querySelector('.border.right').style.width = scr.borderWidth + 'px';
    document.querySelector('.border.bottom').style.height = scr.borderWidth + 'px';
    document.querySelector('.border.bottom').style.width = scr.width - 2*scr.borderWidth + 'px';
    document.querySelector('.border.bottom').style.left = scr.borderWidth + 'px';
    document.querySelector('.border.bottom').style.bottom = -scr.borderWidth + 'px';
    document.querySelector('.border.bottom-left').style.bottom = -scr.borderWidth + 'px';
    document.querySelector('.border.bottom-right').style.bottom = -scr.borderWidth + 'px';
    document.querySelector('.border.bottom-left').style.width = scr.borderWidth + 'px';
    document.querySelector('.border.bottom-left').style.height = scr.borderWidth + 'px';
    document.querySelector('.border.bottom-left').style.borderRadius = `0 0 0 ${scr.borderWidth}px`;
    document.querySelector('.border.bottom-left').style.borderBottomWidth = `${scr.borderWidth}px`;
    document.querySelector('.border.bottom-left').style.borderLeftWidth = `${scr.borderWidth}px`;
    document.querySelector('.border.bottom-right').style.width = scr.borderWidth + 'px';
    document.querySelector('.border.bottom-right').style.height = scr.borderWidth + 'px';
    document.querySelector('.border.bottom-right').style.borderRadius = `0 0 ${scr.borderWidth}px 0`;
    document.querySelector('.border.bottom-right').style.borderBottomWidth = `${scr.borderWidth}px`;
    document.querySelector('.border.bottom-right').style.borderRightWidth = `${scr.borderWidth}px`;
    ball.radius = Math.round(window.innerWidth * 0.03);
    bit.radius = ball.radius * 1.1;
    document.body.style.backgroundPosition = `${scr.borderWidth}px 0;`;
    ball.elem.style.width = 2 * ball.radius + 'px';
    ball.elem.style.height = 2 * ball.radius + 'px';
    bit.elem.style.width = 2 * bit.radius + 'px'
    bit.elem.style.height = 2 * bit.radius + 'px'

    ball.elem.style.display = 'none';

    gate.width = ball.radius * 6;
    gate.left = (scr.width - gate.width) / 2;
    document.querySelector('.gate').style.height = scr.borderWidth + 'px';
    document.querySelector('.gate').style.width = gate.width + 2*scr.borderWidth + 'px';
    document.querySelector('.gate').style.bottom = -scr.borderWidth + 'px';
    document.querySelector('.gate').style.left = gate.left - scr.borderWidth + 'px';
    document.querySelector('.gate-left').style.left = gate.left - scr.borderWidth + 'px';
    document.querySelector('.gate-left').style.height = scr.borderWidth + 'px';
    document.querySelector('.gate-left').style.width = scr.borderWidth + 'px';
    document.querySelector('.gate-left').style.bottom = -scr.borderWidth + 'px';
    document.querySelector('.gate-left').style.borderRadius = `0 ${scr.borderWidth}px 0 0`;
    document.querySelector('.gate-right').style.left = gate.left + gate.width + 'px';
    document.querySelector('.gate-right').style.height = scr.borderWidth + 'px';
    document.querySelector('.gate-right').style.width = scr.borderWidth + 'px';
    document.querySelector('.gate-right').style.bottom = -scr.borderWidth + 'px';
    document.querySelector('.gate-right').style.borderRadius = `${scr.borderWidth}px 0 0 0`;
  };

  const reset = () => { // начало игры или после гола
    bit.x = Math.round(scr.width / 2 - bit.radius);
    bit.y = Math.round(scr.height - 3*bit.radius);
    bit.elem.style.left = bit.x + 'px';
    bit.elem.style.top = bit.y + 'px';
    bit.elem.style.display='block';
    ball.x = Math.round(scr.width / 2 - ball.radius);
    ball.y = Math.round(scr.height / 8 - ball.radius);
    ball.dy = 5;
    ball.dx = 1;
    ball.elem.style.display = 'block';
    ball.elem.style.left = ball.x + 'px';
    ball.elem.style.top = ball.y + 'px';
    game.active = true;
  };

  const bottomHandler = () => 
  { // мяч коснулся низа:

      if ( !(ball.y >= scr.height - 2 * ball.radius - scr.borderWidth)) return; // мяч не внизу

      if ( ball.x <= gate.left - ball.radius | ball.x >= gate.left + gate.width) { // мяч не в зоне ворот, отскок мяча от низа:
        ball.dy = -ball.dy;
        ball.y = scr.height - 2 * ball.radius  - scr.borderWidth - 1;
        return;
      }

      // мяч в зоне ворот, "штанга или гол":

      if (ball.y - ball.radius >= scr.height) { // гол!
        console.log('goal!!!!!!!!!!!!!!!!!!!!!!!!!');
        reset();
        return;
      }

      // не гол (штанга):

      let postKnockResult = // столкновение мяча с левой штангой
      knock(
            ball.x, ball.y, ball.radius, ball.dx, ball.dy,
            gate.left - scr.borderWidth, scr.height - scr.borderWidth, scr.borderWidth, 0, 0
          );
        if (postKnockResult) {
          if (!game.postContact) { // столкновение в данный момент не происходит
            ball.dx = postKnockResult.dx1;
            ball.dy = postKnockResult.dy1;
          }
          ball.elem.style.backgroundColor = 'red';
          console.log('Штанга!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!')
          game.postContact = true;

        } else { // конец столкновения мяча со штангой
        ball.elem.style.backgroundColor = 'green';
        game.postContact = false;
      }
      let rightPostKnockResult = // столкновение мяча с правой штангой
      knock(
        ball.x, ball.y, ball.radius, ball.dx, ball.dy,
          gate.left - scr.borderWidth + gate.width, scr.height - scr.borderWidth, scr.borderWidth, 0, 0
          );
        if (rightPostKnockResult) {
          if (!game.postContact) { // столкновение в данный момент не происходит
            ball.dx = rightPostKnockResult.dx1;
            ball.dy = rightPostKnockResult.dy1;
          }
          ball.elem.style.backgroundColor = 'red';
          console.log('Штанга!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!')
          game.postContact = true;

        } else { // конец столкновения мяча со штангой
        ball.elem.style.backgroundColor = 'green';
        game.postContact = false;
      }
  }

  // игровой интервал мяч на своем поле. основной цикл игры. ============
  const activeIntervalFunction = () => {
    
    if (!game.active) return;

    ball.x += ball.dx;
    ball.y += ball.dy;
  
    bottomHandler();
      
    if (ball.x >= scr.width - 2 * ball.radius - scr.borderWidth) 
    { // мяч коснулся правого края
      ball.dx = -ball.dx;
      ball.x = scr.width - 2 * ball.radius - scr.borderWidth - 1;
    }
    if (ball.x <= 2 * ball.radius - scr.borderWidth) 
    { // мяч коснулся левого края
      ball.dx = -ball.dx;
      ball.x = 2 * ball.radius - scr.borderWidth + 1;
    }
    
    // контакт биты с мячом:
    let knockResult = knock(ball.x, ball.y, ball.radius, ball.dx, ball.dy, bit.x, bit.y, bit.radius, mouse.knockDX, mouse.knockDY);
    if (knockResult) {
      if (!game.contact) {
        if (mouse.down) {
          ball.dx = knockResult.dx1;
          ball.dy = knockResult.dy1;
          }
        }
        ball.elem.style.border = '5px solid red';
        game.contact = true;
      } else { // конец столкновения мяча с битой
        ball.elem.style.border = 'none';
        game.contact = false;
      }
      
      // замедление
      ball.dx *= game.friction;
      ball.dy *= game.friction;
      // визуализируем перемещение мяча
      ball.elem.style.left = ball.x + 'px';
    ball.elem.style.top = ball.y + 'px';
    
    // отбиваем мяч от верха (временно):///////////////////////////////////////////////////////////////
    if (ball.y <= 0) {ball.dy = -ball.dy;}
    return;
    
    if (ball.y <= -ball.radius) { // мяч наполовину ушел за пределы поля
      
      // отправка данных на сервер
      if (!game.sendRequest) { // отправляем запрос только 1 раз, пока мяч не скроется полностью за пределами поля
        game.sendRequest = true;
        pass();
      }
    };
    
    if (ball.y <= -ball.radius * 2) {// мяч полностью ушел за пределы поля
      game.active = false; // стоп движение мяча
      game.sendRequest = false;
    };
  } // end ---------- activeInterval

  
  const passiveIntervalFunction = () => { // мяч на поле соперника
    if (game.active)
    {
      game.waitTime = 0;
      return;
    };
    waitBack();
    game.waitTime += game.requestInterval;
    if (game.waitTime >= game.timeout) 
    {
      clearInterval(passiveInterval);
      console.log('game.timeout ---------------');
    }
  };
  
  // начало игры ===============================================
  bit.elem.addEventListener('mousedown', (event)=>
  {
    event.preventDefault();
    event.stopPropagation();
    mouse.down = true;
  });

  window.addEventListener('mouseup', (event)=>
  {
      mouse.down = false;
  });

  window.addEventListener('mousemove', (event)=>
  {
    // запоминаем предыдущее положение мыши
    mouse.lastTimeX = mouse.x;
    mouse.lastTimeY = mouse.y;

    mouse.x = event.pageX;
    mouse.y = event.pageY;

    // вычисляем вектор скорости мыши
    mouse.dx = mouse.x - mouse.lastTimeX;
    mouse.dy = mouse.y - mouse.lastTimeY;
    
    // вычисляем скаляр скорости движения мыши:
    mouse.v = Math.hypo(mouse.dx, mouse.dy, 0.5);
    if (mouse.v > game.maxSpeed) {
      let mouseAngle = Math.abs(Math.atan(mouse.y / mouse.x));
      mouse.knockDY = Math.sin(mouseAngle) * game.maxSpeed * Math.sign(mouse.dx);
      mouse.knockDX = Math.cos(mouseAngle) * game.maxSpeed * Math.sign(mouse.dy);
    }
    // ограничиваем максимальную скорость мыши:
    mouse.knockDX = mouse.dx;
    mouse.knockDY = mouse.dy;

    if (mouse.down) { // если удерживается кнопка мыши:
      // перемещение биты:
      bit.x += mouse.dx;
      bit.y += mouse.dy;
      // проверка выхода биты за поле
      bit.y = bit.y < -5 ? -5 : bit.y;
      bit.y = bit.y > scr.height - scr.borderWidth ? scr.height - scr.borderWidth - 2*bit.radius : bit.y;
      bit.x = bit.x < scr.borderWidth ? scr.borderWidth : bit.x;
      bit.x = bit.x > scr.width - scr.borderWidth - 2*bit.radius ? scr.width -scr.borderWidth - 2*bit.radius : bit.x;
      // визуализация перемещения биты
      bit.elem.style.left = bit.x + 'px';
      bit.elem.style.top = bit.y + 'px';
    }
      
  });

  setSizes(); // уст. размеры объектов
  begin();
  let activeInterval = setInterval( activeIntervalFunction, game.gameInterval);
  let passiveInterval = setInterval( passiveIntervalFunction, game.requestInterval);
*/
</script>
</html>