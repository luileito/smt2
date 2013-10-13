<?php
/**
 * This class is a wrapper layer for all required MySQL queries.
 * It is highly recommended to use them instead of the current PHP functions,
 * because in that way it is possible to abstract this layer 
 * and use different database engines.
 * @date 27/March/2009    
 */
 
/** @global array   look for (smt)2 tables */
$_lookupTables = array(TBL_RECORDS,TBL_CACHE,TBL_BROWSERS,TBL_OS,TBL_USERS,TBL_ROLES,TBL_EXTS,TBL_CMS,TBL_JSOPT);

/** 
 * Opens or reuses a connection against the database server. 
 * @return  resource    connection link identifier   
 */
function db_connect() 
{
  $idcnx = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or trigger_error( mysql_error() );
  mysql_select_db(DB_NAME, $idcnx) or trigger_error( mysql_error() );
  
  return $idcnx;
}

/** 
 * Performs a generic query to database.
 * @param   string    $sql  SQL query  
 * @return  resource        Resource query identifier   
 */
function db_query($sql) 
{
  $cnx = db_connect();
  $res = mysql_query($sql, $cnx) or trigger_error( mysql_error() );
  
  return $res; 
}

/** 
 * Gets column(s) value(s) of a single row from a table.
 * This function can be used also to check if a row field exists.
 * @param   string    $column     column(s) name(s) 
 * @param   string    $table      table name
 * @param   string    $condition  WHERE condition. To allow row ordering without WHERE clause, you can use "1 ORDER BY ..."  
 * @return  mixed                 Associative array with column(s) name(s) as keys on success or FALSE on failure  
 */
function db_select($table, $column, $condition)
{
  $sql = "SELECT $column FROM $table WHERE $condition";
  $res = db_query($sql);
  if (mysql_num_rows($res) > 0) {
    return mysql_fetch_assoc($res);
  }
  
  return false;
}

/** 
 * Selects ALL rows from table that match the given condition.
 * @param   string  $table      table name
 * @param   string  $column     column name
 * @param   string  $condition  WHERE condition. To allow row ordering without WHERE clause, you can use "1 ORDER BY ..."
 * @return  array               Array with all queried rows.
 */
function db_select_all($table, $column, $condition)
{
  $sql = "SELECT $column FROM $table WHERE $condition";
  
  $res = db_query($sql);
  // get ALL rows
  $opt = array();
  while ($row = mysql_fetch_assoc($res)) {
    $opt[] = $row;
  }
  
  return $opt;
}

/** 
 * Deletes a row from table. 
 * @param   string  $table      able name 
 * @param   string  $condition  WHERE clause (i.e: id='#' LIMIT 1)
 * @return  boolean             TRUE on success, or FALSE on failure   
 */
function db_delete($table, $condition)
{
  $sql = "DELETE FROM $table WHERE $condition";
  $res = db_query($sql);
  
  return $res;
}

/** 
 * Inserts a new row on table.  
 * @param   string    $table      table name
 * @param   string    $fields     column(s) name(s) 
 * @param   string    $values     column(s) value(s)
 * @return  int                   last instered row id, or FALSE on failure   
 */
function db_insert($table, $fields, $values)
{
  $sql = "INSERT INTO $table ($fields) VALUES ($values)";
  $res = db_query($sql);
  
  return mysql_insert_id();
}

/** 
 * Updates a row on table.
 * @param   string  $table      table name
 * @param   string  $tuples     column(s) name(s) and value(s) in the form column=value (i.e: col='val',foo='val',...)
 * @param   string  $condition  WHERE clause (i.e: id='#' LIMIT 1) 
 * @return  boolean             TRUE on success, or FALSE on failure   
 */
function db_update($table, $tuples, $condition)
{
  $sql = "UPDATE $table SET $tuples WHERE $condition";
  $res = db_query($sql);
  
  return $res;
}

/** 
 * Checks that both database connection and tables are OK. 
 * @return  boolean   TRUE on sucess, or FALSE on failure   
 */
function db_check() 
{
  global $_lookupTables;

  foreach ($_lookupTables as $table) {
    $res = db_query("SHOW TABLES LIKE '".TBL_PREFIX.$table."'", $cnx);
    if (!mysql_num_rows($res)) {
      return false;
    }
  }
  
  return true;
}

/** 
 * Shortcut for getting the total number of (smt) records in database, or alternatively the column names. 
 * @param   boolean  $getColNames	return column names instead of number of records
 * @return  mixed   						Number of total DB entries (int) or column names (array)
 */
function db_records($getColNames = false) 
{
  $n = ($getColNames) ? "*" : "id";
  $res = db_query("SELECT $n FROM ".TBL_PREFIX.TBL_RECORDS);
  
  if ($getColNames) {
    $i = 0;
  	while ($i < mysql_num_fields($res)) {
  		$meta = mysql_fetch_field($res, $i); 
  		$headers[] = $meta->name;
  		++$i;
  	}
  	return $headers;
  } else {
    return mysql_num_rows($res);
  } 
}

/** 
 * Selects one (and only one) row from an "options" table (CMS or JSOPT).
 * This function is a wrapper for 'db_select()'.
 * Instead of having to deal with an associative array of one key alone, 
 * this function speeds the process and returns the array member value.
 * @param   string  $table        table name
 * @param   string  $optionName   option name stored on "name" column 
 * @return  string                Option value
 */
function db_option($table, $optionName)
{ 
  $row = db_select($table, "value", "name='".$optionName."'");
  
  return $row['value'];
}
?>
