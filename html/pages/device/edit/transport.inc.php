<?php

if ($_POST['editing']) {
    if ($_SESSION['userlevel'] > '7') {
        $transport_type = mres($_POST['transporttype']);
        $transport_port = mres($_POST['transportport']);
        $transport_username = mres($_POST['transport_username']);
        $transport_password = mres($_POST['transport_password']);
        $transport_enablepassword = mres($_POST['transport_enablepassword']);

        var_dump($transport_password);
        $update_item = array();

        if($transport_type != $device['transport_type']){
          $update_item['transport_type']=$transport_type;
        }
        if($transport_port != $device['transport_port']){
           $update_item['transport_port']=$transport_port;
        }
        if($transport_username!= $device['transport_username']){
           $update_item['transport_username'] = $transport_username;
        }
        if($transport_password != $device['transport_password']){
           $update_item['transport_password']=$transport_password;
        }
        if($transport_enablepassword != $device['transport_enablepassword']){
           $update_item['transport_enablepassword'] = $transport_enablepassword;
        }


       if (count($update_item) == 0){
          $update_message = 'xxxxx Device record unchanged. No update necessary.';
          $update = -1;
       }
       else{
            $rows_updated = dbUpdate($update_item, 'devices', '`device_id` = ?', array($device['device_id']));
            if ($rows_updated > 0) {
                $update_message = $rows_updated.' Device record updated.';
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
         <div class="form-group">
              <div class="col-sm-12 alert alert-info">
                  <label class="control-label text-left input-sm">CLI Transport Configuration</label>
              </div>
          </div>
          <div class="form-group">
              <label for="transporttype" class="col-sm-3 control-label">Transport Type</label>
              <div class="col-sm-3">
                  <select name="transporttype" id="transporttype" class="form-control input-sm">
                    <?php
                         $options = array('none'=>'None','telnet'=>'Telnet','SSHv2'=>'SSH V2');
                         foreach($options as $option => $text){
                             if ($option == $device['transport_type']){
                                echo "<option value='".$option."' selected>$text</option>";
                             }else{
                               echo "<option value='".$option."'>$text</option>";
                             }
                         }
                    ?>
                  </select>
              </div>
              <div class="col-sm-3">
                  <?php
                      echo "<input type='text' name='transportport' class='flow-control input-sm' value='".$device['transport_port']."'>";
                  ?>
              </div>
              <div class="clearfix"></div>
          </div>
          <div class="form-group">
              <label for="clicredential" class="col-sm-3 control-label">Credential</label>
              <div class="col-sm-3">
                  <?php echo "<input type='text' name='transport_username' class='form-control input-sm' value='".$device['transport_username']."'>";?>
              </div>
              <div class="col-sm-3">
                 <?php echo "<input type='password' name='transport_password' class='form-control input-sm' value='".$device['transport_password']."'>";?>
              </div>
              <div class="col-sm-3">
                 <?php echo "<input type='enablepassword' name='transport_enablepassowrd' class='form-control input-sm' value='".$device['transport_enablepassword']."'>";?>
              </div>
          </div>
      
          <div class="col-sm-3">
               <button type="submit" name="Submit"  class="btn btn-default"><i class="fa fa-check"></i> Save</button>
          </div>
    </form>
