<?php
/**
 * Defines a Cluster.
 * Each cluster is a set of $points (x,y), with a center (x,y) and variance on (x,y) directions
 * @date 27/March/2009 
 */

class Cluster
{
  /** Cluster points */
  public $points;
  /** Maximum point value from sample */
  public $maxPoint;
  /** Cluster center (mean) */
  public $avgPoint;
  /** Cluster median */
  public $medPoint;
  /** Cluster variance */
  public $variance;
  
  public function __construct($center) 
  {
    $this->avgPoint = $center;
    // to begin, variance is set to 0
    $this->variance = new Point(0, 0);
  }
  
  /** Sets the maximum point value from vectors sample */
  public function setMaxPoint($point)
  {
    $this->maxPoint = $point;
  }
  
  /** Computes the cluster center (mean) */
  public function calculateAverage()
  {
    $count = count($this->points);
    
    // with random initialization, this check is required 
    if ($count == 0) 
    {
      // no clues...
      $this->avgPoint->x = 0;
      $this->avgPoint->y = 0;
      
      return;
    }
    
    $xsum = 0; $ysum = 0;
    foreach ($this->points as $p) {
      $xsum += $p->x;
      $ysum += $p->y;
    }
    
    $this->avgPoint->x = $xsum / $count;
    $this->avgPoint->y = $ysum / $count;
  }
    
  /** Computes the cluster median */
  public function calculateMedian()
  {
    $count = count($this->points);
    
    // with random initialization, this check is required 
    if ($count == 0) 
    {
      // no clues, get max values and just randomize
      $this->medPoint->x = rand(0, $this->maxPoint->x);
      $this->medPoint->y = rand(0, $this->maxPoint->y);
      
      return;
    }
    
    sort($this->points);
    
    // compute 50th percentile (in general: q = N * p/100 + 0.5)
    $q = round($count/2 + 0.5);
    
    $this->medPoint->x = $this->points[$q]->x;
    $this->medPoint->y = $this->points[$q]->y;
  }
  
  /** 
   * Computes the cluster variance, defined as the second sample central moment. 
   * @link http://mathworld.wolfram.com/SampleVariance.html 
   */
  public function calculateVariance()
  {
    $count = count($this->points);
    
    if ($count == 0) {
      $this->variance->x = 0;
      $this->variance->y = 0;
      
      return;
    }
    // Var(X) = E[(X - m)^2]
    $xsum2 = 0; $ysum2 = 0;
    foreach($this->points as $p) {
      $xdiff = $p->x - $this->avgPoint->x;
      $ydiff = $p->y - $this->avgPoint->y;
      $xsum2 += $xdiff * $xdiff; 
      $ysum2 += $ydiff * $ydiff;
    }   
    /* Note that the underlying distribution is not known. 
     * Thus, in order to obtain an unbiased estimator for sigma^2, 
     * it is necessary to instead define a "bias-corrected sample variance".
     */ 
    $bias = ($count > 1) ? $count - 1 : $count;
    $this->variance->x = $xsum2 / $bias;
    $this->variance->y = $ysum2 / $bias;
  }
}
?>
