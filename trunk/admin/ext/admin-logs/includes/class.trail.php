<?php
/**
 * UserTrail class.
 * Gets information about the click path. 
 * @date 19/June/2009
 */
class UserTrail 
{
  /** Client ID */
  protected $cid;
  /** User trail data: Associative Array with keys "id", "udate", "sess_time" and "url" */
  protected $data;
  /** Number of trails (visited pages) */
  protected $num;
  
  public function __construct($clientId) 
  {
    $this->cid = $clientId;
    $this->query();
  }

  protected function query() 
  {    
    $records = db_select_all(TBL_PREFIX.TBL_RECORDS, 
                             "id,cache_id,sess_date,DATE_FORMAT(sess_date,'%W %D %M %Y (%H:%i:%s)') as udate,sess_time", 
                             "client_id = '".$this->cid."' ORDER BY id ASC");
   
    $this->num = count($records);
    
		$count = 0;
		$prevRecord = null;
    foreach ($records as $record) 
    {
			// split browsing sessions by access date
			if ($prevRecord && strtotime($record['sess_date']) - strtotime($prevRecord['sess_date']) > 1200) {
				$count++;
			}
      // this $cache query is really needed only on the 'analyze' module
      $cache = db_select(TBL_PREFIX.TBL_CACHE, "url", "id = '".$record['cache_id']."'");
      // to track the REAL clickpath we need both the id AND the trail group of each record
      $this->data[] = array( 
                            "id"    => $record['id'],
                            "date"  => $record['udate'],
                            "time"  => $record['sess_time'],
                            "url"   => $cache['url'],
														"trail" => $count
                           );
			// update			
			$prevRecord = $record;
    }
  }
  
  public function getNumTrails() 
  {
    return $this->num;
  }
  
  public function getData() 
  {
		return $this->data;
  }

}
?>
