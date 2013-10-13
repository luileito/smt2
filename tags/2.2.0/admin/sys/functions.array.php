<?php
/** 
 * Computes the frequency of each $input array member.
 * @param   mixed  $input        input string or array of strings to parse ($_POST vars are sent as strings)
 * @param   int    $threshold    frequencies (in percentage) under this $threshold will not be stored (default: 1%)
 * @return  array                A sorted associative array in the form '[mostFrequentItem]=>frequency,...,[lessFrequentItem]=>frequency'
 */
function array_frequency($input, $threshold = 1) 
{
  // convert $input in a real PHP array
  if (!is_array($input)) $input = explode(",", $input);
  // count occurrences (array keys must be strings or integers)
  $unique = array_sanitize(array_count_values($input));
  // exit if there are no data
  if (!$unique) return false;
  
  // compute sum
  $sum  = array_sum($unique);
  $data = array();
  // now calculate the frequency of each input element (in percentage)
  foreach ($unique as $k => $value) {
    $frequency = round(100*$value/$sum, 2);
    // store frecuencies above given threshold
    if ($frequency > $threshold) {
      $data[$k] = $frequency;
    } 
  }
  // order by frecuency
  arsort($data);

  return $data;
}

/** 
 * Removes empty items (both key and value) from an associative numeric array.
 * @param   mixed  $input   array or string to sanitize
 * @return  mixed           Sanitized array or string (used for widget tracking)
 */
function array_sanitize($input)
{
  $is_arr = is_array($input);
  
  if (!$is_arr) $input = explode(",", $input);
  $out = array();
  foreach ($input as $key => $val) {
    $key = trim($key);
    $val = trim($val);
    if (!empty($key) && !empty($val)) {
      $out[$key] = $val;
    }
  }
  
  return $is_arr ? $out : implode(",", $out);
}

/** 
 * Convert null values to empty strings. Used to generate valid JSON arrays.
 * @param   array  $input   array
 * @return  array           Parsed array
 */
function array_null($input)
{
  if (!is_array($input)) $input = explode(",", $input);
  
  $out = array(); 
  foreach ($input as $key => $val) {
    $out[$key] = (!empty($val)) ? $val : 0;
  }
  
  return $out;
}

/** 
 * Computes the average sum of a numeric array.
 * @param   array  $input   array or set of arrays (matrix)
 * @return  float           Array average
 */
function array_avg($input)
{
  return round( array_sum($input) / count($input), 2);
}

/**
 * Computes the variance of a numeric array.
 * @param   array  $input   array
 * @return  int             Array index
 */
function array_sd($input, $mean = null)
{
  $variance = 0;
  if ($mean == null) $mean = array_avg($input);
  foreach ($input as $elem) {
    $variance += ($elem - $mean) * ($elem - $mean);
  }

  return round( sqrt($variance/count($input)), 2 );
}

/**
 * Computes the average sum of a matrix, assuming that each row is a numeric array.
 * @param   array $matrix a set of arrays (matrix)
 * @return  float         matrix average value
 */
function matrix_avg($matrix)
{
  $sum = 0;
  $count = 0;

  foreach ($matrix as $arrItem) {
    //if (!is_array($arrItem)) $arrItem = explode(",", $arrItem);
    $sum += array_avg($arrItem);
    ++$count;
  }

  return round($sum/$count, 2);
}

/**
 * Computes the standard deviation of a matrix, assuming that each row is a numeric array.
 * @param   array $matrix a set of arrays (matrix)
 * @return  float         matrix average value
 */
function matrix_sd($matrix)
{
  $sd = 0;
  $count = 0;

  foreach ($matrix as $arrItem) {
    //if (!is_array($arrItem)) $arrItem = explode(",", $arrItem);
    $sd += array_sd($arrItem);
    ++$count;
  }
  
  return round($sd/$count, 2);
}

/** 
 * Gets the array index that has the maximum value.
 * @param   array  $input   array
 * @return  int             Array index
 */
function array_argmax($input)
{
  $max = max($input);
  $key = array_search($max, $input);
  
  return $key;
}

/** 
 * Gets the array index that has the minimum value.
 * @param   array  $input   array
 * @return  int             Array index
 */
function array_argmin($input)
{
  $min = min($input);
  $key = array_search($min, $input);
  
  return $key;
}

/**
 * De-nests nested arrays within the given array.
 * @autor DZone Snippets
 * @link  http://snippets.dzone.com/posts/show/4660
 */
function array_flatten($input)
{
  $i = 0;
  while ($i < count($input))
  {
    if (is_array($input[$i])) {
      array_splice($input, $i, 1, $input[$i]);
    } else {
      ++$i;
    }
  }

  return $input;
}

/**
 * Normalize array values on a per feature basis, so that each feature has uniform variance.
 */
function whiten(array $matrix)
{
  $sd = array();
  // transpose
  $columns = array_map(NULL, $matrix);
  foreach ($columns as $i => $col) {
    $sd[$i] = array_sd($col);
  }

  foreach ($matrix as $row => &$feats) {
    foreach ($feats as $i => &$feat) {
      $feat /= $sd[$row];
    }
  }
  
  return $matrix;
}
?>
