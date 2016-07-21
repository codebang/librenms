import os
import subprocess
import logging
import logging.handlers
import sys
import json
import time
import datetime
import MySQLdb

from kafka import KafkaClient,SimpleProducer

ob_install_dir = os.path.dirname(os.path.dirname(os.path.realpath(__file__)))
config_file = ob_install_dir + '/config.php'




def get_config_data():
    try:
        with open(config_file) as f:
            pass
    except IOError as e:
        print "ERROR: Oh dear... %s does not seem readable" % config_file
        sys.exit(-1)

    config_cmd = [ 'php', '%s/config_to_json.php' % ob_install_dir]
    try:
        proc = subprocess.Popen(config_cmd, stdout=subprocess.PIPE, stdin=subprocess.PIPE)
    except Exception,e:
        print e.message
        print "ERROR: Could not execute: %s" % config_cmd

    try:
        return json.loads(proc.communicate()[0])
    except Exception,e:
        print e.message



config = get_config_data()



logger = logging.getLogger('tnms')
logger.setLevel(logging.INFO)

fh = logging.FileHandler(config["log_file"])
fh.setLevel(logging.DEBUG)
formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
fh.setFormatter(formatter)
logger.addHandler(fh)

if config['sender']['syslog_enable']:
    if config['sender']['syslog']['address'].startswith(os.path.sep):
       syslog_handler = logging.handlers.SysLogHandler(address=config['sender']['syslog']['address'])
    else:
       syslog_handler = logging.handlers.SysLogHandler(address=(config['sender']['syslog']['address'],514))
    logger.addHandler(syslog_handler)



def sendalarmtokakfa(host,item,message):
    msg={}
    msg["@timestamp"] = int(round(time.time() * 1000))
    msg["accountId"] = 'dms_server'
    msg["host"] =  host
    msg["item"] = item
    msg["severity"] = 'Critical'
    msg["message"] = message
    transport_string = json.dumps(msg)
    broker_list = config['sender']['kafka']['kafka_brokers']
    alarm_topic = config['sender']['kafka']['kafka_alert_topic']
    print alarm_topic
    kafka_client = KafkaClient(broker_list)
    print broker_list
    producer = SimpleProducer(kafka_client)
    try:
      producer.send_messages(alarm_topic, b"%s" % transport_string)
    except Exception as err:
      print err.message
    finally:
      kafka_client.close()


def log_event(host,message,type,reference=None):
    db_host = config['db_host']
    user = config['db_user']
    pwd = config['db_pass']
    database = config['db_name']
    try:
        connect = MySQLdb.connect(db_host,user,pwd,database)
        db = connect.cursor()
        db.execute("""select device_id from devices where hostname='%s'""" % (host))
        row = db.fetchone()
        device_id = int(row[0])
        if reference:
           db.execute(""" select port_id from ports where device_id = '%s' and (ifName = '%s' or ifDescr = '%s')""" % (device_id,reference,reference))
           row = db.fetchone()
           reference = row[0]
        else:
           reference = 'NULL'
        db.execute("""insert into eventlog (host,datetime,message,type,reference) values(%s,'%s','%s','%s','%s') """ % (device_id,datetime.datetime.now(),message,type,reference))
        connect.commit()
    except Exception,e:
        print e.message
    finally:
        try:
            connect.close()
        finally:
            pass
