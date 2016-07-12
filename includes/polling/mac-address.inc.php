<?php
   if ($device['os'] == 'comware'){
   		poll_mactable($device['hostname'],$device['transport_username'],$device['transport_password'],$device['os']);
   }
?>
