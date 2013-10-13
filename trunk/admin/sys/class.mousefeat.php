<?php
require_once 'functions.php';

class MouseFeat 
{
  public $numClicks   = 0; // ...
  public $activity    = 0; // in [0, 100]
  public $time        = 0; // if contRecording:false, interaction time < browsing time
  public $entry       = array('x' => 0, 'y' => 0);
  public $exit        = array('x' => 0, 'y' => 0);
  public $range       = array('x' => 0, 'y' => 0);
  public $scrollReach = array('x' => 0, 'y' => 0);
  public $centroid    = array('x' => 0, 'y' => 0);
  public $trackLen    = array('x' => 0, 'y' => 0);
  public $distance    = array('x' => 0, 'y' => 0);
  
  public function __construct(array $track) 
  {   
      $x = $track['x'];
      $y = $track['y'];
      $c = $track['c'];
      // Ensure real arrays
      if (!is_array($x)) $x = explode(",", $x);
      if (!is_array($y)) $y = explode(",", $y);
      if (!is_array($c)) $c = explode(",", $c);
      
      $f = (int)$track['f'];
      $n = count($x);
      $distCoords = convert_points($x, $y, true);
      // Distance study
      $dc_x = array();
      $dc_y = array();
      foreach ($x as $j => $val) {
        if ($j >= $n - 1) break;
        $dx = abs($x[$j] - $x[$j + 1]);
        $dy = abs($y[$j] - $y[$j + 1]);
        $dc_x[] = $dx;
        $dc_y[] = $dy;
        $dc_t[] = $dx*$dx + $dy*$dy;
      }
      // Kinematics study
      $stop = 0;
      foreach ($distCoords as $dist) {
        if ($dist > 0) continue;
        ++$stop;
      }
      
      $clean_x = array_unique($x);
      $clean_y = array_unique($y);
      // Horizontal components
      $entry_x    = $x[0];
      $exit_x     = $x[$n - 1];
      $min_x      = min($clean_x);
      $max_x      = max($clean_x);
      $range_x    = $max_x - $min_x;
      $scroll_x   = 100 * round($range_x / $track['w'], 4);
      $centroid_x = array_avg($clean_x);
      $len_x      = array_sum($dc_x);
      $dist_x     = array_avg($dc_x);
      // Vertical components
      $entry_y    = $y[0];
      $exit_y     = $y[$n - 1];
      $min_y      = min($clean_y);
      $max_y      = max($clean_y);
      $range_y    = $max_y - $min_y;
      $scroll_y   = 100 * round($range_y / $track['h'], 4);
      $centroid_y = array_avg($clean_y);
      $len_y      = array_sum($dc_y);
      $dist_y     = array_avg($dc_y);
      // Total components
      $dist_t     = array_avg($dc_t);
      $len_t      = array_sum($dc_t);

      // save features
      $this->time        = round($n/$f, 2);
      $this->numClicks   = count_clicks($c);
      $this->activity    = 100 * (1 - round($stop/count($distCoords), 4));
      $this->entry       = array( 'x' => $entry_x,    'y' => $entry_y    );
      $this->exit        = array( 'x' => $exit_x,     'y' => $exit_y     );
      $this->range       = array( 'x' => $range_x,    'y' => $range_y    );
      $this->scrollReach = array( 'x' => $scroll_x,   'y' => $scroll_y   );
      $this->centroid    = array( 'x' => $centroid_x, 'y' => $centroid_y );
      $this->trackLen    = array( 'x' => $len_x,      'y' => $len_y,     't' => $len_t  );
      $this->distance    = array( 'x' => $dist_x,     'y' => $dist_y,    't' => $dist_t );
  }
  
}
?>
