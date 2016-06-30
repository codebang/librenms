<?php

if ($_POST['editing']) {
    if ($_SESSION['userlevel'] > '7') {
        $accountName = mres($_POST['accountName']);
        $dms_location = mres($_POST['dms_location']);

        $update_item = array();

        if($accountName != $device['account_name']){
          $update_item['account_name']=$accountName;
        }
        if($dms_location != $device['dms_location']){
           $update_item['dms_location']=$dms_location;
        }


       if (count($update_item) == 0){
          $update_message = 'Device record unchanged. No update necessary.';
          $update = -1;
       }
       else{
            $rows_updated = dbUpdate($update_item, 'devices', '`device_id` = ?', array($device['device_id']));
            if ($rows_updated > 0) {
                $update_message = $rows_updated.' Device record updated.';
                   $install_dir = $config['install_dir'];
                   $dso_url = $config['dso_url'];
                   $redis_server = $config['redis_server'];
                   $redis_port = $config['redis_port'];
                   $cmd = "python {$install_dir}/python_scripts/dms_manager.py -d {$dso_url} -r {$redis_server} updateswitch_dms {$device['hostname']} {$accountName} {$dms_location} ";
                   echo $cmd;
                   exec($cmd,$ret_desc,$ret_code);
                   if ($ret_code < 0){
                      print_error('failed to notify dso.');
                   }
                   else{
                     print_message($ret_desc[0]);
                   }
                   $updated        = 1;
            }
            else if ($rows_updated = '-1') {
                $update_message = 'Device record unchanged. No update necessary.';
                $updated        = -1;
            }
            else {
                $update_message = 'Device record update error.';
                $updated        = 0;
            }
       }

       $device = dbFetchRow('SELECT * FROM `devices` WHERE `device_id` = ?', array($device['device_id']));
       if ($updated && $update_message) {
           print_message($update_message);
        }
       else if ($update_message) {
            print_error($update_message);
        }

    }
    else {
        include 'includes/error-no-perm.inc.php';
    }//end if
}//end if

?>
    <form name="form1" method="post" action="" class="form-horizontal" role="form">
          <input type=hidden name='editing' value='yes'>
          <div class='form-group'>
              <div class="col-sm-12 alert alert-info">
                  <label class="control-label text-left input-sm">DMS Configuration(optional)</label>
              </div>
          </div>
          <div class="form-group">
              <label for="accountName" class="col-sm-3 control-label">Account Name</label>
              <div class="col-sm-9">
                  <select name="accountName" id="accountName" class="form-control input-sm">
                      <?php
                           $an_db = $device['account_name'];
                           $loc_db = $device['dms_location'];
                           $cmd="python ".$config["install_dir"]."/python_scripts/dms_manager.py -d {$config['dso_url']} -r {$config['redis_server']} listaccountnames";
                           exec($cmd,$accounts,$ret);
                           array_unshift($accounts,"none");
                           foreach ($accounts as $account){
                               if ($an_db == $account){
                                  echo "<option value={$account} selected>{$account}</option>";
                               }
                               else{
                                  echo "<option value={$account}>{$account}</option>";
                               }
                           }
                      ?>
                  </select>
              </div>
          </div>
          <div class="form-group">
              <label for="location" class="col-sm-3 control-label">location</label>
              <div class="col-sm-9">
                  <select name="dms_location" id="location" class="form-control input-sm">
                      <?php
                           $loc_db = $device['dms_location'];
                           $cmd="python ".$config["install_dir"]."/python_scripts/dms_manager.py -d {$config['dso_url']} -r {$config['redis_server']} listlocations";
                           exec($cmd,$locations,$ret);
                           array_unshift($locations,"none");
                           foreach ($locations as $location){
                               if ($loc_db == $location){
                                 echo "<option value={$location} selected>{$location}</option>";
                               }
                               else{
                                 echo "<option value={$location}>{$location}</option>";
                               }
                           }
                      ?>
                  </select>
              </div>
          </div>
          <div class="col-sm-3">
               <button type="submit" name="Submit"  class="btn btn-default"><i class="fa fa-check"></i> Save</button>
          </div>
    </form>
