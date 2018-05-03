<?php
function xrange($start, $end, $step = 1) {
    for ($i = $start; $i <= $end; $i += $step) {
        yield $i;
    }
}
foreach (xrange(1, 500000) as $num) {
    echo $num, "\n";
}

foreach (xrange(500000, 1000000) as $num) {
    echo $num, "\n";
}