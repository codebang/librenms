import redis
import json
import time
import os
import re

from netmiko import ConnectHandler

from optparse import OptionParser

parser = OptionParser()
parser.add_option('-d','--device',dest='manageip',help='switch manage ip')
parser.add_option('-t','--mactable',dest='mactable',help='mactable')
parser.add_option('-s','--redishost',dest='redis_host',help='redis-server')
parser.add_option('-p','--redisport',dest='redis_port',help='redis-server')

(options, args) = parser.parse_args()

class MacEntry(object):

    def __init__(self,mac_addr,vlan_id,state,port_index,aging_time):
        self.mac_addr = mac_addr
        self.vlan_id = vlan_id
        self.state = state
        self.port_index = port_index
        self.aging_time = aging_time

    @classmethod
    def fromData(cls,dataline):
        return MacEntry(dataline[0],dataline[1],dataline[2],dataline[3],dataline[4])


pattern = re.compile(r'---\s+(\d+)(.*)')

if __name__ == '__main__':
    if not options.manageip:
      parser.error('switch manage ip is required.')

    if not options.mactable:
      parser.error('switch mactable is required')

    if not options.redis_host:
      parser.error('redis host ip is required')
 
    if not options.redis_port:
      parser.error('redis port is required')
 
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

    redis_host = redis.Redis(host=options.redis_host,port=options.redis_port)
    for entry in entries:
          map = {}
          map['vlan_id'] = entry.vlan_id
          map['state'] = entry.state
          map['port_index'] = entry.port_index
          map['aging_time'] = entry.aging_time
          map['@timestamp'] = int(round(time.time()))
          map['device'] = options.manageip
          redis_host.hset('mactable',entry.mac_addr,json.dumps(map)) 

