<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require INC_PATH.'sys/logincheck.php';
// now you have access to all (smt) API functions and constants
include INC_PATH.'inc/header.php';

// retrieve extensions 
$MODULES = ext_available();
// get all roles
$ROLES = db_select_all(TBL_PREFIX.TBL_ROLES, "*", "1");
// query DB once
$ROOT = is_root();
// helper function
function table_row($role, $new = false) 
{
  global $MODULES,$ROOT;
  
  $self = ($role['id'] == $_SESSION['role_id']);
  
  // wrap table row in a form, so each user can be edited separately
  $row .= '<form action="saveroles.php" method="post">';
  $row .= '<tr>';
  $row .= ' <td>';
  $row .= (!$new) ? '<strong>'.$role['name'].'</strong>' : 
                    '<input type="text" class="text center" name="name" value="new role?" size="10" maxlength="100" />';
  $row .= ' </td>';
  
  $allowed = explode(",", $role['ext_allowed']);
  // check available extensions    
  foreach ($MODULES as $module) 
  {
    // disable admin roles (they have wide access)
    $disabled = ($self || ($role['id'] == 1 && !$new)) ? ' disabled="disabled"' : null;
    // look for registered users' roles
    $checked = ($role['id'] == 1 && !$new || in_array($module, $allowed)) ? ' checked="checked"' : null;
    $row .= '<td><input type="checkbox" name="exts[]" value="'.$module.'"'.$checked.$disabled.' /></td>';
  }
  
  // superuser cannot be edited
  $row .= ' <td>';
  if ($role['id'] == 1) 
  {
    $row .= '<em>not editable</em>';
  } 
  else 
  {
    $row .= '<input type="hidden" name="submit" value="manage" />';
    if ($new) {
      $row .= '<input type="image" src="'.ADMIN_PATH.'css/add.png" name="create" alt="create" title="Create new role" />'; 
    } else {
      // if a role is allowed to this section, they should be able to change/create roles 
      $row .= '<input type="hidden" name="id" value="'.$role['id'].'" />';
      $row .= ' <input type="image" src="'.ADMIN_PATH.'css/accept.png" name="update" alt="update" title="Update role" />';
      if ($ROOT) {
        $row .= ' <input type="image" src="'.ADMIN_PATH.'css/remove.png" name="delete" alt="delete" class="ml del" title="Delete role" />';
      }
    }
  }
  $row .= ' </td>';
  $row .= '</tr>';
  $row .= '</form>';
  
  return $row;
}
?>

<h1 id="manage">Manage Roles</h1>

<?php check_notified_request("manage"); ?>

<table border="0" cellpadding="10" cellspacing="1" class="mb">
  <caption>check those sections that each role can access to</caption>
  <thead>
    <tr>
      <th>role name</th>
      <?php
      foreach ($MODULES as $mod) { echo '<th>'.filename_to_str($mod).'</th>'; }
      ?>
      <th>action</th>
    </tr>
  </thead>
  <!-- NOW SHOULD BE VALIDATION ERRORS, BECAUSE FORMS CANNOT WRAP THE WHOLE TABLE ROW, but who cares? -->
  <tbody>
    <?php
    foreach ($ROLES as $role) { echo table_row($role); }
    ?>
  </tbody>
  <tbody>
    <tr><td colspan="<?=count($MODULES)+2?>">...</td></tr>
    <?php
    // add one row more for creating a new role
    echo table_row(0, true);
    ?>
  </tbody>
</table>



<h1 id="describe">Describe Roles</h1>

<?php check_notified_request("describe"); ?>

<p>
  Here you can set or change each role's properties. 
  Registered users will see their role description when accessing their user area.
</p>

<form action="saveroles.php" method="post">
  <fieldset>
  <?php
  foreach ($ROLES as $role) 
  { 
    $disabled = ($role['id'] == 1 && !$ROOT) ? ' disabled="disabled"' : null;
    
    $rnd = mt_rand(); // to match label with id correctly
    $f .= '<fieldset>';
    
    $f .= '<div class="fl mr">';
    $f .= '<label for="check'.$rnd.'">change</label>'; 
    $f .= '<input type="checkbox" id="check'.$rnd.'" name="check[]" class="block"'.$disabled.' value="'.$role['id'].'" />';
    $f .= '</div>';
    
    $f .= '<div class="fl mr">';
    $f .= '<label for="name'.$rnd.'">name</label>';
    $f .= '<input type="text" id="name'.$rnd.'" name="name['.$role['id'].']" size="10" maxlength="100" class="text block"'.$disabled.' value="'.$role['name'].'" />';
    $f .= '</div>'; 
    
    $f .= '<div class="fl mr">';
    $f .= '<label for="description'.$rnd.'">description</label>';
    $f .= '<input type="text" id="description'.$rnd.'" name="description['.$role['id'].']" size="80" maxlength="255" class="text block"'.$disabled.' value="'.$role['description'].'" />';
    $f .= '</div>';
    
    $f .= '</fieldset>';
  }
  echo $f;
  ?>
  <input type="hidden" name="submit" value="describe" />
  <input type="submit" class="button round" value="update roles" />
  </fieldset>
</form>

<script type="text/javascript">
// <![CDATA[
$(function(){
  $('.del').click(function(e){
    return confirm("Are you sure?");
  });
});
// ]]>
</script>

<?php include INC_PATH.'inc/footer.php'; ?>