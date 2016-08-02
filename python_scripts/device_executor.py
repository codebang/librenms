from optparse import OptionParser
import json
import re
import sys

from netmiko import ConnectHandler
from paramiko.ssh_exception import SSHException
from netmiko.ssh_exception import NetMikoTimeoutException
from util import logger



from alarm import PortEnableStart,PortEnableEnd,PortEnableAlarm,DeviceConnectError,DeviceTypeNotSupport

mapping = {
    'comware':'huawei'
}






parser = OptionParser()
parser.add_option('-m','--model',dest='deviceModel',help='device model')
parser.add_option('-d','--device',dest='managementIp',help='device manangeip')
parser.add_option('-u','--user',dest='userName',help='user name')
parser.add_option('-a','--pass',dest='password',help='password')
parser.add_option('-p','--port',dest='port',help='ssh port',default=22)

(options, args) = parser.parse_args()


def display_macaddress(device_connect):
  result_array = device_connect.send_command('display mac-address')
  print result_array
  




def ports_enable(ports_json,device_connect):
   ports = json.loads(ports_json)
   print ports
   for port in ports:
      speed_limit = int(port['speed_limit'])
      speed_limit = speed_limit - speed_limit % 64
      port['speed_limit'] = str(speed_limit)

   device_connect.send_command('system-view')
   for port in ports:
      start_event = PortEnableStart(device_connect.host,port['port'],port)
      start_event.send()
      try:
          port_enable(port,device_connect)
          status_desc = port_enable_confirm(port,device_connect)
          if len(status_desc) > 0:
             port['status'] = status_desc
             alarm_event = PortEnableAlarm(device_connect.host,port['port'],port)
             alarm_event.send()
          else:
            end_event = PortEnableEnd(device_connect.host,port['port'],port)
            end_event.send()
      except NetMikoTimeoutException,e:
            context = {}
            context['status'] = e.message
            error = DeviceConnectError(options.managementIp,context)
            error.send()
            sys.exit(-1)
      except SSHException,ssh_excpetion:
            context = {}
            context['status'] = ssh_excpetion.message
            error = DeviceConnectError(options.managementIp,context)
            error.send()
            sys.exit(-1)

   device_connect.send_command('save')
   device_connect.send_command('Y')
   device_connect.send_command('\n')


def port_enable(port,device_connect):
    port_name = port['port']
    m = re.search('\d',port_name)
    start_index = m.start()
    port_type = port_name[:start_index]
    port_number = port_name[start_index:]
    vlan = port['vlan']
    speed_limit = port['speed_limit']
    device_connect.send_command('vlan %s' % vlan)
    device_connect.send_command('quit')
    device_connect.send_command('interface %s %s' % (port_type,port_number))
    device_connect.send_command('port access vlan %s' % vlan)
    device_connect.send_command('line-rate outbound %s' % speed_limit)
    device_connect.send_command('undo shutdown')


def port_enable_confirm(port,device_connect):
    port_name = port['port']
    vlan = port['vlan']
    speed_limit = port['speed_limit']
    m = re.search('\d',port_name)
    status = []
    if not m:
      status.append('port name(%s) format is wrong' % port_name)
      return False

    start_index = m.start()
    port_type = port_name[:start_index]
    port_number = port_name[start_index:]
    result_array = device_connect.send_command('display brief interface %s %s' % (port_type,port_number))
    state_check = True
    '''
       Interface   Link     Speed  Duplex Type   PVID Description
      ---------------------------------------------------------------------------
       Eth1/0/4    ADM DOWN A      A      access 1
    '''
    if result_array.find('ADM DOWN') != -1:
       state_check = False
       status.append('port status is "administratively down", "undo shutdown" execute failed')

    vlan_check = False
    if result_array.find(vlan) != -1:
       vlan_check = True
    else:
       status.append('pvid is not (%s)' % (vlan))
   
    speed_check = False
    #Ethernet1/0/1: line-rate
    # Outbound: 128 Kbps
    outputs = device_connect.send_command('display qos-interface %s %s line-rate' % (port_type,port_number))
    line_find = False
    for output in outputs:
       output = output.strip()
       if output.find(':'):
           kvs = output.split(':')
           if kvs[0].strip() == 'Outbound':
              speed = kvs[1].strip()
              speeds = speed.split(' ')
              if speeds[0] == speed_limit:
                  speed_check = True
                  line_find = True
              else:
                  status.append("speed limit is (%s) not expect (%s)" % (speeds[0],speed_limit))
    if line_find and not speed_check:
       status.append("speed limit output cannot resolved: %s" % (outputs))
    return status
    



def port_disable(port,device_connect):
    port_name = port['port']
    vlan = port['vlan']
    speed_limit = port['speed_limit']
    device_connect.send_command('interface %s' % port_name)
    device_connect.send_command('undo port access vlan')
    device_connect.send_command('undo line-rate outbound')
    device_connect.send_command('shutdown')
    device_connect.send_command('quit')



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
            'device_type':device_model,
            'ip': options.managementIp,
            'username': options.userName,
            'password': options.password,
            'port': options.port,
            'secret': 'secret',
            'verbose': False,
        }
        try:
          netconnect = ConnectHandler(**device_meta)
          func_string = args.pop(0)
          func_proxy = locals().get(func_string,None)
          if func_proxy:
             args.append(netconnect);
             func_proxy(*args)
          else:
             print 'can not find the command handler.'
             sys.exit(-1)
          netconnect.disconnect()
        except NetMikoTimeoutException,e:
            context = {}
            context['status'] = e.message
            error = DeviceConnectError(options.managementIp,context)
            error.send()
            sys.exit(-1)
        except SSHException,ssh_excpetion:
            context = {}
            context['status'] = ssh_excpetion.message
            error = DeviceConnectError(options.managementIp,context)
            error.send()
            sys.exit(-1)
        except TypeError,err:
            print 'type error'
            sys.exit(-1)
    else:
        context = {}
        context['type'] = device_model
        device_err = DeviceTypeNotSupport(options.managementIp,context)
        device_err.send()
