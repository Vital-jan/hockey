const toDeg = 180 / Math.PI;
const toRad = Math.PI / 180;

const getAngle = (x0, y0, x, y, units = 'deg') => {
  // возв. угол вектора x0,y0 - x,y по отношению к вертикали в градусах (если не задан аргумент units), или в радианах при любом другом значении units
  let res = Math.atan2(x - x0, y0 - y);
  if (res < 0) res += Math.PI * 2;
  return units == 'deg' ? res * toDeg : res;
}

const minAngle = (H, r1, r2, units = 'deg') => {
  // возв. макс. угол, при котором произойдет соударение двух шаров радиусом r1 и r2, расстояние между центрами которых = H
  let res = Math.asin((r1 + r2) / H);
  return units == 'deg' ? res * toDeg : res;
}

const distance = (x0, y0, x1, y1) => Math.hypot (x1-x0, y1-y0);
// возвращает дистанцию между 2 точками на плоскости (гипотенуза)

const getX = (hypot, angle) => Math.sin(angle) * hypot;
const getY = (hypot, angle) => Math.cos(angle) * hypot;

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
  // if (distance > (r1 + r2)) return false;
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

const discr = (a, b, c) => { // дискриминант
  return b*b - 4*a*c;
}

const quadro = (a, b, c) => { //кв. уравнение, возвращает меньшее значение или false если нет решений
  let D = discr(a ,b, c);
  if (D < 0) return null;
  if (D == 0) return -b / 2*a;
  return {x1:(-b - Math.pow(D, 0.5)) / (2 * a), x2:(-b + Math.pow(D, 0.5)) / (2 * a)};
}