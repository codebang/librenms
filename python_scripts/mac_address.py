import redis
import json
import time
import os
import re

from netmiko import ConnectHandler
from optparse import OptionParser

from util import config

parser = OptionParser()
parser.add_option('-d','--device',dest='manageip',help='switch manage ip')
parser.add_option('-t','--mactable',dest='mactable',help='mactable')

(options, args) = parser.parse_args()

class MacEntry(object):

    def __init__(self,mac_addr,vlan_id,state,port_index,aging_time):
        self.mac_addr = self.convert(mac_addr)
        self.vlan_id = vlan_id
        self.state = state
        self.port_index = port_index
        self.aging_time = aging_time

    def convert(self,mac_addr):
      if mac_addr is not None:
         mac_array = mac_addr.split('-')
      num = []
      for mac in mac_array:
         num.append(mac[0:2])
         num.append(mac[2:4])
      return ':'.join(num)

    @classmethod
    def fromData(cls,dataline):
        return MacEntry(dataline[0],dataline[1],dataline[2],dataline[3],dataline[4])


pattern = re.compile(r'---\s+(\d+)(.*)')

if __name__ == '__main__':
    if not options.manageip:
      parser.error('switch manage ip is required.')

    if not options.mactable:
      parser.error('switch mactable is required')

    data_lines = options.mactable.splitlines()
    entries = []
    if len(data_lines) < 2:
       pass
    for data_line in data_lines[1:]:
       data_line =  data_line.strip()
       if len(data_line) == 0:
         continue
       elif pattern.match(data_line):
          matchObj = pattern.match(data_line)
          number = int(matchObj.group(1))
          print number
          print len(entries)
       else:
          data_line = filter(None,data_line.split(' '))
          entries.append(MacEntry.fromData(data_line))

    redis_server = config["redis_server"]
    redis_port = config["redis_port"]
    redis_host = redis.Redis(host=redis_server,port=redis_port)
    for entry in entries:
          map = {}
          map['vlan_id'] = entry.vlan_id
          map['state'] = entry.state
          map['port_index'] = entry.port_index
          map['aging_time'] = entry.aging_time
          map['@timestamp'] = int(round(time.time()))
          map['device'] = options.manageip
          redis_host.hset('mactable',entry.mac_addr,json.dumps(map)) 

