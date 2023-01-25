<?php

// 10: 29
// 100: 541
// 1000: 7917
// 10000: 104729

class PRNG
{
    private const IM = 139968;
    private const IA = 3877;
    private const IC = 29573;

    public function __construct(private float $seed = 0) {}

    public function next(): float
    {
        $this->seed = ($this->seed * self::IA + self::IC) % self::IM;

        return $this->seed / self::IM;
    }
}

function insertion(array &$input): void
{
    $count = count($input);
    for ($i = 1; $i < $count; $i++) {
        for ($j = $i; $j > 0 && $input[$j - 1] > $input[$j]; $j--) {
            $tmp = $input[$j - 1];
            $input[$j - 1] = $input[$j];
            $input[$j] = $tmp;
        }
    }
}

function bubble(array &$input): void
{
    $count = count($input);
    $swapped = false;
    for ($i = 0; $i < $count; $i++) {
        for ($j = 1; $j < $count - $i; $j++) {
            if ($input[$j - 1] <= $input[$j]) {
                continue;
            }

            $tmp = $input[$j - 1];
            $input[$j - 1] = $input[$j];
            $input[$j] = $tmp;
            $swapped = true;
        }

        if (!$swapped) {
            break;
        }
    }
}

function engine(array &$input): void
{
    sort($input);
}

function engine_numeric(array &$input): void
{
    sort($input, SORT_NUMERIC);
}

$prng = new PRNG();
foreach (['bubble', 'insertion' /*, 'engine', 'engine_numeric' */] as $sortFn) {
    for ($i = 0; $i < 4; $i++) {
        for ($j = 12; $j < 16; $j++) {
            $array = range((1 << $j) - 1, 0, -1);
            // $array = [];
            // $elems = 1 << $j;
            // for ($k = 0; $k < $elems; $k++) {
            //     $array[] = $prng->next();
            // }
            $start = hrtime(true);
            $sortFn($array);
            $end = hrtime(true);

            $duration = ($end - $start) / 1e9;

            echo sprintf("%d\t%s\t%d\t%.6f", $i, $sortFn, count($array), $duration), PHP_EOL;
        }
        echo PHP_EOL;
    }
}
