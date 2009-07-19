<?php
/**
 * Defines a Cluster.
 * Each cluster is a set of $points (x,y), with a center (x,y) and variance (x,y)
 * @date 27/March/2009 
 */

class Cluster
{
  /** Cluster points */
  public $points;
  /** Cluster center */
  public $avgPoint;
  /** Cluster variance */
  public $variance;
  
  public function __construct($point) 
  {
    $this->avgPoint = $point;
    // to begin, variance is set to 0
    $vP = new Point(0, 0);
    $this->variance = $vP;
  }
  
  /** Sets the cluster center */
  public function calculateAverage($maxX, $maxY)
  {
    // $maxX, $maxY are used in case of not having cluster points
    $count = count($this->points);
    
    if ($count == 0) {
      // just randomize
      $this->avgPoint->x = rand(0, $maxX);
      $this->avgPoint->y = rand(0, $maxY);
      return;
    }
    
    foreach ($this->points as $p) {
      $xsum += $p->x;
      $ysum += $p->y;
    }
    
    $this->avgPoint->x = $xsum / $count;
    $this->avgPoint->y = $ysum / $count;
  }
  
  /** Sets the cluster variance */
  public function calculateVariance()
  {
    $count = count($this->points);
    
    if ($count == 0) {
        $this->variance->x = 0;
        $this->variance->y = 0;
        return;
    }
    
    foreach($this->points as $p) {
      $xdiff = $p->x - $this->avgPoint->x;
      $ydiff = $p->y - $this->avgPoint->y;
      $xsum2 += $xdiff * $xdiff; 
      $ysum2 += $ydiff * $ydiff;
    }
    
    $this->variance->x = $xsum2 / $count;
    $this->variance->y = $ysum2 / $count;
  }
}
?>