const process = require('node:process');
const fs = require('node:fs');
const buffer = require('node:buffer');

function simple() {
  let a = 0;
  for (let i = 0; i < 1000000; i++) {
    a++;
  }

  let thisisanotherlongname = 0;
  for (let thisisalongname = 0; thisisalongname < 1000000; thisisalongname++) {
    thisisanotherlongname++;
  }
}

function simplecall() {
  for (let i = 0; i < 1000000; i++) {
    'hallo'.length;
  }
}

function hallo(a) {}

function simpleucall() {
  for (let i = 0; i < 1000000; i++) {
    hallo(`hallo`);
  }
}

function simpleudcall() {
  for (let i = 0; i < 1000000; i++) {
    hallo2(`hallo`);
  }
}

function hallo2(a) {}

function mandel() {
  let w1 = 50;
  let h1 = 150;
  let recen = -.45;
  let imcen = 0.0;
  let r = 0.7;
  let s = 0;
  let rec = 0;
  let imc = 0;
  let re = 0;
  let im = 0;
  let re2 = 0;
  let im2 = 0;
  let x = 0;
  let y = 0;
  let w2 = 0;
  let h2 = 0;
  let color = 0;
  s = 2 * r / w1;
  w2 = 40;
  h2 = 12;
  for (y = 0; y <= w1; y = y + 1) {
    const buf = [];
    imc = s * (y - h2) + imcen;
    for (x = 0; x <= h1; x = x + 1) {
      rec = s * (x - w2) + recen;
      re = rec;
      im = imc;
      color = 1000;
      re2 = re * re;
      im2 = im * im;
      while ((((re2 + im2) < 1000000) && color > 0)) {
        im = re * im * 2 + imc;
        re = re2 - im2 + rec;
        re2 = re * re;
        im2 = im * im;
        color = color - 1;
      }
      if (color == 0) {
        buf.push('_');
      } else {
        buf.push('#');
      }
    }
    buf.push('\n');

    fs.writeSync(process.stderr.fd, buffer.Buffer.from(buf.join('')));
  }
}

function mandel2() {

  const b = ' .:,;!/>)|&IH%*#';
  //float r, i, z, Z, t, c, C;
  for (let y = 30; y > 0; y--) {
    const C = y * 0.1 - 1.5;

    for (let x = 0; x < 75; x++) {
      const c = x * 0.04 - 2;
      let z = 0;
      let Z = 0;

      let k = 0;
      for (; k < 5000; k++) {
        const t = z * z - Z * Z + c;
        Z = 2 * z * Z + C;
        z = t;
        if (z * z + Z * Z > 500000) {
          break;
        }
      }

      process.stderr.write(b[k % 16]);
    }

    process.stderr.write('\n');
  }
}

function Ack(m, n) {
  if (m == 0) {
    return n + 1;
  }
  if (n == 0) {
    return Ack(m - 1, 1);
  }

  return Ack(m - 1, Ack(m, (n - 1)));
}

function ackermann(n) {
  const r = Ack(3, n);
  process.stderr.write(`Ack(3,${n}): ${r}\n`);
}

function ary(n) {
  const X = new Array(n);
  const Y = new Array(n);

  for (let i = 0; i < n; i++) {
    X[i] = i;
  }
  for (let i = n - 1; i >= 0; i--) {
    Y[i] = X[i];
  }
  const last = n - 1;
  process.stderr.write(`${Y[last]}\n`);
}

function ary2(n) {
  const X = new Array(n);
  const Y = new Array(n);

  for (let i = 0; i < n;) {
    X[i] = i;
    ++i;
    X[i] = i;
    ++i;
    X[i] = i;
    ++i;
    X[i] = i;
    ++i;
    X[i] = i;
    ++i;

    X[i] = i;
    ++i;
    X[i] = i;
    ++i;
    X[i] = i;
    ++i;
    X[i] = i;
    ++i;
    X[i] = i;
    ++i;
  }

  for (let i = n - 1; i >= 0;) {
    Y[i] = X[i];
    --i;
    Y[i] = X[i];
    --i;
    Y[i] = X[i];
    --i;
    Y[i] = X[i];
    --i;
    Y[i] = X[i];
    --i;

    Y[i] = X[i];
    --i;
    Y[i] = X[i];
    --i;
    Y[i] = X[i];
    --i;
    Y[i] = X[i];
    --i;
    Y[i] = X[i];
    --i;
  }

  const last = n - 1;
  process.stderr.write(`${Y[last]}\n`);
}

function ary3(n) {
  const X = new Array(n);
  const Y = new Array(n);

  for (let i = 0; i < n; i++) {
    X[i] = i + 1;
    Y[i] = 0;
  }
  for (let k = 0; k < 1000; k++) {
    for (let i = n - 1; i >= 0; i--) {
      Y[i] += X[i];
    }
  }
  const last = n - 1;
  process.stderr.write(`${Y[0]} ${Y[last]}\n`);
}

function fibo_r(n) {
  return ((n < 2) ? 1 : fibo_r(n - 2) + fibo_r(n - 1));
}

function fibo(n) {
  const r = fibo_r(n);
  process.stderr.write(`${r}\n`);
}

// function hash1(n) {
//   const X = {};
//   for (let i = 1; i <= n; i++) {
//     X[i.toString(16)] = i;
//   }
//   let c = 0;
//   for (let i = n; i > 0; i--) {
//     if (Object.hasOwn(X, i.toString(16))) {
//       c++;
//     }
//   }
//   process.stderr.write(`${c}\n`);
// }

function hash1(n) {
  const X = new Map();
  for (let i = 1; i <= n; i++) {
    X.set(i.toString(16), i);
  }
  let c = 0;
  for (let i = n; i > 0; i--) {
    if (X.has(i.toString(16))) {
      c++;
    }
  }
  process.stderr.write(`${c}\n`);
}

// function hash2(n) {
//   const hash1 = {};
//   const hash2 = {};
//   for (let i = 0; i < n; i++) {
//     hash1[`foo_${i}`] = i;
//     hash2[`foo_${i}`] = 0;
//   }
//   for (let i = n; i > 0; i--) {
//     Object.entries(hash1).forEach(([key, value]) => {
//       hash2[key] += value;
//     });
//   }
//   const first = 'foo_0';
//   const last = `foo_${n - 1}`;
//   process.stderr.write(`${hash1[first]} ${hash1[last]} ${hash2[first]} ${hash2[last]}\n`);
// }

function hash2(n) {
  const hash1 = new Map();
  const hash2 = new Map();
  for (let i = 0; i < n; i++) {
    hash1.set(`foo_${i}`, i);
    hash2.set(`foo_${i}`, 0);
  }
  for (let i = n; i > 0; i--) {
    for (let [key, value] of hash1) {
      hash2.set(key, hash2.get(key) + value);
    }
  }
  const first = 'foo_0';
  const last = `foo_${n - 1}`;
  process.stderr.write(`${hash1.get(first)} ${hash1.get(last)} ${hash2.get(first)} ${hash2.get(last)}\n`);
}

let LAST = 42;
const IM = 139968;
const IA = 3877;
const IC = 29573;

function gen_random(n) {
  return ((n * (LAST = (LAST * IA + IC) % IM)) / IM);
}

function heapsort_r(n, ra) {
  let l = (n >> 1) + 1;
  let ir = n;

  while (1) {
    let rra;
    if (l > 1) {
      rra = ra[--l];
    } else {
      rra = ra[ir];
      ra[ir] = ra[1];
      if (--ir == 1) {
        ra[1] = rra;

        return;
      }
    }
    let i = l;
    let j = l << 1;
    while (j <= ir) {
      if ((j < ir) && (ra[j] < ra[j + 1])) {
        j++;
      }
      if (rra < ra[j]) {
        ra[i] = ra[j];
        j += (i = j);
      } else {
        j = ir + 1;
      }
    }
    ra[i] = rra;
  }
}

function heapsort(N) {

  const ary = new Array(N + 1);
  for (let i = 1; i <= N; i++) {
    ary[i] = gen_random(1);
  }
  heapsort_r(N, ary);
  process.stderr.write(`${ary[N].toFixed(10)}\n`);
}

function mkmatrix(rows, cols) {
  let count = 1;
  const mx = [];
  for (let i = 0; i < rows; i++) {
    mx[i] = [];
    for (let j = 0; j < cols; j++) {
      mx[i][j] = count++;
    }
  }

  return mx;
}

function mmult(rows, cols, m1, m2) {
  const m3 = [];
  for (let i = 0; i < rows; i++) {
    m3[i] = [];
    for (let j = 0; j < cols; j++) {
      let x = 0;
      for (let k = 0; k < cols; k++) {
        x += m1[i][k] * m2[k][j];
      }
      m3[i][j] = x;
    }
  }

  return m3;
}

function matrix(n) {
  const SIZE = 30;
  const m1 = mkmatrix(SIZE, SIZE);
  const m2 = mkmatrix(SIZE, SIZE);
  let mm;
  while (n--) {
    mm = mmult(SIZE, SIZE, m1, m2);
  }
  process.stderr.write(`${mm[0][0]} ${mm[2][3]} ${mm[3][2]} ${mm[4][4]}\n`);
}

function nestedloop(n) {
  let x = 0;
  for (let a = 0; a < n; a++) {
    for (let b = 0; b < n; b++) {
      for (let c = 0; c < n; c++) {
        for (let d = 0; d < n; d++) {
          for (let e = 0; e < n; e++) {
            for (let f = 0; f < n; f++) {
              x++;
            }
          }
        }
      }
    }
  }

  process.stderr.write(`${x}\n`);
}

function sieve(n) {
  let count = 0;
  while (n-- > 0) {
    count = 0;
    const flags = [...new Array(8192).keys()];
    for (let i = 2; i < 8193; i++) {
      if (flags[i] > 0) {
        for (let k = i + i; k <= 8192; k += i) {
          flags[k] = 0;
        }
        count++;
      }
    }
  }
  process.stderr.write(`Count: ${count}\n`);
}

function start_test() {
  // ob_start();

  return Number(process.hrtime.bigint()) / 1e9;
}

let total = 0;

function end_test(start, name) {
  const end = Number(process.hrtime.bigint()) / 1e9;
  const duration = end - start;
  total += duration;
  const durationFmt = duration.toFixed(6);

  const space = ' '.repeat(24 - name.length - durationFmt.length);
  process.stdout.write(`${name}${space}${durationFmt}\n`);

  return Number(process.hrtime.bigint()) / 1e9;
}

function strcat(n) {
  let str = '';
  while (n-- > 0) {
    str += 'hello\n';
  }
  process.stderr.write(`${str.length}\n`);
}

function print_total() {
  process.stdout.write('-'.repeat(24) + '\n');
  const totalFmt = total.toFixed(6);
  const space = ' '.repeat(19 - totalFmt.length);
  process.stdout.write(`Total${space}${totalFmt}\n`);
}

let t0, t;
t0 = t = start_test();
simple();
t = end_test(t, 'simple');
simplecall();
t = end_test(t, 'simplecall');
simpleucall();
t = end_test(t, 'simpleucall');
simpleudcall();
t = end_test(t, 'simpleudcall');
mandel();
t = end_test(t, 'mandel');
mandel2();
t = end_test(t, 'mandel2');
ackermann(7);
t = end_test(t, 'ackermann(7)');
ary(50000);
t = end_test(t, 'ary(50000)');
ary2(50000);
t = end_test(t, 'ary2(50000)');
ary3(2000);
t = end_test(t, 'ary3(2000)');
fibo(30);
t = end_test(t, 'fibo(30)');
hash1(50000);
t = end_test(t, 'hash1(50000)');
hash2(500);
t = end_test(t, 'hash2(500)');
heapsort(20000);
t = end_test(t, 'heapsort(20000)');
matrix(20);
t = end_test(t, 'matrix(20)');
nestedloop(12);
t = end_test(t, 'nestedloop(12)');
sieve(30);
t = end_test(t, 'sieve(30)');
strcat(200000);
t = end_test(t, 'strcat(200000)');
print_total();
