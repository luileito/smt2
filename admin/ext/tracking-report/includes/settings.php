<?php
// if JavaScript is not enabled, get page number
$page = (int) $_GET['page'];
// otherwise, start from scratch
if (!$page) { $page = 1; }
// use this flag to reset DB query
$resetFlag = "prevall";
// the 'more' link text
$showMoreText = $show." more";
// use this text to stop requesting more records from DB
$noMoreText = '<h2 class="heading notfound">No more records found!</h2>';
?>