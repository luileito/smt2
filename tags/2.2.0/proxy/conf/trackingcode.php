<?php
if (!session_id()) session_start();
// This is the tracking code that will be inserted on proxied pages
$smt2code  = '//<![CDATA['                                                  . PHP_EOL;
$smt2code .= ' if (smt2) {'                                                 . PHP_EOL;
$smt2code .= '  smt2.record({'                                              . PHP_EOL;
$smt2code .= '   trackingServer: "' . $_SESSION["smt-trackingServer"] . '",'. PHP_EOL;
$smt2code .= '   recTime:'          . $_SESSION["smt-recTime"]        .  ','. PHP_EOL;
$smt2code .= '   fps:'              . $_SESSION["smt-fps"]            .  ','. PHP_EOL;
$smt2code .= '   postInterval:'     . $_SESSION["smt-postInterval"]   .  ','. PHP_EOL;
$smt2code .= '   cookieDays:'       . $_SESSION["smt-cookieDays"]     .  ','. PHP_EOL;
$smt2code .= '   layoutType: "'     . $_SESSION["smt-layoutType"]     . '",'. PHP_EOL;
$smt2code .= '   contRecording:'    . $_SESSION["smt-contRecording"]  .  ','. PHP_EOL;
$smt2code .= '   warn:'             . $_SESSION["smt-warn"]           .  ','. PHP_EOL;
$smt2code .= '   disabled:'         . $_SESSION["smt-disabled"]             . PHP_EOL;
$smt2code .= '  });'                                                        . PHP_EOL;
$smt2code .= ' }'                                                           . PHP_EOL;
$smt2code .= '//]]>'                                                        . PHP_EOL;
?>
