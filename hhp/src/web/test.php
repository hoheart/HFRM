<?php
$t1  = strtotime( '2011-03-28' );
$t2 = strtotime( date( 'Y-m-d' ) );

echo ( $t2 - $t1 ) / 3600 / 24; 

// $t1 += 3600 * 24 * 1000;

// echo date( 'Y-m-d' , $t1 );