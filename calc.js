const getAngle = (x0, y0, x, y, units = 'deg') => 
  units == 'deg' ? Math.atan2(x - x0, y0 - y) * 180 / Math.PI : Math.atan2(x - x0, y0 - y);
// возв. угол между точками в градусах (если не задан аргумент units), или в радианах при любом другом значении units
// возвращает положительный результат если dx>0 или отрицательный в противном случае
// нулевой угол - направление вверх, 180 гр. - вниз;

const line = (el, x1, y1, x2, y2, color = 'blue', width = 1) =>
// отображает линию
{
  el.style.backgroundColor = color;
  el.style.height = width + 'px';
  el.style.position = 'absolute';

  let w = Math.hypot(x2-x1, y2-y1);
  let angle = getAngle(x1,y1,x2,y2);
  el.style.left = x1 + 'px';
  el.style.top = y1 + 'px';
  el.style.width = w + 'px';
  el.style.transformOrigin = '0 0';
  el.style.transform = `rotate(${angle-90}deg)`;
}

const knock = (x1, y1, r1, dx1, dy1, x2, y2, r2, dx2, dy2)=>{
  // возвращает два вектора после столкновения двух шаров без учета массы
  let distX = x1 - x2;
  let distY = y1 - y2;
  let distance = Math.hypot(distX, distY);
  if (distance > (r1 + r2)) return false;
  console.log('knock***************************')
  let angle = Math.abs(Math.atan(distY / distX)); // угол столкновения
  let v1 = Math.hypot(dx1, dy1); // скаляр движения 1 шара
  let v2 = Math.hypot(dx2, dy2); // скаляр движения 2 шара
  let res = {
    dx1: Math.cos(angle) * v1 * Math.sign(distX) + dx2,
    dy1: Math.sin(angle) * v1 * Math.sign(distY) + dy2,
    dx2: Math.cos(angle) * v2 * Math.sign(distX) + dx1,
    dy2: Math.sin(angle) * v2 * Math.sign(distY) + dy1,
  };
  return res;
}
