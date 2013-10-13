<?php
/**
 * Hypernotes class.
 * @date 21/June/2012
 */
class Hypernote
{
  protected $id;
  protected $login = "";  
  protected $smpte = "";
  protected $data = array();  
  
  public function __construct($track_id, $login, $smpte = "") 
  {
    $this->id = (int)$track_id;
    $this->login = $login;
    $this->smpte = $smpte;
  }

  public function read() 
  {
    $sql = "record_id = '".$this->id."'"; // AND user_id = '".$this->uid()."'";
    if (!empty($this->smpte)) {
      $sql .= " AND cuepoint = '".$this->smpte."'";
    }    
    $sql .= " ORDER BY record_id ASC";
    
    $notes = db_select_all(TBL_PREFIX.TBL_HYPERNOTES, "*", $sql);
    foreach ($notes as $note) 
    {
      $this->data[] = array( 
                              "uid" => $note['user_id'],
                              "pos" => $note['cuepoint'],                            
														  "txt" => $note['hypernote']
                           );
    }
  }
  
  public function insert($data) 
  {
    $fields = $this->arrayFields($data);
    $names  = implode(",", array_keys($fields));
    $values = "'" . implode("','", array_values($fields)) . "'";
    // this table doesn't have an autoincrement column, so check for error (if any)
    return db_insert(TBL_PREFIX.TBL_HYPERNOTES, $names, $values) !== false;
  }

  public function update($data) 
  {
    $fields = $this->arrayFields($data);
    $tuples = array();
    foreach ($fields as $key => $value) {
      $tuples[] = $key."='".$value."'";
    }
    
    return db_update(TBL_PREFIX.TBL_HYPERNOTES, implode(",", $tuples), "record_id='".$this->id."'");
  }

  public function delete() 
  { 
    //$condition = "record_id='".$this->id."' AND cuepoint='".$this->smpte."' AND user_id='".$this->uid()."' LIMIT 1";
    $condition = "record_id='".$this->id."' AND CONCAT(cuepoint)='".$this->smpte."' AND CONCAT(user_id)='".$this->uid()."' LIMIT 1";
    return db_delete(TBL_PREFIX.TBL_HYPERNOTES, $condition);
  }
    
  public function getData($include_text = true) 
  {
    $this->read();
    $data = $this->data;
    if (!$include_text) foreach ($data as &$d) {
      unset($d['txt']);
    }
    
		return $data;
  }
  
  protected function uid()
  {
    $user = db_select(TBL_PREFIX.TBL_USERS, "id", "login='".$this->login."'");
    return $user['id'];
  }
  
  protected function arrayFields($data) 
  {
    return array(
                  "record_id" => $this->id,
                  "user_id"   => $this->uid(),
                  "cuepoint"  => $this->smpte,
                  "hypernote" => trim($data)
                );
  }
    
}
?>
