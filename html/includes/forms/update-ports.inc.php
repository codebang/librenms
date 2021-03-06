<?php
header('Content-type: application/json');

if (is_admin() === false) {
    $response = array(
        'status'  => 'error',
        'message' => 'Need to be admin',
    );
    echo _json_encode($response);
    exit;
}

$status           = 'error';
$message          = 'Error with config';

// enable/disable ports/interfaces on devices.
$device_id    = intval($_POST['device']);
$rows_updated = 0;
$delete_ports = array();
$add_ports = array();
$port_updates = array();
foreach ($_POST as $key => $val) {
    if (strncmp($key, 'oldign_', 7) == 0) {
        // Interface identifier passed as part of the field name
        $port_id = intval(substr($key, 7));

        $oldign = intval($val) ? 1 : 0;
        $newign = $_POST['ignore_'.$port_id] ? 1 : 0;

        // As checkboxes are not posted when unset - we effectively need to do a diff to work
        // out a set->unset case.
        if ($oldign == $newign) {
            continue;
        }

        $n = dbUpdate(array('ignore' => $newign), 'ports', '`device_id` = ? AND `port_id` = ?', array($device_id, $port_id));

        if ($n < 0) {
            $rows_updated = -1;
            break;
        }

        $rows_updated += $n;
    }
    else if (strncmp($key, 'olddis_', 7) == 0) {
        // Interface identifier passed as part of the field name
        $port_id = intval(substr($key, 7));

        $olddis = intval($val) ? 1 : 0;
        $newdis = $_POST['disabled_'.$port_id] ? 1 : 0;

        // As checkboxes are not posted when unset - we effectively need to do a diff to work
        // out a set->unset case.
        if ($olddis == $newdis) {
            continue;
        }

        $n = dbUpdate(array('disabled' => $newdis), 'ports', '`device_id` = ? AND `port_id` = ?', array($device_id, $port_id));

        if ($n < 0) {
            $rows_updated = -1;
            break;
        }

        $rows_updated += $n;
    }//end if
    else if (strncmp($key, 'oldalloc_', 9) == 0) {
        // Interface identifier passed as part of the field name
        $port_id = intval(substr($key, 9));

        $oldalloc = intval($val) ? 1 : 0;
        $newalloc = $_POST['alloc_'.$port_id] ? 1 : 0;

        // As checkboxes are not posted when unset - we effectively need to do a diff to work
        // out a set->unset case.
        if ($oldalloc == $newalloc) {
            continue;
        }
        $port_obj = get_port_by_id($port_id);
        if($newalloc){
            $add_ports[] = $port_obj;
        }
        else{
           $delete_ports[] = $port_obj;
        }
        $n = dbUpdate(array('allocatable' => $newalloc), 'ports', '`device_id` = ? AND `port_id` = ?', array($device_id, $port_id));

        if ($n < 0) {
            $rows_updated = -1;
            break;
        }
        $port_updates[$port_id] = $newalloc;

        $rows_updated += $n;
    }
}//end foreach

$device = device_by_id_cache($device_id);

if(count($add_ports) > 0){
  notify_dso_for_port($device['hostname'],$add_ports,false);
}

if(count($delete_ports) > 0){
  notify_dso_for_port($device['hostname'],$delete_ports,true);
}


if ($rows_updated > 0) {
    $message = $rows_updated.' Device record updated.';
    $status         = 'ok';
}
else if ($rows_updated = '-1') {
    $message = 'Device record unchanged. No update necessary.';
    $status         = 'ok';
}
else {
    $message = 'Device record update error.';
}

$response = array(
    'status'        => $status,
    'message'       => $message,
    'port_alloc'    => $port_updates      
);
echo _json_encode($response);
