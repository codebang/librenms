from optparse import OptionParser
import sys
import requests
import redis
import json
import subprocess
import os
import logging



ob_install_dir = os.path.dirname(os.path.dirname(os.path.realpath(__file__)))
config_file = ob_install_dir + '/config.php'


def get_config_data():
    config_cmd = ['/usr/bin/env', 'php', '%s/config_to_json.php' % ob_install_dir]
    try:
        proc = subprocess.Popen(config_cmd, stdout=subprocess.PIPE, stdin=subprocess.PIPE)
    except Exception,e:
        print e.message
        print "ERROR: Could not execute: %s" % config_cmd
        sys.exit(2)
    return proc.communicate()[0]

try:
    with open(config_file) as f:
        pass
except IOError as e:
    print "ERROR: Oh dear... %s does not seem readable" % config_file
    sys.exit(2)

try:
    config = json.loads(get_config_data())
except Exception,e:
    print e.message
    print "ERROR: Could not load or parse configuration, are PATHs correct?"
    sys.exit(2)

logger = logging.getLogger('dms_manager')
logger.setLevel(logging.DEBUG)

fh = logging.FileHandler(config["log_file"])
fh.setLevel(logging.DEBUG)

formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
fh.setFormatter(formatter)
logger.addHandler(fh)



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

def updateswitch_dms(switch_ip,account_name,dms_location):
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


