<?php
$max = 100;
$showstep = 6;
$retli = $lefli = '';
$list = '';
$top = '12';

$now = $_REQUEST['page'] ? ($_REQUEST['page']>$max ? $max : $_REQUEST['page']) : 1;

if ($now <= $showstep*2+1) {
    here('a');
    $top = '';
    $retli = '...';
    for ($i=1; $i<=$showstep*2+2; $i++) {
        $list .= $i;
    }
}

if ($now > $showstep*2+1 && $now < $max-$showstep) {
    here('b');

    $retli = $lefli = '...';
    for ($i=$now-$showstep; $i<=$now+$showstep; $i++) {
        $list .= $i;
    }
}

if ($now >= $max-$showstep) {
    here('c');

    $lefli = '...';
    for ($i=$max-$showstep*2; $i<=$max; $i++) {
        $list .= $i;
    }
}

$list = $top.$lefli.$list.$retli;

echo $list;

function here($site) {
    echo $site;
}