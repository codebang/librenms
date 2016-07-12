import os
import subprocess
import logging
import sys
import json


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

config = {}

try:
    config = json.loads(get_config_data())
except Exception,e:
    print e.message
    print "ERROR: Could not load or parse configuration, are PATHs correct?"
    sys.exit(2)


logger = logging.getLogger('python_executor')
logger.setLevel(logging.INFO)

fh = logging.FileHandler(config["log_file"])
fh.setLevel(logging.DEBUG)

formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
fh.setFormatter(formatter)
logger.addHandler(fh)
