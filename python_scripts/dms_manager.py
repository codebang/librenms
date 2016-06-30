from optparse import OptionParser
import sys
import requests
import redis
import json

parser = OptionParser()
parser.add_option('-d','--dso',dest='dso_url',help='dso sa url endpoint')
parser.add_option('-r','--redis',dest='redis_host',help='redis host')
parser.add_option('-p','--port',dest='redis_port',help='redis_port',default=6379)


(options, args) = parser.parse_args()

if not options.dso_url:
  parser.error('missing dso_url, is required...')
if not options.redis_host:
  parser.error('missing redis_host, is required...')

dso_url = options.dso_url
redis_host = options.redis_host
redis_port = options.redis_port
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
   headers = {'content-type': 'application/json'}
   r = requests.post("%s/switch" % dso_url,data=switch_json,headers=headers)
   if r.status_code == 201:
      print 'notify dso to create switch successfully.'
   else:
      print 'fail to notify dso to create switch.'
      sys.exit(-1)
def updateswitch_dms(switch_ip,account_name,dms_location):
   headers = {'content-type': 'application/json'}
   switch = {}
   switch['managementIp'] = switch_ip
   account_id = redis_server.hget('accounts',account_name)
   if not account_id:
      print 'can not find corresponding accountid.'
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
     sys.exit(-1)
   switch['locationId'] = location_id
   switch_json = json.dumps(switch)
   print switch_json
   requests.post('%s/switch' % dso_url,data=switch_json,headers=headers)
   

if __name__ == '__main__':
  func_string = args.pop(0)
  func_proxy = locals().get(func_string,None) 
  if func_proxy:
     func_proxy(*args)
  else:
     print 'can not find the command handler.'
     sys.exit(-1)


