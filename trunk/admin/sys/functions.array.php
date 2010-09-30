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
  $input = (!is_array($input)) ? explode(",", $input) : $input;
  // count occurrences (array keys must be strings or integers)
  $unique = array_count_values($input); // returns an associative array of values from $input as keys and their count as value.
  // $hovered is an associative array(string => int)
  $unique = array_sanitize($unique);
  
  // exit if there are no data
  if (!$unique) return false;
  
  // compute sum
  $sum  = array_sum($unique);
  $data = array();
  // now calculate the frequency of each hovered element (in percentage)
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
  $isString = false;
  
  if (!is_array($input)) { 
    $input = explode(",", $input);
    $isString = true; 
  }
  
  $temp = array();  
  foreach ($input as $key => $value) {
    // avoid buggy values
    $key = trim($key);
    $value = trim($value);
    // store valid data
    if (!empty($key) && !empty($value)) {
      $temp[$key] = $value;
    }
  }
  
  return ($isString) ? implode(",", $temp) : $temp;
}

/** 
 * Convert null values to empty strings. Used to generate valid JSON arrays.
 * @param   array  $input   array
 * @return  array           Parsed array
 */
function array_null($input)
{
  if (!is_array($input)) {
    $input = explode(",", $input);
  }
  
  $temp = array(); 
  foreach ($input as $key => $value) {
    // store valid data
    $temp[$key] = (!empty($value)) ? $value : 0;
  }
  
  return $temp;
}

/** 
 * Does a weighted sum for a given multidimensional numeric array and computed weights.
 * @param   array  $input     multidimensional array (matrix)
 * @param   array  $weights   weights 
 * @return  array             Weighted sum
 * @link    http://www.compapp.dcu.ie/~humphrys/PhD/e.html 
 */
function array_avg_weighted($input, $weights) 
{
  $sumArray = array();
  
  foreach ($input as $arrItem) {
    $sumArray[] = array_avg($arrItem) * count($arrItem) / max($weights);
  }
  
  return $sumArray;
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
 * Computes the average sum of a matrix, assuming that each row is a numeric array.
 * @param   array $matrix a set of arrays (matrix)
 * @return  float         matrix average value
 */
function matrix_avg($matrix)
{
  $sum = 0;
  $count = 0;

  foreach ($matrix as $arrItem)
  {
    if (!is_array($arrItem)) { $arrItem = explode(",", $arrItem); }

    $sum += array_avg($arrItem);
    // note that this is an accumulative sum
    ++$count;
  }

  return round( $sum/$count, 2 );
}

/**
 * Computes the variance of a numeric array.
 * @param   array  $input   array
 * @return  int             Array index
 */
function array_sd($input)
{
  $variance = 0;
  $mean = array_avg($input);
  foreach ($input as $elem) {
    $variance += ($elem - $mean) * ($elem - $mean);
  }

  return round( sqrt($variance/count($input)), 2 );
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

  foreach ($matrix as $arrItem)
  {
    if (!is_array($arrItem)) { $arrItem = explode(",", $arrItem); }

    $sd += array_sd($arrItem);
    // note that we can have more than one input array
    ++$count;
  }
  
  return round( $sd/$count, 2 );
}

/** 
 * Gets the array index that has the maximum value.
 * @param   array  $input   array
 * @return  int             Array index
 */
function array_argmax($input)
{
  $max = max($input);
  foreach ($input as $key => $value)
  {
    if ($value == $max) {
      $maxIndex = $key;
      break;
    }
  }
  
  return $maxIndex;
}

/** 
 * Gets the array index that has the minimum value.
 * @param   array  $input   array
 * @return  int             Array index
 */
function array_argmin($input)
{
  $min = min($input);
  foreach ($input as $key => $value)
  {
    if ($value == $min) {
      $minIndex = $key;
      break;
    }
  }
  
  return $minIndex;
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
?>