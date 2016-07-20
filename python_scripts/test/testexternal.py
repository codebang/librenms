from .. alarm import PortEnableAlarm




if __name__ == '__main__':
  context = {}
  status = []
  context['status'] = status
  status.append(['test'])
  alarm = PortEnableAlarm('192.168.1.4','Ethrent1/0/4',status)
  alarm.send()
