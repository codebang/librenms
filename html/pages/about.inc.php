<?php
$pagetitle[] = 'About';
$git_log = `git log -10`;
?>
<div class="modal fade" id="git_log" tabindex="-1" role="dialog" aria-labelledby="git_log_label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Local git log</h4>
      </div>
      <div class="modal-body">
<pre><?php echo $git_log; ?></pre>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div style="margin: 10px;">
  <div style="float: right; padding: 0px; width: 49%">

    <h3>Statistics</h3>

<?php
$stat_devices    = dbFetchCell('SELECT COUNT(device_id) FROM `devices`');
$stat_ports      = dbFetchCell('SELECT COUNT(port_id) FROM `ports`');
$stat_syslog     = dbFetchCell('SELECT COUNT(seq) FROM `syslog`');
$stat_events     = dbFetchCell('SELECT COUNT(event_id) FROM `eventlog`');
$stat_apps       = dbFetchCell('SELECT COUNT(app_id) FROM `applications`');
$stat_services   = dbFetchCell('SELECT COUNT(service_id) FROM `services`');
$stat_storage    = dbFetchCell('SELECT COUNT(storage_id) FROM `storage`');
$stat_diskio     = dbFetchCell('SELECT COUNT(diskio_id) FROM `ucd_diskio`');
$stat_processors = dbFetchCell('SELECT COUNT(processor_id) FROM `processors`');
$stat_memory     = dbFetchCell('SELECT COUNT(mempool_id) FROM `mempools`');
$stat_sensors    = dbFetchCell('SELECT COUNT(sensor_id) FROM `sensors`');
$stat_toner      = dbFetchCell('SELECT COUNT(toner_id) FROM `toner`');
$stat_hrdev      = dbFetchCell('SELECT COUNT(hrDevice_id) FROM `hrDevice`');
$stat_entphys    = dbFetchCell('SELECT COUNT(entPhysical_id) FROM `entPhysical`');

$stat_ipv4_addy = dbFetchCell('SELECT COUNT(ipv4_address_id) FROM `ipv4_addresses`');
$stat_ipv4_nets = dbFetchCell('SELECT COUNT(ipv4_network_id) FROM `ipv4_networks`');
$stat_ipv6_addy = dbFetchCell('SELECT COUNT(ipv6_address_id) FROM `ipv6_addresses`');
$stat_ipv6_nets = dbFetchCell('SELECT COUNT(ipv6_network_id) FROM `ipv6_networks`');

$stat_pw    = dbFetchCell('SELECT COUNT(pseudowire_id) FROM `pseudowires`');
$stat_vrf   = dbFetchCell('SELECT COUNT(vrf_id) FROM `vrfs`');
$stat_vlans = dbFetchCell('SELECT COUNT(vlan_id) FROM `vlans`');

$callback_status = dbFetchCell("SELECT `value` FROM `callback` WHERE `name` = 'enabled'");
if ($callback_status == 1) {
     $stats_checked = 'checked';
}
else {
     $stats_checked = '';
}


echo "
<div class='table-responsive'>
    <table class='table table-condensed'>
      <tr>
        <td colspan='4'><span class='bg-danger'>$callback</span><br />
        Online support: <a href='http://www.tethrnet.com/'>Tethrnet</a></td>
      <tr>";

if (dbFetchCell("SELECT `value` FROM `callback` WHERE `name` = 'uuid'") != '' && $callback_status != 2) {
    echo "
      <tr>
        <td colspan='4'><button class='btn btn-danger btn-xs' type='submit' name='clear-stats' id='clear-stats'>Clear remote stats</button></td>
      </tr>
    ";
}

echo "
        <td><img src='images/icons/device.png' class='optionicon'> <b>Devices</b></td><td align=right>$stat_devices</td>
        <td><img src='images/icons/port.png' class='optionicon'> <b>Ports</b></td><td align=right>$stat_ports</td>
      </tr>
      <tr>
        <td><img src='images/icons/ipv4.png'  class='optionicon'> <b>IPv4 Addresses<b></td><td align=right>$stat_ipv4_addy</td>
        <td><img src='images/icons/ipv4.png' class='optionicon'> <b>IPv4 Networks</b></td><td align=right>$stat_ipv4_nets</td>
      </tr>
      <tr>
        <td><img src='images/icons/ipv6.png'  class='optionicon'> <b>IPv6 Addresses<b></td><td align=right>$stat_ipv6_addy</td>
        <td><img src='images/icons/ipv6.png' class='optionicon'> <b>IPv6 Networks</b></td><td align=right>$stat_ipv6_nets</td>
       </tr>
     <tr>
        <td><img src='images/icons/services.png'  class='optionicon'> <b>Services<b></td><td align=right>$stat_services</td>
        <td><img src='images/icons/apps.png' class='optionicon'> <b>Applications</b></td><td align=right>$stat_apps</td>
      </tr>
      <tr>
        <td ><img src='images/icons/processor.png' class='optionicon'> <b>Processors</b></td><td align=right>$stat_processors</td>
        <td><img src='images/icons/memory.png' class='optionicon'> <b>Memory</b></td><td align=right>$stat_memory</td>
      </tr>
      <tr>
        <td><img src='images/icons/storage.png' class='optionicon'> <b>Storage</b></td><td align=right>$stat_storage</td>
        <td><img src='images/icons/diskio.png' class='optionicon'> <b>Disk I/O</b></td><td align=right>$stat_diskio</td>
      </tr>
      <tr>
        <td><img src='images/icons/inventory.png' class='optionicon'> <b>HR-MIB</b></td><td align=right>$stat_hrdev</td>
        <td><img src='images/icons/inventory.png' class='optionicon'> <b>Entity-MIB</b></td><td align=right>$stat_entphys</td>
      </tr>
      <tr>
        <td ><img src='images/icons/syslog.png' class='optionicon'> <b>Syslog Entries</b></td><td align=right>$stat_syslog</td>
        <td><img src='images/icons/eventlog.png' class='optionicon'> <b>Eventlog Entries</b></td><td align=right>$stat_events</td>
      </tr>
      <tr>
        <td ><img src='images/icons/sensors.png' class='optionicon'> <b>Sensors</b></td><td align=right>$stat_sensors</td>
        <td><img src='images/icons/toner.png' class='optionicon'> <b>Toner</b></td><td align=right>$stat_toner</td>
      </tr>
    </table>
</div>
";
?>
  </div>

  <div style="float: left; padding: 0px; width: 49%">

    <h3>Tethrnet NMS(TNMS) is an autodiscovering PHP/MySQL-based network monitoring system.</h3>
<?php
$versions = version_info();
$project_name    = $config['project_name'];
$project_version = $config['version'];
$apache_version  = str_replace('Apache/', '', $_SERVER['SERVER_SOFTWARE']);
$php_version     = $versions['php_ver'];
$mysql_version   = $versions['mysql_ver'];
$netsnmp_version = $versions['netsnmp_ver'];
$rrdtool_version = $versions['rrdtool_ver'];
$schema_version  = $versions['db_schema'];
$version         = `git rev-parse --short HEAD`;


echo "
<div class='table-responsive'>
    <table class='table table-condensed' border='0'>
      <tr><td><b>Version</b></td><td>$version</td></tr>
      <tr><td><b>DB Schema</b></td><td>#$schema_version</td></tr>
      <tr><td><b>Apache</b></td><td>$apache_version</td></tr>
      <tr><td><b>PHP</b></td><td>$php_version</td></tr>
      <tr><td><b>MySQL</b></td><td>$mysql_version</td></tr>
      <tr><td><b>RRDtool</b></td><td>$rrdtool_version</td></tr>
    </table>
</div>
";


?>
  <div style="margin-top:10px;">
  </div>

</div>

<script>
    $("[name='statistics']").bootstrapSwitch('offColor','danger','size','mini');
    $('input[name="statistics"]').on('switchChange.bootstrapSwitch',  function(event, state) {
        event.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { type: "callback-statistics", state: state},
            dataType: "html",
            success: function(data){
             },
             error:function(){
                 return $("#switch-state").bootstrapSwitch("toggle");
             }
        });
    });
    $('#clear-stats').click(function(event) {
        event.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { type: "callback-clear"},
            dataType: "html",
            success: function(data){
                location.reload(true);
             },
             error:function(){
             }
        });
    });
</script>
