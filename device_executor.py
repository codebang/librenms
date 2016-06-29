from optparse import OptionParser
from netmiko import ConnectHandler



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
    if  device_model:
        device_meta = {
            'device_type':options.deviceModel,
            'ip': options.managementIp,
            'username': options.userName,
            'password': options.password,
            'port': options.port,
            'secret': 'secret',
            'verbose': False,
        }

        netconnect = ConnectHandler(**device_meta)
        output  = netconnect.send_command(args[0])
        netconnect.disconnect()
        print output
    else:
        print error_desc['DEVICE_NOT_SUPPORT']
