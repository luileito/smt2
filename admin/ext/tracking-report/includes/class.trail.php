<?php
/**
 * UserTrail class.
 * Gets information about the click path. 
 * @date 19/June/2009
 */
class UserTrail 
{
  /** User ID */
  protected $id;
  /** User trail data: Associative Array with keys "id","date" and "url" */
  protected $data;
  /** Number of trails (visited pages) */
  protected $num;
  
  public function __construct($cliendId) 
  {
    $this->id = $cliendId;
    $this->query();
  }

  protected function query() 
  {    
    $records = db_select_all(TBL_PREFIX.TBL_RECORDS, 
                            "id,cache_id,DATE_FORMAT(sess_date,'%W %D %M %Y (%H:%i:%s)') as udate", 
                            "client_id = '".$this->id."' ORDER BY id ASC");
    
    $this->num = count($records);
    
    foreach ($records as $record) 
    {
      // this $cache query is only needed on 'analyze' module
      $cache = db_select(TBL_PREFIX.TBL_CACHE, "url", "id='".$record['cache_id']."'");
      // to track the clickpath we only need the id of each record  
      $this->data[] = array( 
                              "id"   => $record['id'],  
                              "date" => $record['udate'], // for 'analyze' module
                              "url"  => $cache['url']     // for 'analyze' module
                           );
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