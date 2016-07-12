<?php

require 'includes/geshi/geshi.php';

// FIXME svn stuff still using optc etc, won't work, needs updating!
if (is_admin()) {
            echo '<br />
                <div class="row">
            ';
            $node_info['last']['status'] = 'OK';
            $node_info['name'] = $device['hostname'];
            $node_info['ip'] = $device['hostname'];
            $node_info['model'] = $device['os'];
            $node_info['last']['end'] = 'test';

            if (is_array($node_info)) {
                echo '
                      <div class="col-sm-4">
                          <div class="panel panel-primary">
                              <div class="panel-heading">Sync status: <strong>'.$node_info['last']['status'].'</strong></div>
                              <ul class="list-group">
                                  <li class="list-group-item"><strong>Node:</strong> '.$node_info['name'].'</strong></li>
                                  <li class="list-group-item"><strong>IP:</strong> '.$node_info['ip'].'</strong></li>
                                  <li class="list-group-item"><strong>Model:</strong> '.$node_info['model'].'</strong></li>
                                  <li class="list-group-item"><strong>Last Sync:</strong> '.$node_info['last']['end'].'</strong></li>
                              </ul>
                          </div>
                      </div>
                ';
            }
            echo '</div>';
        }
    $ret_arr = show_config($device['hostname'],$device['transport_username'],$device['transport_password'],$device['os']);
    $array = $ret_arr['desc'];
    $text =  implode("\r\n",$array);
    if (!empty($text)) {
        // if (isset($previous_config)) {
        //     $language = 'diff';
        // } else {
        $language = 'ios';
        // }
        $geshi = new GeSHi($text, $language);
        $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
        $geshi->set_overall_style('color: black;');
        // $geshi->set_line_style('color: #999999');
        echo '<div class="config">';
        echo '<input id="linenumbers" class="btn btn-primary" type="submit" value="Hide line numbers"/>';
        echo $geshi->parse_code();
        echo '</div>';
    }
//}//end if

$pagetitle[] = 'Config';
