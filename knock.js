// гипотенуза:
const hypotenuse = (a, b) => Math.pow(a*a + b*b, 0.5);

// возвращает два вектора после столкновения двух шаров без учета массы
const knock = (x1, y1, r1, dx1, dy1, x2, y2, r2, dx2, dy2)=>{
  let distX = x1 - x2;
  let distY = y1 - y2;
  let distance = hypotenuse(distX, distY);
  if (distance > r1 + r2) return false;

  let angle = Math.abs(Math.atan(distY / distX)); // угол столкновения
  let v1 = hypotenuse(dx1, dy1); // скаляр движения 1 шара
  let v2 = hypotenuse(dx2, dy2); // скаляр движения 2 шара
  let res = {
    dx1: Math.cos(angle) * v1 * Math.sign(distX) + dx2,
    dy1: Math.sin(angle) * v1 * Math.sign(distY) + dy2,
    dx2: Math.cos(angle) * v2 * Math.sign(distX) + dx1,
    dy2: Math.sin(angle) * v2 * Math.sign(distY) + dy1,
  };
  return res;
}
