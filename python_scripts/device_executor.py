from optparse import OptionParser
from netmiko import ConnectHandler
from netmiko.ssh_exception import NetMikoTimeoutException
from util import config
from util import logger
from util import sendalarm

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
