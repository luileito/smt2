<?php
/**
 * Defines a Point (2D vector)
 * @date 27/March/2009  
 */
 
class Point
{
  /** Horizontal coordinate */
  public $x;
  /** Vertical coordinate */
  public $y;
  
  public function __construct($x, $y) 
  {
    if ($x === null) { $x = 0; }
    if ($y === null) { $y = 0; }
    
    $this->x = $x;
    $this->y = $y;
  }
  
  /** Computes the Euclidean distance to point $p */
  public function getDistance($p) 
  {
    $x = $this->x - $p->x;
    $y = $this->y - $p->y;
    
    return sqrt($x*$x + $y*$y);
  }
}
?>