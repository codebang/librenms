ALTER TABLE `devices` ADD `transport_type` varchar(10)  DEFAULT NULL;
ALTER TABLE `devices` ADD `transport_username` varchar(255) CHARACTER SET latin1  DEFAULT NULL;
ALTER TABLE `devices` ADD `transport_password` varchar(255) CHARACTER SET latin1 DEFAULT NULL;
ALTER TABLE `devices` ADD `transport_status` varchar(255) CHARACTER SET latin1 DEFAULT NULL;
ALTER TABLE `devices` ADD `last_trans_time` timestamp NULL DEFAULT NULL;
ALTER TABLE `devices` ADD `transport_port` smallint(5) unsigned NOT NULL DEFAULT '22';
ALTER TABLE `devices` ADD `transport_enablepassoword` varchar(255) CHARACTER SET latin1  DEFAULT NULL;

