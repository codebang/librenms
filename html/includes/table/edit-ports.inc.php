<?php

$row = 1;

$device_id = $_POST['device_id'];

$sql = 'FROM `ports` WHERE `device_id` = ?';
$param = array($device_id);

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`ifName` LIKE '$searchPhrase%' OR `ifAlias` LIKE '$searchPhrase%' OR `ifDescr` LIKE '$searchPhrase%')";
}

$count_sql = "SELECT COUNT(`port_id`) $sql";
$total     = dbFetchCell($count_sql, $param);


$device_sql = "SELECT hostname from devices where devices.device_id = ?";
$device_ip = dbFetchCell($device_sql,$param);
if (empty($total)) {
    $total = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = '`ifIndex` ASC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low  = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT * $sql";

if ($config['enable_workspace_lookup']){
$response[] = array(
    'ifIndex' => "<button id='save-form' type='submit' value='Save' class='btn btn-success btn-sm' title='Save current port disable/ignore settings'>Save</button><button type='submit' value='Reset' class='btn btn-danger btn-sm' id='form-reset' title='Reset form to previously-saved settings'>Reset</button>",
    'label' => '',
    'ifAdminStatus' => '',
    'ifOperStatus' => '',
    'Allocatable' => "<button type='submit' value='Toggle' class='btn btn-default btn-sm' id='disalloc-toggle' title='toggle all the selectd ports'>Toggle</button><button type='submit' value='Select' class='btn btn-default btn-sm' id='disalloc-select' title='Select all the ports'>Select All</button>",
    'ifAlias' => ''
);
$cache = getWorkstationForDevice($device_ip);
}
else{
  $response[] = array(
    'ifIndex' => "<button id='save-form' type='submit' value='Save' class='btn btn-success btn-sm' title='Save current port settings'>Save</button><button type='submit' value='Reset' class='btn btn-danger btn-sm' id='form-reset' title='Reset form to previously-saved settings'>Reset</button>",
    'label' => '',
    'ifAdminStatus' => '',
    'ifOperStatus' => "<button type='submit' value='Alerted' class='btn btn-default btn-sm' id='alerted-toggle' title='Toggle alerting on all currently-alerted ports'>Alerted</button><button type='submit' value='Down' class='btn btn-default btn-sm' id='down-select' title='Disable alerting on all currently-down ports'>Down</button>",
    'disabled' => "<button type='submit' value='Toggle' class='btn btn-default btn-sm' id='disable-toggle' title='Toggle polling for all ports'>Toggle</button><button type='submit' value='Select' class='btn btn-default btn-sm' id='disable-select' title='Disable polling on all ports'>Select All</button>",
    'ignore' => "<button type='submit' value='Toggle' class='btn btn-default btn-sm' id='ignore-toggle' title='Toggle alerting for all ports'>Toggle</button><button type='submit' value='Select' class='btn btn-default btn-sm' id='ignore-select' title='Disable alerting on all ports'>Select All</button>",
    'ifAlias' => ''
);
}

$ports = dbFetchRows($sql, $param);


foreach ($ports as $port) {
    $port = ifLabel($port);

    // Mark interfaces which are OperDown (but not AdminDown) yet not ignored or disabled, or up yet ignored or disabled
    // - as to draw the attention to a possible problem.

   if($config['enable_workspace_lookup']){
      //update the mismatching
      $ws = getwsfromcache($cache,$port);
      if ($ws != $config['port_unbind_constant'] && $port['allocatable'] == 0){
            $updated = dbUpdate(array('allocatable' => 1), 'ports', '`port_id` = ?', array($port['port_id']));
            $port['allocatable'] = 1;
            $port['ws_binding'] = true;

      }
      else if ($ws != $config['port_unbind_constant']){
         $port['ws_binding'] = true;
      }
      else{
         $port['ws_binding'] = false;
      }
      
   }
    
    $isportbad = ($port['ifOperStatus'] == 'down' && $port['ifAdminStatus'] != 'down') ? 1 : 0;
    $dowecare  = ($port['ignore'] == 0 && $port['disabled'] == 0) ? $isportbad : !$isportbad;
    $outofsync = $dowecare ? " class='red'" : '';
    $checked = '';
    $device['device_id'] = $device_id;
    if (get_dev_attrib($device, 'ifName_tune:'.$port['ifName']) == "true") {
        $checked = 'checked';
    }
    if ($config['enable_workspace_lookup']){
    $response[] = array(
        'ifIndex'          => $port['ifIndex'],
        'ifName'           => $port['label'],
        'ifAdminStatus'    => $port['ifAdminStatus'],
        'ifOperStatus'     => '<span name="operstatus_'.$port['port_id'].'"'.$outofsync.'>'.$port['ifOperStatus'].'</span>',
        'Allocatable'      => '<input type="checkbox" class="disable-check" name="alloc_'.$port['port_id'].'"'.($port['allocatable'] ? 'checked' : '' ).($port['ws_binding']?' disabled=disabled' : '').'>
                               <input type="hidden" name="oldalloc_'.$port['port_id'].'" value="'.($port['allocatable'] ? 1 : 0).'"">',
        'ifAlias'          => '<div class="form-group"><input class="form-control input-sm" id="if-alias" name="if-alias" data-device_id="'.$port['device_id'].'" data-port_id="'.$port['port_id'].'" data-ifName="'.$port['label'].'" value="'.$port["ifAlias"].'"><span class="glyphicon form-control-feedback" aria-hidden="true"></span></div>',
    );
}
else{
      $response[] = array(
        'ifIndex'          => $port['ifIndex'],
        'ifName'           => $port['label'],
        'ifAdminStatus'    => $port['ifAdminStatus'],
        'ifOperStatus'     => '<span name="operstatus_'.$port['port_id'].'"'.$outofsync.'>'.$port['ifOperStatus'].'</span>',
        'disabled'         => '<input type="checkbox" class="disable-check" name="disabled_'.$port['port_id'].'"'.($port['disabled'] ? 'checked' : '').'>
                               <input type="hidden" name="olddis_'.$port['port_id'].'" value="'.($port['disabled'] ? 1 : 0).'"">',
        'ignore'           => '<input type="checkbox" class="ignore-check" name="ignore_'.$port['port_id'].'"'.($port['ignore'] ? 'checked' : '').'>
                               <input type="hidden" name="oldign_'.$port['port_id'].'" value="'.($port['ignore'] ? 1 : 0).'"">',
        'port_tune'        => '<input type="checkbox" id="override_config" name="override_config" data-attrib="ifName_tune:'.$port['ifName'].'" data-device_id="'.$port['device_id'].'" data-size="small" '.$checked.'>',
        'ifAlias'          => '<div class="form-group"><input class="form-control input-sm" id="if-alias" name="if-alias" data-device_id="'.$port['device_id'].'" data-port_id="'.$port['port_id'].'" data-ifName="'.$port['label'].'" value="'.$port['ifAlias'].'"><span class="glyphicon form-control-feedback" aria-hidden="true"></span></div>',
    );
}

}//end foreach

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo _json_encode($output);
