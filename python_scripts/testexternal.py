from alarm import PortEnableAlarm




if __name__ == '__main__':
  context = {}
  status = []
  context['status'] = status
  status.append('test')
  context['vlan'] = '104'
  context['speed_limit'] = '512' 
  alarm = PortEnableAlarm('192.168.1.4','Ethrent1/0/4',context)
  alarm.send()
