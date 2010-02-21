<?php
/**
 * K means clustering PHP class implementation.
 * @autor Luis Leiva
 * @date 20/December/2009
 */
 
//require_once 'functions.php';
require_once REQUIRED.'/class.cluster.php';
require_once REQUIRED.'/class.point.php';

class KMeans {

  /**
   * Number of clusters to find.
   * @type int
   */
  public $k;
  
  /**
   * Points to cluster.
   * @type Point
   */
  public $points;
  
  /**
   * Groups of clusters.
   * @type Cluster
   */
  public $clusters;
  
  /**
   * Number of maximum iterations until convergence.
   * @type int
   */
  public $maxIterations = 50;
  
  /**
   * Constructor.
   * @param int $k  number of clusters
   * @param int $pt points array
   */
  public function __construct($k, $pt)
  {
    $this->k = $k;
    $this->points = $pt;
  }
  
  /**
   * Computes K-means clustering for a given 2D array of mouse points.
   * @return  array         Clusters (center means and mean variances)
   */
  public function distributeOverClusters()
  {
    //ini_set('max_execution_time', 60);
    if (!$this->clusters) {
      $this->clusters = $this->initRandom($this->k, $this->points);
    }
    // now deploy points to closest center
    for ($a = 0; $a < $this->maxIterations; ++$a)
    {
      $this->deployClusters();
    }

    return $this->clusters;
  }
  
  /**
   * Initializes cluster centers randomly.
   * @return  cluster        Clusters
   */
  public function initRandom()
  {
    // compute the maximum point from sample, as the initialization is randomized
    $max = $this->computeMaxPoint($this->points);

    $clusters = array();
    
    for ($i = 0; $i < $this->k; ++$i)
    {
      // initialize clusters centers randomly
      $center = new Point(rand(0, $max->x), rand(0, $max->y));
      $clusters[] = new Cluster($center);
      // set max point, if no points are assigned to one cluster
      $clusters[$i]->setMaxPoint( new Point($max->x, $max->y) );
    }

    $this->clusters = $clusters;
  }

  /**
   * Initializes cluster centers, using the technique:
   * Katsavounidis et al. "A New Initialization Technique for Generalized
   * Lloyd Iteration", IEEE Signal Proc. Lett. 1 (10), 144-146, 1994.
   * Pros: A local minimum is guaranteed,
   *       and less iterations until convergence are needed.
   * @return  cluster        Clusters
   */
  public function initKatsavounidis()
  {
    $norms = array();
    // note that this initialization
    foreach ($this->points as $i => $point)
    {
      $norms[] = $this->vectorNorm($point, "uL2");
    }
    // order by max norm (highest to lowest), preserving keys
    arsort($norms);
    $normindex = array_keys($norms);

    $clusters = array();
    for ($i = 0; $i < $this->k; ++$i)
    {
      // initialize clusters
      $center = $this->points[ $normindex[$i] ];
      $clusters[] = new Cluster($center);
    }

    $this->clusters = $clusters;
  }

  /**
   * Computes the maximum point from input sample.
   */
  private function computeMaxPoint()
  {
    $maxX = 0;
    $maxY = 0;
    foreach ($this->points as $point)
    {
      if ($point->x > $maxX) { $maxX = $point->x; }
      if ($point->y > $maxY) { $maxY = $point->y; }
    }

    return new Point($maxX, $maxY);
  }

  /**
   * Assigns points to a cluster.
   * @return  cluster           New cluster
   */
  private function deployClusters()
  {
    // reinitialize points
    foreach ($this->clusters as $cluster) {
      $cluster->points = array();
    }
    // compute best distance
    foreach ($this->points as $pnt)
    {
      $bestcluster = $this->clusters[0];
      $bestdist = $bestcluster->avgPoint->getDistance($pnt);

      foreach ($this->clusters as $cluster) {
        $distance = $cluster->avgPoint->getDistance($pnt);
        if ($distance < $bestdist) {
          $bestcluster = $cluster;
          $bestdist = $distance;
        }
      }
      // add the point to the best cluster.
      $bestcluster->points[] = $pnt;
    }
    // recalculate the centers and sample variance
    foreach ($this->clusters as $cluster) {
      $cluster->calculateAverage();
      $cluster->calculateVariance();
    }
  }

  /**
   * Vector Norm calculation
   * @param   array   $array  input Array
   * @param   string  $type   norm calculation type: "L1", "uL2", "sL2", "Linf"
   * @return  float           Vector Norm
   * @link http://www.physics.arizona.edu/~restrepo/475A/Notes/sourcea/node49.html
   * @link http://w3.gre.ac.uk/~physica/phy3.00/theory/node97.htm
   * @link http://cnx.org/content/m12877/latest/
   */
  protected function vectorNorm($array, $type)
  {
    /* Note: mouse coordinates are always positive values, so we don't need
     * to compute the absolute values first for cases "L1", "Linf" and "Linf2".
     */
    switch ($type)
    {
      /** 1-norm */
      case "L1":
        $sum = 0;
        foreach ($array as $value) { $sum += $value; }
        $result = $sum;
        break;

      /** Unscaled 2-norm */
      case "uL2":
      default:
        $sum = 0;
        foreach ($array as $value) { $sum += $value*$value; }
        $result = sqrt($sum);
        break;

      /** Scaled 2-norm */
      case "sL2":
        $points = array();
        foreach ($array as $value) { $points[] = $value; }
        $rmax = max($points);
        $sum = 0;
        foreach ($array as $value) {
          $value /= $rmax;
          $sum += $value * $value;
        }
        $result = $rmax * sqrt($sum);

        break;

      /** Infinity norm */
      case "Linf":
        $points = array();
        foreach ($array as $value) { $points[] = $value; }
        $result = max($points);
        break;

      /** Negative infinity norm */
      case "Linf2":
        $points = array();
        foreach ($array as $value) { $points[] = $value; }
        $result = min($points);
        break;
    }

    return $result;
  }
}
?>