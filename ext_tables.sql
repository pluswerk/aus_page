#
# Table structure for table 'tx_auspage_domain_model_pagecategory'
#
CREATE TABLE tx_auspage_domain_model_pagecategory (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,

  title varchar(255) DEFAULT '' NOT NULL,
  dok_type varchar(255) DEFAULT '' NOT NULL,

  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,

  sys_language_uid int(11) DEFAULT '0' NOT NULL,
  l10n_parent int(11) DEFAULT '0' NOT NULL,
  l10n_diffsource mediumblob,

  PRIMARY KEY (uid),
  KEY parent (pid),
  KEY language (l10n_parent,sys_language_uid)
);

#
# Table structure for table 'tx_auspage_page_pagecategory_mm'
#
CREATE TABLE tx_auspage_page_pagecategory_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'pages'
#
CREATE TABLE pages (
  page_categories int(11) unsigned DEFAULT '0' NOT NULL,
);
