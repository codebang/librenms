from optparse import OptionParser
import sys
import requests
import redis
from util import config
from util import logger

dso_url = config['dso_url']
redis_host = config['redis_server']
redis_port = config['redis_port']
redis_server = redis.Redis(host=redis_host,port=redis_port)


def listaccountnames():
  keys = redis_server.hkeys('accounts')
  for key in keys:
    print key

def listlocations():
   keys = redis_server.hvals('locations')
   for key in keys:
     print key
def getaccountidbyname(name):
   print name

def getlocationidbyname(name):
   print name

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

def updateswitch(switch_ip,account_name,dms_location):
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
   

if __name__ == '__main__':
  func_string = args.pop(0)
  func_proxy = locals().get(func_string,None) 
  if func_proxy:
     func_proxy(*args)
  else:
     print 'can not find the command handler.'
     sys.exit(-1)


