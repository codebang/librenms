<?php
if ($_POST['editing']) {
    if ($_SESSION['userlevel'] > "7") {
        $updated = 0;
      $update_item = array();
      if($config['enable_location_feature']){
        $dms_location = mres($_POST['dms_location']); 
        if($dms_location != $device['dms_location']){
           $update_item['dms_location']=$dms_location;
        } 
      }else{
        $override_sysLocation_bool = mres($_POST['override_sysLocation']);
        if (isset($_POST['sysLocation'])) {
            $override_sysLocation_string = $_POST['sysLocation'];
        }

        if ($device['override_sysLocation'] != $override_sysLocation_bool || $device['location'] != $override_sysLocation_string) {
            $updated = 1;
        }

        if ($override_sysLocation_bool) {
            $override_sysLocation = 1;
        }
        else {
            $override_sysLocation = 0;
        }
        $update_item['override_sysLocation'] = $override_sysLocation;

        if (isset($override_sysLocation_string)) {
            $update_item["location"] = $override_sysLocation_string;
        }
      }

       if (isset($_POST['description']) && $_POST['description'] != $device["description"]) {
          $update_item['description'] = $_POST['description'];
        }

       if (isset($_POST['sn']) && $_POST['sn'] != $device["sn"]) {
          $update_item['sn'] = $_POST['sn'];
        }   

        $update_item['type'] = $_POST['type'];
        #FIXME needs more sanity checking! and better feedback


        $rows_updated = dbUpdate($update_item, 'devices', '`device_id` = ?', array($device['device_id']));

        if ($rows_updated > 0 || $updated) {
            $update_message = "Device record updated.";
            $updated = 1;
            if($config['']){
                $device_proxy = array();
                if(isset($update_item['description'])){
                       $device_proxy['description'] = $update_item['description'];
                }
                if(isset($update_item['sn'])){
                       $device_proxy['sn'] = $update_item['sn'];
                }
                if(isset($update_item['dms_location'])){
                      $device_proxy['dms_location'] = $update_item['dms_location'];
                }
                if(count($device_proxy) > 0){
                      $device_proxy['hostname'] = $device['hostname'];
                      notify_dso_for_create_switch($device_proxy);
                }
            }
            $device = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = ?", array($device['device_id']));
        }
        elseif ($rows_updated = '-1') {
            $update_message = "Device record unchanged. No update necessary.";
            $updated = -1;
        }
        else {
            $update_message = "Device record update error.";
        }
    }
    else {
        include 'includes/error-no-perm.inc.php';
    }
}

$descr  = $device['purpose'];

$override_sysLocation = $device['override_sysLocation'];
$override_sysLocation_string = $device['location'];

if ($updated && $update_message) {
    print_message($update_message);
}
elseif ($update_message) {
    print_error($update_message);
}

?>
<h3> Device Settings </h3>
<div class="row">
    <div class="col-md-1 col-md-offset-2">
        <form id="delete_host" name="delete_host" method="post" action="delhost/" role="form">
            <input type="hidden" name="id" value="<?php echo($device['device_id']); ?>">
            <button type="submit" class="btn btn-danger" name="Submit"><i class="fa fa-trash"></i> Delete device</button>
        </form>
    </div>
    <div class="col-md-1 col-md-offset-2">
        <?php
        if($config['enable_clear_discovery'] == 1) {
        ?>
            <button type="submit" id="rediscover" data-device_id="<?php echo($device['device_id']); ?>" class="btn btn-primary" name="rediscover"><i class="fa fa-retweet"></i> Rediscover device</button>
        <?php
        }
        ?>
    </div>
</div>
<br>
<form id="edit" name="edit" method="post" action="" role="form" class="form-horizontal">
<input type=hidden name="editing" value="yes">
    <div class="form-group">
        <label for="descr" class="col-sm-2 control-label">Description:</label>
        <div class="col-sm-5">
            <?php
               echo  "<input type='text' id='description' name='description' class='form-control' value={$device['description']}></input>";
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="type" class="col-sm-2 control-label">Type:</label>
        <div class="col-sm-5">
            <select id="type" name="type" class="form-control">
                <?php
                $unknown = 1;

                foreach ($config['device_types'] as $type) {
                    echo('          <option value="'.$type['type'].'"');
                    if ($device['type'] == $type['type']) {
                        echo(' selected="1"');
                        $unknown = 0;
                    }
                    echo(' >' . ucfirst($type['type']) . '</option>');
                }
                if ($unknown) {
                    echo('          <option value="other">Other</option>');
                }
                ?>
            </select>
       </div>
    </div>
   <div class="form-group">
     <label for="sn" class="col-sm-2 control-label">Serial Number</label>
     <div class="col-sm-5">
        <?php 
           echo "<input type='text' id='sn' name='sn' class='form-control' value={$device['sn']}></input>";
        ?>
     </div>
   </div>
<?php if(!$config["enable_location_feature"]) { ?>
<div class="form-group">
    <label for="sysLocation" class="col-sm-2 control-label">Override sysLocation:</label>
    <div class="col-sm-6">
      <input onclick="edit.sysLocation.disabled=!edit.override_sysLocation.checked" type="checkbox" name="override_sysLocation"<?php if ($override_sysLocation) echo(' checked="1"'); ?> />
    </div>
</div>
<div class="form-group">
    <div class="col-sm-2"></div>
    <div class="col-sm-5">
      <input id="sysLocation" name="sysLocation" class="form-control" <?php if (!$override_sysLocation) echo(' disabled="1"'); ?> value="<?php echo($override_sysLocation_string); ?>" />
    </div>
</div>
<?php } else {?>
<div class="form-group">
     <label for="location" class="col-sm-2 control-label">Location</label>
     <div class="col-sm-5">
            <select name="dms_location" id="location" class="form-control input-sm" <?php if($device['dms_location'] != $config['device_default_location']) {echo('disabled="1"');} ?> >
                 <?php
                       $loc_db = $device['dms_location'];
                       $ret_arr = list_location();
                       $locations = $ret_arr['desc'];
                       array_unshift($locations,$config['device_default_location']);
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
<?php } ?>
<div class="form-group">
    <label for="disabled" class="col-sm-2 control-label">Disable:</label>
    <div class="col-sm-1">
      <input name="disabled" type="checkbox" id="disabled" value="1" <?php if ($device["disabled"]) echo("checked=checked"); ?> />
    </div>
</div>
<div class="form-group">
    <label for="ignore" class="col-sm-2 control-label">Ignore</label>
    <div class="col-sm-6">
       <input name="ignore" type="checkbox" id="ignore" value="1" <?php if ($device['ignore']) echo("checked=checked"); ?> />
    </div>
</div>
<div class="row">
    <div class="col-md-1 col-md-offset-2">
        <button type="submit" name="Submit"  class="btn btn-default"><i class="fa fa-check"></i> Save</button>
    </div>
</div>
</form>
<br />
<script>
    $("#rediscover").click(function() {
        var device_id = $(this).data("device_id");
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { type: "rediscover-device", device_id: device_id },
            dataType: "json",
            success: function(data){
                if(data['status'] == 'ok') {
                    toastr.success(data['message']);
                } else {
                    toastr.error(data['message']);
                }
            },
            error:function(){
                toastr.error('An error occured setting this device to be rediscovered');
            }
        });
    });
   $(document).ready(function() {
        $('#edit').bootstrapValidator({
          feedbackIcons: {
             valid: 'glyphicon glyphicon-ok',
             invalid: 'glyphicon glyphicon-remove',
             validating: 'glyphicon glyphicon-refresh'
          },
         fields: {
            description: {
                validators: {
                    notEmpty: {
                        message: 'Device Description is required.'
                    }
                }
            }
         }
   }) 
  });
</script>
<?php
print_optionbar_start();
list($sizeondisk, $numrrds) = foldersize($config['rrd_dir']."/".$device['hostname']);
echo("Size on Disk: <b>" . formatStorage($sizeondisk) . "</b> in <b>" . $numrrds . " RRD files</b>.");
print_optionbar_end();
?>

