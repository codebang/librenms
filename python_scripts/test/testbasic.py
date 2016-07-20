import unittest
import json
from mock import Mock

import util



config_mock = {}
config_mock['sender'] = {}
config_mock['sender']['kafka_enable'] = False

temp_mock = Mock()
temp_mock.return_value = config_mock
util.config = config_mock
util.get_config_data = temp_mock


send_to_kafka_mock = Mock()
util.sendalarmtokakfa = send_to_kafka_mock


logger_mock = Mock()
util.logger = logger_mock


log_event_mock = Mock()
util.log_event = log_event_mock

from device_executor import ports_enable


def side_effect(*args,**kwargs):
    if not (args[0].find('display brief interface') < 0):
        return     '''Interface   Link     Speed  Duplex Type   PVID Description
              ---------------------------------------------------------------------------
               Eth1/0/4    ADM DOWN A      A      access 1
            '''
    elif not (args[0].find('display qos-interface') < 0):
        return ["Ethernet1/0/1: line-rate","Outbound: 128 Kbps"]


class BasicUnitTest(unittest.TestCase):
    def setUp(self):
        self.device_connect = Mock()
        self.device_connect.send_command.side_effect = side_effect
        input = [{'port':'Ethernet1/0/4','vlan':'105','speed_limit':'512'}];
        self.input_para = json.dumps(input)
        log_event_mock.reset_mock()

    def tearDown(self):
        pass



    def testVlanFail(self):
        ports_enable(self.input_para,self.device_connect)
        self.assertEqual(logger_mock.info.call_count,1)


    def testShutdownFail(self):
        pass



    def testSpeedLimitFail(self):
        pass


    def testAllSuccess(self):
        pass


if __name__ == '__main__':
    unittest.main()


