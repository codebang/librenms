from optparse import OptionParser
import sys
import requests
import redis
import json
from util import config
from util import logger
from util import sendalarm


dso_url = config['dso_url']
redis_host = config['redis_server']
redis_port = config['redis_port']


parser = OptionParser()
(options, args) = parser.parse_args()


def redis_context(func):
   def inner_func(*args,**kwargs):
       redis_server = redis.Redis(host=redis_host,port=redis_port)
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
      print 'fail to notify dso to create switch.'
      sys.exit(-1)

@redis_context
def updateswitch(switch_ip,account_name,dms_location,redis_server):
   logger.info('start to update switch(%s)' % switch_ip)

   headers = {'content-type': 'application/json'}
   switch = {}
   switch['managementIp'] = switch_ip
   account_id = redis_server.hget('accounts',account_name)
   if not account_id:
      print 'can not find corresponding accountid.'
      logger.error("can not find corresponding accountid for (%s)" % account_name)
      sys.exit(-1)
   switch['accountId'] = account_id
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
   requests.post('%s/switch' % dso_url,data=switch_json,headers=headers)


@redis_context
def getworkstationfromport(switchip,port_name,redis_server):
  key = "switch_" + switchip
  json_attrib = redis_server.hget(key,port_name) 
  if json_attrib:
     switch = json.loads(json_attrib)
     print switch['workstation']
  else:
     print "none"


if __name__ == '__main__':
  func_string = args.pop(0)
  func_proxy = locals().get(func_string,None) 
  if func_proxy:
     func_proxy(*args)
  else:
     print 'can not find the command handler.'
     sys.exit(-1)


