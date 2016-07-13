<?php

/**
  enable port and configure vlan
*/

function show_config($sw_manageip,$username, $password, $os){
   $cmd = 'display current';
   $args = "-d {$swtich_manageip} -u {$username} -a {$password} -m {$os} '{$cmd}'";
   return _exec_python_with_ret("device_executor",$args);
}

function poll_mactable($switch_manageip,$username,$password,$os){
   $cmd = 'display mac-address';
   $args = "-d {$swtich_manageip} -u {$username} -a {$password} -m {$os} '{$cmd}'";
   $ret_arr = _exec_python_with_ret("device_executor",$args);
   if ($ret_arr['result'] == 'FAILURE'){
      return $ret_arr;
   }
   $array = $ret_arr['desc'];
   $text =  implode("\r\n",$array);
   $args = "-d {$device['hostname']} -s {$config['redis_server']} -p {$config['redis_port']}  -t '{$text}'";
   return  _exec_python_with_ret("mac_address",$args);
}

function port_enable($switch_manageip,$username,$password,$os,$ports){
   $cmd = '"system-view;';
   foreach($ports as $port_info){
      $cmd = $cmd."interface {$port_info['port']};undo shutdown;port access vlan {port_info['vlan']};quit;";
   }
   $cmd = $cmd."save;Y;\n".'"';
   $args = "-d {$swtich_manageip} -u {$username} -a {$password} -m {$os} '{$cmd}'";
   return _exec_python_with_ret("device_executor",$args);

}

function create_switch_for_dms($request_body){
    $args = "createswitch '".$request_body."'";
    return  _exec_python_with_ret("dms_manager",$args);
}

function update_switch_dms($switch_ip,$accountname,$dms_location){
   $args = "updateswitch {$swtich_ip} {$accountname} {$dms_location}" ;
   return  _exec_python_with_ret("dms_manager",$args);
} 

function list_accountname(){
  $args = "listaccountnames";
  return  _exec_python_with_ret("dms_manager",$args);
}

function list_location(){
  $args = "listlocations";
  return  _exec_python_with_ret("dms_manager",$args);
}

function getWorkStationFromPort($switch_manageip,$port){
  $args = "getworkstationfromport '{$port}'";
  $ret_arr = _exec_python_with_ret("dms_manager",$args);
  if ($ret_arr['result'] == "SUCCESS"){
       $ws = $ret_arr['desc'];    
       if ($ws == 'none'){
          return 'UnFound';
       }
       else{
          return $ws;
       }
  }
  else{
     return "UnFound";
  }
}


function _exec_python_with_ret($script,$args){
   $install_dir = $config['install_dir'];
   $cmd = "python ".$install_dir."/python_scripts/{$script}.py {$args}";
   exec($cmd,$ret_desc,$ret_code);
   if ($ret_code < 0){
      $result = "FAILURE";
   }
   else{
      $result = "SUCCESS";
   }
   return array("result" => $result,"desc" =>$ret_desc);
}

?>




