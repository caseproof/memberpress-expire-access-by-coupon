<?php
/*
Plugin Name: Expire Access by Coupon Code (for MemberPress)
Plugin URI: http://www.memberpress.com/
Description: Expires all Transactions in MemberPress that are associated with a given coupon code.
Version: 1.0.0
Author: Caseproof, LLC
Author URI: http://caseproof.com/
Copyright: 2004-2013, Caseproof, LLC
*/

function cbc_show_menu_page()
{
  ?>
  <div class="wrap">
  <h2>Expire Transactions by Coupon Code</h2>
  <?php
  if(isset($_GET['cbc_saved']) && $_GET['cbc_saved'] == 'true')
    echo '<div class="updated"><p><strong>Transactions have been cancelled.</strong></p></div>';
  
  if(isset($_GET['cbc_saved']) && $_GET['cbc_saved'] == 'false')
    echo '<div class="updated"><p><strong>An error occurred, please make sure you entered a valid coupon code.</strong></p></div>';
  ?>
    <br/><br/>
    <form action="" method="post">
      <label>Coupon Code:</label>
      <br/>
      <input type="text" name="cbc_code" />
      <br/><br/>
      <input type="submit" name="cbc_code_submit" value="Cancel Transactions" />
    </form>
  </div>
  <?php
}

function cbc_catch_post()
{
  global $wpdb;
  $mepr_db = new MeprDb();
  
  if(!isset($_POST['cbc_code_submit']) || empty($_POST['cbc_code_submit']) || empty($_POST['cbc_code']))
    return;
  
  $code = stripslashes($_POST['cbc_code']);
  
  $q = "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = %s";
  $id = $wpdb->get_var($wpdb->prepare($q, $code, MeprCoupon::$cpt));
  
  if(!$id)
  {
    wp_redirect(admin_url('admin.php?page=cancel_by_coupon&cbc_saved=false'));
    die();
  }
  
  $now = date('c');
  $wpdb->query("UPDATE {$mepr_db->transactions} SET `expires_at` = '{$now}' WHERE `coupon_id` = {$id}");
  
  wp_redirect(admin_url('admin.php?page=cancel_by_coupon&cbc_saved=true'));
  die();
}

function cbc_add_menu_page()
{
 add_submenu_page('memberpress', __('Expire Access', 'memberpress'), __('Expire Access', 'memberpress'), 'administrator', 'cancel_by_coupon', 'cbc_show_menu_page');
}

add_action('admin_init', 'cbc_catch_post');
add_action('mepr_menu', 'cbc_add_menu_page');
