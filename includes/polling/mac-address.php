<?php
   require '../../config.php';
   require '../../test.php';
   if ($device['os'] == 'comware'){
      $cmd = '"display mac-address"';
      exec("python /opt/librenms/device_executor.py -d {$device['hostname']} -u {$device['transport_username']} -a {$device['transport_password']} -m {$device['os']} {$cmd}",$array,$ret);
      $text =  implode("\r\n",$array);
      echo $text;
      exec("python /opt/librenms/python_scripts/mac_address.py -d {$device['hostname']} -s {$config['redis_server']} -p {$config['redis_port']}  -t '{$text}'");
   }
?>
