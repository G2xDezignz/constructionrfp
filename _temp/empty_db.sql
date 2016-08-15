CREATE DATABASE `rfp` /*!40100 DEFAULT CHARACTER SET latin1 */;

CREATE TABLE `rfp`.`vt_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admLevel` tinyint(2) NOT NULL,
  `admType` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `admLevel_UNIQUE` (`admLevel`),
  UNIQUE KEY `admType_UNIQUE` (`admType`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Determines access level and permissions for application';
INSERT INTO `rfp`.`vt_admin` (`admLevel`, `admType`) VALUES (0, 'dummy');
INSERT INTO `rfp`.`vt_admin` (`admLevel`, `admType`) VALUES (1, 'Admin');
INSERT INTO `rfp`.`vt_admin` (`admLevel`, `admType`) VALUES (2, 'Manager');
INSERT INTO `rfp`.`vt_admin` (`admLevel`, `admType`) VALUES (3, 'Staff');
INSERT INTO `rfp`.`vt_admin` (`admLevel`, `admType`) VALUES (4, 'Field');

CREATE TABLE `rfp`.`vt_plantype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `planType` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `planType_UNIQUE` (`planType`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT INTO `rfp`.`vt_plantype` (`planType`) VALUES ('Architectual');
INSERT INTO `rfp`.`vt_plantype` (`planType`) VALUES ('Civil');
INSERT INTO `rfp`.`vt_plantype` (`planType`) VALUES ('Mechanical');
INSERT INTO `rfp`.`vt_plantype` (`planType`) VALUES ('Electrical');
INSERT INTO `rfp`.`vt_plantype` (`planType`) VALUES ('Landscape');
INSERT INTO `rfp`.`vt_plantype` (`planType`) VALUES ('Other');

CREATE TABLE `rfp`.`vt_proposaltype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proposalType` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `proposalType_UNIQUE` (`proposalType`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT INTO `rfp`.`vt_proposaltype` (`proposalType`) VALUES ('Concrete');
INSERT INTO `rfp`.`vt_proposaltype` (`proposalType`) VALUES ('Carpentry');
INSERT INTO `rfp`.`vt_proposaltype` (`proposalType`) VALUES ('Elevators');
INSERT INTO `rfp`.`vt_proposaltype` (`proposalType`) VALUES ('HVAC');
INSERT INTO `rfp`.`vt_proposaltype` (`proposalType`) VALUES ('Landscaping');
INSERT INTO `rfp`.`vt_proposaltype` (`proposalType`) VALUES ('Plumbing');

CREATE TABLE `rfp`.`vt_state` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stateName` varchar(45) NOT NULL,
  `stateAbbr` varchar(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `stateName_UNIQUE` (`stateName`),
  UNIQUE KEY `stateAbbr_UNIQUE` (`stateAbbr`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT INTO `rfp`.`vt_state` (`stateName`, `stateAbbr`) VALUES ('Missouri', 'MO');
INSERT INTO `rfp`.`vt_state` (`stateName`, `stateAbbr`) VALUES ('Georgia', 'GA');
INSERT INTO `rfp`.`vt_state` (`stateName`, `stateAbbr`) VALUES ('New Mexico', 'NM');
INSERT INTO `rfp`.`vt_state` (`stateName`, `stateAbbr`) VALUES ('Texas', 'TX');

CREATE TABLE `rfp`.`vt_status` (
  `idStatus` char(1) NOT NULL,
  `statusName` varchar(45) NOT NULL,
  PRIMARY KEY (`idStatus`),
  UNIQUE KEY `statusName_UNIQUE` (`statusName`),
  UNIQUE KEY `idStatus_UNIQUE` (`idStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT INTO `rfp`.`vt_status` (`idStatus`, `statusName`) VALUES ('A', 'Awarded');
INSERT INTO `rfp`.`vt_status` (`idStatus`, `statusName`) VALUES ('R', 'Rejected');
INSERT INTO `rfp`.`vt_status` (`idStatus`, `statusName`) VALUES ('T', 'Tentative');
INSERT INTO `rfp`.`vt_status` (`idStatus`, `statusName`) VALUES ('U', 'Unprocessed');

CREATE TABLE `rfp`.`projects` (
  `id` int(11) NOT NULL,
  `projName` varchar(100) NOT NULL,
  `projAddress` varchar(100) DEFAULT NULL,
  `projState` varchar(45) NOT NULL,
  `projCity` varchar(45) NOT NULL,
  `projZip` varchar(15) DEFAULT NULL,
  `projSummary` text NOT NULL,
  `projDetail` blob NOT NULL,
  `rfpInfo` text,
  `closeDate` datetime NOT NULL,
  `projStatus` char(1) NOT NULL DEFAULT 'S',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `pState` (`projState`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `rfp`.`planspecs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectID` int(11) NOT NULL,
  `sheet` varchar(45) NOT NULL,
  `title` varchar(45) NOT NULL,
  `planTypeID` int(11) NOT NULL,
  `revisionDate` date NOT NULL,
  `datetimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `filename` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `projectID` (`projectID`),
  KEY `planTypeID` (`planTypeID`),
  CONSTRAINT `planTypeID` FOREIGN KEY (`planTypeID`) REFERENCES `rfp`.`vt_plantype` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `projectID` FOREIGN KEY (`projectID`) REFERENCES `rfp`.`projects` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `rfp`.`demographics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(45) NOT NULL,
  `LastName` varchar(45) NOT NULL,
  `Title` varchar(100) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `Address` varchar(100) NOT NULL,
  `City` varchar(75) NOT NULL,
  `State` varchar(45) NOT NULL,
  `Zip` varchar(15) NOT NULL,
  `Company` varchar(100) DEFAULT NULL,
  `Trade` varchar(255) DEFAULT NULL,
  `Phone` varchar(15) NOT NULL,
  `Mobile` varchar(15) DEFAULT NULL,
  `Fax` varchar(15) DEFAULT NULL,
  `sect3` tinyint(1) DEFAULT '0',
  `mbe` tinyint(1) DEFAULT '0',
  `wbe` tinyint(1) DEFAULT '0',
  `other` varchar(150) DEFAULT NULL,
  `datetimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `username` varchar(45) NOT NULL,
  `password` varchar(255) NOT NULL,
  `passhint` varchar(45) NOT NULL,  
  `delete` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  KEY `projID` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `rfp`.`bids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectID` int(11) NOT NULL,
  `subID` int(11) NOT NULL,
  `appType` int(11) NOT NULL,
  `rfpFile` varchar(255) NOT NULL,
  `submitDate` datetime NOT NULL,
  `status` char(1) NOT NULL DEFAULT 'U',
  `datetimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `sid` (`subID`),
  KEY `aid` (`appType`),
  KEY `pid` (`projectID`),
  CONSTRAINT `aid` FOREIGN KEY (`appType`) REFERENCES `rfp`.`vt_proposaltype` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `pid` FOREIGN KEY (`projectID`) REFERENCES `rfp`.`projects` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `sid` FOREIGN KEY (`subID`) REFERENCES `rfp`.`demographics` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `rfp`.`users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(25) NOT NULL,
  `lastName` varchar(25) NOT NULL,
  `username` varchar(25) NOT NULL,
  `password` varchar(255) NOT NULL,
  `admLevel` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  KEY `admLvlType` (`admLevel`),
  CONSTRAINT `admLvlType` FOREIGN KEY (`admLevel`) REFERENCES `rfp`.`vt_admin` (`admLevel`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE USER 'webuser'@'localhost' IDENTIFIED BY 'webUser01';
GRANT SELECT ON `rfp`.`projects` TO 'webuser'@'localhost';
GRANT SELECT ON `rfp`.`demographics` TO 'webuser'@'localhost';

CREATE USER 'rfpadmin'@'localhost' IDENTIFIED BY 'P0rtalAdm1n';
GRANT DELETE, INSERT, SELECT, UPDATE ON `rfp`.* TO 'rfpadmin'@'localhost';
INSERT INTO `rfp`.`users` (`firstName`,`lastName`,`username`,`password`,`admLevel`) VALUES ('Default','Admin','admin','$2y$07$Ah3fIeidUUnYxTmpUZwlOeACSNXKoaKR9n8tOyF1cG8Vdm.IYH00O',1);
