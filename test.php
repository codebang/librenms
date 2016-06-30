<?php
require 'includes/snmp.inc.php';
function notify_dso_for_create_switch($device){
   global $config;
   $install_dir = $config['install_dir'];
   $dso_url = $config['dso_url'];
   $redis_server = $config['redis_server'];
   $redis_port = $config['redis_port'];
   $switch = array();
   $swtich ['description'] = 'switch';
   $switch['managementIp'] = $device['hostname'];
   $port_stats = array();
   $port_stats = snmpwalk_cache_oid($device, 'ifDescr', $port_stats, 'IF-MIB');
   $port_stats = snmpwalk_cache_oid($device, 'ifName', $port_stats, 'IF-MIB');
   $port_stats = snmpwalk_cache_oid($device, 'ifType', $port_stats, 'IF-MIB');
   d_echo($port_stats);
   $ports = array();
   foreach ($port_stats as $ifIndex => $port){
      $port = array();
      $port['name'] = $port;
      array_push($ports,$port);
   }
   $switch['ports'] = $ports;
   $swtich['portcount'] = count($ports);
   $output = json_encode($switch);
   $cmd = "{$install_dir}/python_scripts/dso_manager.py -d {$dso_url} -r {$redis_server} createswitch ".'"{$output}"';
   echo $cmd;
   exec($cmd,$ret_desc,$ret_code);
   if($ret_code <0 ){
       print_error($ret_desc);
   }
   else{
       print_message($ret_desc);
   }

}
   $device = array('hostname' => '10.74.113.119',
        'sysName' => 'h3c',
        'community' => 'dms',
        'port' => '161',
        'transport' => 'udp',
        'status' => '1',
        'snmpver' => 'v2c',
        'poller_group' => '',
        'status_reason' => '',
        'port_association_mode' => 'ifindex',
        'transport_type' => 'ssh',
        'transport_port' => '22',
        'transport_username' => 'root',
        'transport_password' => 'cisco123',
        'transport_enablepassword' => '',
        'account_name' => '',
        'dms_location' => '',
        'os' => 'comware'
    );
    $config = array('install_dir' => '/opt/librenms','dso_url' => 'http://10.74.113.56:8282/dso/sa/switch','redis_server'=> '10.74.113.133','redis_port' => 6379);
    
    notify_dso_for_create_switch($device);
?>
