ALTER TABLE #__sermon_speakers ADD COLUMN `alias` VARCHAR(255) NOT NULL;
ALTER TABLE #__sermon_series ADD COLUMN `alias` VARCHAR(255) NOT NULL;
ALTER TABLE #__sermon_speakers ADD COLUMN `metakey` TEXT NOT NULL, ADD `metadesc` TEXT NOT NULL;
ALTER TABLE #__sermon_series ADD COLUMN `metakey` TEXT NOT NULL, ADD `metadesc` TEXT NOT NULL;
ALTER TABLE #__sermon_sermons CHANGE `created_on` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE #__sermon_series CHANGE `created_on` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE #__sermon_speakers CHANGE `created_on` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE #__sermon_sermons CHANGE `published` `state` TINYINT(3) NOT NULL DEFAULT '0';
ALTER TABLE #__sermon_speakers CHANGE `published` `state` TINYINT(3) NOT NULL DEFAULT '0';
ALTER TABLE #__sermon_series CHANGE `published` `state` TINYINT(3) NOT NULL DEFAULT '0';
ALTER TABLE #__sermon_sermons CHANGE `sermon_number` `sermon_number` INT(10) NOT NULL DEFAULT '0';
# SS4.1
ALTER TABLE #__sermon_sermons CHANGE `sermon_path` `audiofile` TEXT NOT NULL DEFAULT '';
ALTER TABLE #__sermon_sermons ADD `videofile` TEXT NOT NULL DEFAULT '';
ALTER TABLE #__sermon_sermons ADD `picture` TEXT NOT NULL DEFAULT '';
ALTER TABLE #__sermon_sermons CHANGE `sermon_date` `sermon_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
