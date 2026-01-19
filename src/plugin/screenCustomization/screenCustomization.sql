-- *** COPYRIGHT NOTICE *********************************************************
-- *
-- * Copyright 2016 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
-- *
-- ******************************************************************************
-- *** WARNING *** T H I S    F I L E    I S    N O T    O P E N    S O U R C E *
-- ******************************************************************************
-- *
-- * This file is an add-on to ProjeQtOr, packaged as a plug-in module.
-- * It is NOT distributed under an open source license. 
-- * It is distributed in a proprietary mode, only to the customer who bought
-- * corresponding licence. 
-- * The company ProjeQtOr remains owner of all add-ons it delivers.
-- * Any change to an add-ons without the explicit agreement of the company 
-- * ProjeQtOr is prohibited.
-- * The diffusion (or any kind if distribution) of an add-on is prohibited.
-- * Violators will be prosecuted.
-- *    
-- *** DO NOT REMOVE THIS NOTICE ************************************************/

-- ///////////////////////////////////////////////////////////////////////////////
-- // PROJECTOR PERSONALIZED TRANSLATION PLUGIN                                 //
-- // Allows definition of new field names and change all translated texts.     //
-- ///////////////////////////////////////////////////////////////////////////////

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(100004001, 'menuScreenCustomization', 143, 'item', 9104, NULL, 0, 'Admin ');
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 100004001, 1);             

CREATE TABLE `${prefix}plgcustomlist` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `scope` varchar(100) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE INDEX plgcustomlistScope ON `${prefix}plgcustomlist` (scope);