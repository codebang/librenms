from optparse import OptionParser
from netmiko import ConnectHandler
from netmiko.ssh_exception import NetMikoTimeoutException
import sys
import subprocess
import os
import json
import logging

mapping = {
    'comware':'huawei'
}


error_desc = {
    'DEVICE_NOT_SUPPORT':'this device is not supported now'
}


parser = OptionParser()
parser.add_option('-m','--model',dest='deviceModel',help='device model')
parser.add_option('-d','--device',dest='managementIp',help='device manangeip')
parser.add_option('-u','--user',dest='userName',help='user name')
parser.add_option('-a','--pass',dest='password',help='password')
parser.add_option('-p','--port',dest='port',help='ssh port',default=22)

(options, args) = parser.parse_args()




ob_install_dir = os.path.dirname(os.path.realpath(__file__))
config_file = ob_install_dir + '/config.php'


def get_config_data():
    config_cmd = ['/usr/bin/env', 'php', '%s/config_to_json.php' % ob_install_dir]
    try:
        proc = subprocess.Popen(config_cmd, stdout=subprocess.PIPE, stdin=subprocess.PIPE)
    except Exception,e:
        print e.message
        print "ERROR: Could not execute: %s" % config_cmd
        sys.exit(2)
    return proc.communicate()[0]

try:
    with open(config_file) as f:
        pass
except IOError as e:
    print "ERROR: Oh dear... %s does not seem readable" % config_file
    sys.exit(2)

try:
    config = json.loads(get_config_data())
except Exception,e:
    print e.message
    print "ERROR: Could not load or parse configuration, are PATHs correct?"
    sys.exit(2)

logger = logging.getLogger('device_executor')
logger.setLevel(logging.DEBUG)

fh = logging.FileHandler(config["log_file"])  
fh.setLevel(logging.DEBUG)  

formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')  
fh.setFormatter(formatter)  
logger.addHandler(fh)  


if __name__ == '__main__':
    if not options.deviceModel:
      parser.error('device model is required.')

    if not options.managementIp:
      parser.error('device model is required.')

    if not options.userName:
      parser.error('device model is required.')

    if not options.password:
      parser.error('device model is required.')

    if not args:
        parser.error('no command need to executed')
    
    device_model = mapping.get(options.deviceModel,None)

    logger.info('start to device executor, device_model(%s)' % device_model)
    if  device_model:
        device_meta = {
            'device_type':device_model,
            'ip': options.managementIp,
            'username': options.userName,
            'password': options.password,
            'port': options.port,
            'secret': 'secret',
            'verbose': False,
        }
        cmds = args[0].split(";")
        try:
          netconnect = ConnectHandler(**device_meta)
          for cmd in cmds:
            if cmd == '\\n':
              output = netconnect.send_command("\n")
            else:
              output  = netconnect.send_command(cmd)
            print output
          netconnect.disconnect()
        except NetMikoTimeoutException,e:
          logger.error(e.message)
          print 'ssh to deivce timeout.'
          sys.exit(-1)
    else:
        print error_desc['DEVICE_NOT_SUPPORT']
