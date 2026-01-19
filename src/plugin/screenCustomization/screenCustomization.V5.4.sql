-- *** COPYRIGHT NOTICE *********************************************************
-- *
-- * Copyright 2015-2020 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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

ALTER TABLE ${prefix}plgcustomlist MODIFY sortOrder int(3) unsigned COMMENT '3';
ALTER TABLE ${prefix}plgcustomlist MODIFY idle int(1) unsigned DEFAULT 0 COMMENT '1';