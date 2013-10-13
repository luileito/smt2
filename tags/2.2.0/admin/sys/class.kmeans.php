<?php
require_once 'functions.php';

/**
 * K means clustering PHP class implementation.
 * @autor Luis Leiva
 * @date 20/December/2009
 */
class KMeans {

  /**
   * Number of clusters to find.
   * @type int
   */
  public $k;
  
  /**
   * Points to cluster.
   * @type array
   */
  public $points = array();
  
  /**
   * Groups of clusters.
   * @type array
   */
  public $clusters = array();

  /**
   * Cluster centroids.
   * @type array
   */
  public $centroids = array();
      
  /**
   * Number of maximum iterations (~ passes) until convergence.
   * @type int
   */
  public $maxIter = 10;

  private $dim;
  
  /**
   * Constructor.
   * @param int $k  number of clusters
   * @param int $pt points array
   */
  public function __construct(array $data, $k)
  {
    $this->points = $data;
    $this->dim = count($data[0]);
    // sanitize user input
    if ($k > count($data)) $k = count($data);
    else if ($k < 0) $k = 1;
    $this->k = $k;
  }
  
  /**
   * Computes K-means clustering for a given array of samples.
   */
  public function doCluster()
  {
    //ini_set('max_execution_time', 60);
    if (!$this->centroids) $this->initRandom();
    // a silly (but necessary) check
    if (count($this->k) < 2) {
      $this->deployClusters();
      return;
    }
    
    $iter = 0;
    do {
      $changed = false;
      // copy previous clustering configuration
      $old_centroids = array();
      foreach ($this->centroids as $i => $val) {
        $old_centroids[$i] = $val;
      }
      // assign points to closest centroid
      $this->deployClusters();
      // see if centroids have changed
      foreach ($this->centroids as $i => $center) {
        for ($d = 0; $d < $this->dim; ++$d) {
          if ($center[$d] != $old_centroids[$i][$d]) {
            $changed = true;
            break;
          }
        }
      }
      $iter++;  
    } while (!$changed || $iter < $this->maxIter);
  }
  
  /**
   * Initializes cluster centers randomly.
   * @return  cluster        Clusters
   */
  public function initRandom()
  {
    $min = $this->computeMinPoint($this->points);  
    $max = $this->computeMaxPoint($this->points);
    for ($i = 0; $i < $this->k; ++$i) {
      // initialize clusters centers randomly
      $center = array();
      for ($d = 0; $d < $this->dim; ++$d) {
        $center[$d] = rand($min[$d], $max[$d]);
      }
      $this->centroids[$i] = $center;
    }
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
    foreach ($this->points as $i => $point) {
      $norms[] = $this->vectorNorm($point, "uL2");
    }
    // order by max norm (highest to lowest), preserving keys
    arsort($norms);
    $normindex = array_keys($norms);
    for ($i = 0; $i < $this->k; ++$i) {
      $center = array();
      for ($d = 0; $d < $this->dim; ++$d) {
        $center[$d] = $this->points[$normindex[$i]][$d];
      }
      $this->centroids[$i] = $center;
    }
  }

  /**
   * Computes the maximum point from input sample.
   */
  private function computeMaxPoint()
  {
    $max = array();
    foreach ($this->points as $point) {
      for ($d = 0; $d < $this->dim; ++$d) {
        if (!isset($max[$d]) || $point[$d] > $max[$d]) $max[$d] = $point[$d];
      }
    }

    return $max;
  }

  /**
   * Computes the minimum point from input sample.
   */
  private function computeMinPoint()
  {
    $min = array();
    foreach ($this->points as $point) {
      for ($d = 0; $d < $this->dim; ++$d) {
        if (!isset($min[$d]) || $point[$d] < $min[$d]) $min[$d] = $point[$d];
      }
    }

    return $min;
  }
  
  /**
   * Assigns points to a cluster.
   * @return  cluster           New cluster
   */
  private function deployClusters()
  {
    // reinitialize clusters
    foreach ($this->clusters as &$cluster) {
      $cluster = array();
    }
    // compute best distance
    foreach ($this->points as $i => $pt) {
      $bestindex = 0;
      $bestdist  = PHP_INT_MAX;
      foreach ($this->centroids as $j => $centroid) {
        $distance = $this->getDistance($pt, $centroid);
        if ($distance < $bestdist) {
          $bestindex = $j;
          $bestdist  = $distance;
        }
      }
      // add the point to the best cluster.
      $this->clusters[$bestindex][$i] = $pt;
    }
    // recalculate centroids
    foreach ($this->clusters as $j => $cluster) {
      $this->centroids[$j] = $this->clusterCenter($cluster);
    }
  }
  
  public static function getDistance(array $a, array $b) 
  {
    $dim = count($a); // cannot use $this->dim in a static context
    $dist = 0;
    for ($d = 0; $d < $dim; ++$d) {
      $diff = $a[$d] - $b[$d];
      $dist += $diff * $diff;
    }
    
    return sqrt($dist);
  }
  
  public function clusterCenter(array $cluster) 
  {
    $sum = array_fill(0, $this->dim, 0);        
    // empty clusters have center: 0,...,0
    $n = count($cluster);
    if ($n == 0) return $sum;
    
    foreach ($cluster as $pt) {
      for ($d = 0; $d < $this->dim; ++$d) {
        $sum[$d] += $pt[$d];
      }
    }
    
    return $this->vecMean($sum, $n);
  }

  protected function vecMean(array $vector, $total) 
  {
	  foreach ($vector as &$value) {
		  $value /= $total;
	  }
	  
	  return $vector;
  }
  
  /**
   * Vector Norm calculation
   * @param   array   $vec    input vector
   * @param   string  $type   norm calculation type: "L1", "uL2", "sL2", "Linf", "Linf2"
   * @return  float           Vector Norm
   * @link http://www.physics.arizona.edu/~restrepo/475A/Notes/sourcea/node49.html
   * @link http://w3.gre.ac.uk/~physica/phy3.00/theory/node97.htm
   * @link http://cnx.org/content/m12877/latest/
   */
  protected function vectorNorm(array $vec, $type)
  {
    /* Note: mouse coordinates are always positive values, so we don't need
     * to compute the absolute values first for cases "L1", "Linf" and "Linf2".
     */
    switch ($type)
    {
      /** 1-norm */
      case "L1":
        $sum = 0;
        foreach ($vec as $value) { $sum += $value; }
        $result = $sum;
        break;

      /** Unscaled 2-norm */
      case "uL2":
      default:
        $sum = 0;
        foreach ($vec as $value) { $sum += $value*$value; }
        $result = sqrt($sum);
        break;

      /** Scaled 2-norm */
      case "sL2":
        $points = array();
        foreach ($vec as $value) { $points[] = $value; }
        $rmax = max($points);
        $sum = 0;
        foreach ($vec as $value) {
          $value /= $rmax;
          $sum += $value * $value;
        }
        $result = $rmax * sqrt($sum);

        break;

      /** Infinity norm */
      case "Linf":
        $points = array();
        foreach ($vec as $value) { $points[] = $value; }
        $result = max($points);
        break;

      /** Negative infinity norm */
      case "Linf2":
        $points = array();
        foreach ($vec as $value) { $points[] = $value; }
        $result = min($points);
        break;
    }

    return $result;
  }
}
?>
