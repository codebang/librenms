from util import config,sendalarmtokakfa,logger,log_event


class Event(object):

  def send(self):
      """
          1 send to kafka
          2 send to syslog
          3 send to event log
          4 record to local log
          5 TBD
      """
      descr = self.render_descritpion()

      log_event(self.host,descr,None,None)

      if self.event_level == 'Critical':
          if config['sender']['kafka_enable']:
             sendalarmtokakfa(self.host,self.event_type,descr)
             logger.critical(descr)

      else:
          logger.info(descr)




class DeviceConnectError(Event):
    def __init__(self,switch_ip,context):
        self.host = switch_ip
        self.event_type = "Switch_Connect_Fail"
        self.event_level = "Critical"
        self.context = context

    def render_descritpion(self):
        return self.context['error']


class DeviceTypeNotSupport(Event):
    def __init__(self,switch_ip,context):
        self.host = switch_ip
        self.event_type = "Device_Type_Not_Support"
        self.event_level = "Critical"
        self.context = context

    def render_descritpion(self):
        return "Device type(%s) is not supported now" % self.context['type']


class PortEnableEvent(Event):
  def __init__(self,switch_ip,interface_name,context):
      self.host = switch_ip
      self.interface_name = interface_name
      self.context = context



  def render_descritpion(self):
      desc_array = []
      header_line = "switch:%s      port:%s      alarm:PortEnableFail" % (self.host,self.interface_name)
      desc_array.append(header_line)
      row_line_1 = "Port Enable Flow: config vlan(%s) -> speed_limit(%s) -> undo shutdown" % (self.context['vlan'],self.context['speed_limit'])
      desc_array.append(row_line_1)
      return '\n'.join(desc_array)


class PortEnableStart(PortEnableEvent):
  def __init__(self,switch_ip,interface_name,context):
      super(PortEnableStart,self).__init__(switch_ip,interface_name,context)
      self.event_type = "PortAutoEnable_Start"
      self.event_level = "Normal"
      self.context = context

  def render_description(self):
      desc_1 = PortEnableEvent.render_descritpion(self)
      desc_2 = "Start to enable port..."
      return '\n'.join([desc_1,desc_2])


class PortEnableEnd(PortEnableEvent):
  def __init__(self,switch_ip,interface_name,context):
      super(PortEnableEnd,self).__init__(switch_ip,interface_name,context)
      self.event_type = "PortAutoEnable_End"
      self.event_level = "Normal"
      self.context = context

  def render_description(self):
      desc_1 = PortEnableEvent.render_descritpion(self)
      desc_2 = "Success to enable port."
      return '\n'.join([desc_1,desc_2])


class PortEnableAlarm(PortEnableEvent):
  def __init__(self,switch_ip,interface_name,context):
      super(PortEnableAlarm,self).__init__(switch_ip,interface_name,context)
      self.event_type = "PortAutoEnable_Fail"
      self.event_level = "Critical"
      # include:
      # what we want to do: config vlan -> speed_limit -> undo shutdown


  def render_descritpion(self):
      desc_1 = PortEnableEvent.render_descritpion(self)
      desc_array = [desc_1]
      desc_array.extend(self.context['status'])
      print desc_array
      return '\n'.join(desc_array)

  
