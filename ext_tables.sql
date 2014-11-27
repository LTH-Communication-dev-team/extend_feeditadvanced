#
# Add field to table 'be_groups'
#
CREATE TABLE be_users (
    tx_feeditadvanced_usersettings varchar(255) DEFAULT '' NOT NULL
);

#
# Table structure for table 'tx_feeditadvanced_tmpcontent'
#
CREATE TABLE tx_feeditadvanced_tmpcontent (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    tmpcontent text NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);