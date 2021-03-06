from optparse import OptionParser
import sys
import requests
import redis
import json
from util import config
from util import logger


dso_url = config['dso_url']
redis_host = config['redis_server']
redis_port = config['redis_port']


parser = OptionParser()
(options, args) = parser.parse_args()


def redis_context(func):
   def inner_func(*args,**kwargs):
       redis_server = redis.Redis(host=redis_host,port=redis_port,socket_connect_timeout=2)
       kwargs['redis_server'] = redis_server
       func(*args,**kwargs)
   return inner_func

@redis_context
def listaccountnames(redis_server):
  keys = redis_server.hkeys('accounts')
  for key in keys:
    print key

 
@redis_context
def listlocations(redis_server):
   keys = redis_server.hvals('locations')
   for key in keys:
     print key


def createswitch(switch_json):
   logger.info('start to create switch:(%s)' % switch_json)
   headers = {'content-type': 'application/json'}
   r = requests.post("%s/switch" % dso_url,data=switch_json,headers=headers)
   logger.info(r.status_code)
   logger.info(r.content)
   if r.status_code == 201:
      print 'notify dso to create switch successfully.'
   else:
      print r.content
      print 'fail to notify dso to create switch.'
      sys.exit(-1)

def  addport(switch_json):
   logger.info('start to add port:(%s)' % switch_json)
   headers = {'content-type': 'application/json'}
   r = requests.post("%s/switch" % dso_url,data=switch_json,headers=headers)
   logger.info(r.status_code)
   logger.info(r.content)
   if r.status_code == 201:
      print 'notify dso to add ports successfully.'
   else:
      print r.content
      sys.exit(-1)

def  deleteport(switch_json):
   logger.info('start to delete port:(%s)' % switch_json)
   headers = {'content-type': 'application/json'}
   r = requests.delete("%s/switch" % dso_url,data=switch_json,headers=headers)
   logger.info(r.status_code)
   logger.info(r.content)
   if r.status_code == 200:
      print 'notify dso to delete ports successfully.'
   else:
      print r.content
      sys.exit(-1)

@redis_context
def updateswitch(switch_ip,dms_location,redis_server):
   logger.info('start to update switch(%s)' % switch_ip)

   headers = {'content-type': 'application/json'}
   switch = {}
   switch['managementIp'] = switch_ip
   keys = redis_server.hkeys('locations')
   location_id = None
   for key in keys:
      value = redis_server.hget('locations',key)
      if value == dms_location:
         location_id = key
   if not location_id:
     print 'can not find corresponding location id'
     logger.error("cannot find the location id")
     sys.exit(-1)
   switch['locationId'] = location_id
   switch_json = json.dumps(switch)
   logger.info('request:%s' % switch_json)
   r = requests.post('%s/switch' % dso_url,data=switch_json,headers=headers)
   if r.status_code == 201:
      print 'notify dso to bind location and switch successfully.'
   else:
      print 'fail to notify dso to bind location and switch:status_code(%s),content(%s)' % (r.status_code,r.content)


@redis_context
def getworkstationfordevice(switchip,redis_server):
  key = "switch_" + switchip
  port_names = redis_server.hkeys(key) 
  if port_names:
     port2ws = {}
     for port_name in port_names:
	value = redis_server.hget(key,port_name)
        port_info = json.loads(value)
        port2ws[port_name] = port_info['workstation']
     print json.dumps(port2ws)
  else:
     print ''

@redis_context
def getLocationId(location_name,redis_server):
   keys = redis_server.hkeys('locations')
   location_id = None
   for key in keys:
      value = redis_server.hget('locations',key)
      if value == location_name:
         location_id = key
   if not location_id:
     print 'can not find corresponding location id'
     logger.error("cannot find the location id")
     sys.exit(-1)
   else:
     print location_id

if __name__ == '__main__':
  func_string = args.pop(0)
  func_proxy = locals().get(func_string,None) 
  if func_proxy:
     func_proxy(*args)
  else:
     print 'can not find the command handler.'
     sys.exit(-1)


