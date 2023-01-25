const process = require('node:process');
const console = require('node:console');

const IM = 139968;
const IA = 3877;
const IC = 29573;

class PRNG {
  constructor(seed = 0) {
    this.seed = seed;
  }

  next() {
    this.seed = (this.seed * IA + IC) % IM;
    return this.seed / IM;
  }
}

function insertion(input) {
  for (let i = 1; i < input.length; i++) {
    for (let j = i; j > 0 && input[j - 1] > input[j]; j--) {
      const tmp = input[j];
      input[j] = input[j - 1];
      input[j - 1] = tmp;
    }
  }
}

function bubble(input) {
  let swapped = false;
  for (let i = 0; i < input.length; i++) {
    for (let j = 1; j < input.length - i; j++) {
      if (input[j - 1] <= input[j]) {
        continue;
      }

      const tmp = input[j];
      input[j] = input[j - 1];
      input[j - 1] = tmp;
      swapped = true;
    }

    if (!swapped) {
      break;
    }
  }
}

function engine(input) {
  input = input.sort();
}

const prng = new PRNG();
[bubble, insertion /*, engine */].forEach((sortFn) => {
  for (let i = 0; i < 4; i++) {
    for (let j = 12; j < 16; j++) {
      const array = [...new Array(1 << j).keys()].reverse();
      // const array = [...new Array(1 << j).keys()].map(() => prng.next());

      const start = process.hrtime.bigint();
      sortFn(array);
      const end = process.hrtime.bigint();

      const duration = Number(end - start) / 1e9;

      console.log(`${i}\t${sortFn.name}\t${array.length}\t${duration.toFixed(6)}`);
    }
    console.log();
  }
});
