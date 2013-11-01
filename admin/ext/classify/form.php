<p>Choose a domain to analyze</p>
<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
  <?php
  $s  = '<select id="domain" name="domain_id" class="mr">';
  $s .= '<option value="">---</option>';
  // FIXME: couple domain IDs to user roles
  // This would allow to limit which domains can be inspected, e.g. "id < 3 ORDER BY id DESC" 
  $rows = db_select_all(TBL_PREFIX.TBL_DOMAINS, "id, domain", "1 ORDER BY id DESC"); // GROUP BY domain?
  foreach ($rows as $row) {
    $select = (isset($_SESSION['domain_id']) && $row['id'] == $_SESSION['domain_id']) ? 'selected="selected"' : null;
    $s .= '<option '.$select.' value="'.$row['id'].'">'.$row['domain'].'</option>';            
  }
  $s .= '</select>';
  echo $s;
  ?>
  <input type="submit" class="button round" value="Classify" />
</form>
