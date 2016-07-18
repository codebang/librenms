<?php
   $config = array();
   $config['install_dir'] = '/opt/librenms';
   $config['redis_server'] = '192.168.56.12';
   $config['redis_port'] = 6379;
   include "/opt/librenms/includes/dms.inc.php";   
   print_r("test get work station from port\n");
   var_dump(getWorkstationForDevice('10.74.113.119'));
   $ret = getWorkstationForDevice('10.74.113.119');
   var_dump(isset($ret['Ethernet1/0/1']));
#   print_r("test list locations\n");
#   var_dump(list_location());
#   print_r("test show configuration\n");
   #var_dump(show_config('192.168.1.1','root','cisco123','comware'));
#   print_r("test poll mactable");
#   var_dump(poll_mactable('192.168.1.1','root','cisco123','comware'));
#   print_r("test port enable\n");
#   $port = array('port'=>'Ethernet1/0/5','vlan'=>'20');
#   $ports = array();
#   $ports[]= $port;
#   var_dump(port_enable('192.168.1.1','root','cisco123','comware',$ports));
   
?>
