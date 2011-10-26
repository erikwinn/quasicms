-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.0.51a-3


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema quasicmstest
--

-- CREATE DATABASE IF NOT EXISTS quasicmstest;
-- USE quasicmstest;

--
-- Definition of table `account`
--

DROP TABLE IF EXISTS `account`;
CREATE TABLE  `account` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `registration_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `username` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `notes` text,
  `last_login` timestamp NOT NULL default '0000-00-00 00:00:00',
  `login_count` int(10) unsigned default '1',
  `online` tinyint(1) NOT NULL default '0',
  `onetime_password` tinyint(1) NOT NULL default '0',
  `valid_password` tinyint(1) NOT NULL default '1',
  `type_id` tinyint(3) unsigned NOT NULL default '1',
  `status_id` tinyint(3) unsigned NOT NULL default '1',
  `person_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_account_username` (`username`),
  UNIQUE KEY `idx_account_person` (`person_id`),
  KEY `idx_account_type` (`type_id`),
  KEY `idx_account_status` (`status_id`),
  CONSTRAINT `account_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='Corresponds to the normal users | customers | members table';

--
-- Dumping data for table `account`
--

/*!40000 ALTER TABLE `account` DISABLE KEYS */;
LOCK TABLES `account` WRITE;
INSERT INTO `account` VALUES  (1,'2008-06-10 19:36:18','joeshmoe','5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8','','2008-11-30 14:31:10',169,0,0,1,1,1,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `account` ENABLE KEYS */;


--
-- Definition of table `account_status_type`
--

DROP TABLE IF EXISTS `account_status_type`;
CREATE TABLE  `account_status_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_account_status_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `account_status_type`
--

/*!40000 ALTER TABLE `account_status_type` DISABLE KEYS */;
LOCK TABLES `account_status_type` WRITE;
INSERT INTO `account_status_type` VALUES  (1,'Active'),
 (2,'Cancelled'),
 (3,'Suspended');
UNLOCK TABLES;
/*!40000 ALTER TABLE `account_status_type` ENABLE KEYS */;


--
-- Definition of table `account_type`
--

DROP TABLE IF EXISTS `account_type`;
CREATE TABLE  `account_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_account_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `account_type`
--

/*!40000 ALTER TABLE `account_type` DISABLE KEYS */;
LOCK TABLES `account_type` WRITE;
INSERT INTO `account_type` VALUES  (4,'Administrator'),
 (2,'Customer'),
 (3,'Employee'),
 (6,'Manufacturer'),
 (1,'Member'),
 (5,'Supplier');
UNLOCK TABLES;
/*!40000 ALTER TABLE `account_type` ENABLE KEYS */;


--
-- Definition of table `address`
--

DROP TABLE IF EXISTS `address`;
CREATE TABLE  `address` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(256) default 'My Address',
  `person_id` mediumint(8) unsigned NOT NULL,
  `street_1` varchar(256) default NULL,
  `street_2` varchar(256) default NULL,
  `suburb` varchar(256) default NULL,
  `city` varchar(256) default NULL,
  `county` varchar(256) default NULL,
  `zone_id` smallint(5) unsigned NOT NULL default '13',
  `country_id` smallint(5) unsigned NOT NULL default '223',
  `postal_code` varchar(32) default NULL,
  `is_current` tinyint(1) NOT NULL default '1',
  `type_id` tinyint(3) unsigned NOT NULL default '1',
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `last_modification_date` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `idx_address_person` (`id`),
  KEY `idx_address_type` (`type_id`),
  KEY `idx_address_zone` (`zone_id`),
  KEY `idx_address_country` (`country_id`),
  KEY `person_id` (`person_id`),
  CONSTRAINT `address_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `address`
--

/*!40000 ALTER TABLE `address` DISABLE KEYS */;
LOCK TABLES `address` WRITE;
INSERT INTO `address` VALUES  (1,'My primary address',1,'123 Any Street','Apt. 3','Bronx','New York','',43,223,'12345',1,1,'2008-05-31 18:20:52','0000-00-00 00:00:00'),
 (2,'Office',1,'431 Office Street','','','Commerce City','Laredo',13,223,'09889',1,3,'2008-07-14 09:09:36','0000-00-00 00:00:00'),
 (3,'',2,'234 Pine Street','Apt 1','','Boulder','Boulder',13,223,'80302',1,5,'2008-09-26 10:55:35','0000-00-00 00:00:00');
UNLOCK TABLES;
/*!40000 ALTER TABLE `address` ENABLE KEYS */;


--
-- Definition of table `address_type`
--

DROP TABLE IF EXISTS `address_type`;
CREATE TABLE  `address_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_address_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `address_type`
--

/*!40000 ALTER TABLE `address_type` DISABLE KEYS */;
LOCK TABLES `address_type` WRITE;
INSERT INTO `address_type` VALUES  (3,'Billing'),
 (4,'Company'),
 (5,'Friend'),
 (8,'Historical'),
 (6,'Manufacturer'),
 (1,'Primary'),
 (2,'Shipping'),
 (7,'Supplier');
UNLOCK TABLES;
/*!40000 ALTER TABLE `address_type` ENABLE KEYS */;


--
-- Definition of table `authorize_net_transaction`
--

DROP TABLE IF EXISTS `authorize_net_transaction`;
CREATE TABLE  `authorize_net_transaction` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `order_id` bigint(20) unsigned NOT NULL,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `response_code` varchar(8) default NULL,
  `response_subcode` varchar(8) default NULL,
  `response_reason_code` varchar(8) default NULL,
  `response_reason_text` text,
  `authorization_code` varchar(8) default NULL,
  `transaction_id` varchar(128) default NULL,
  `transaction_type` varchar(128) default NULL,
  `amount` decimal(12,2) default NULL,
  `avs_response_code` varchar(8) default NULL,
  `ccv_response_code` varchar(8) default NULL,
  `cav_response_code` varchar(8) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_authnet_transaction_transactionid` (`transaction_id`),
  KEY `idx_authnet_transaction_orderid` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `authorize_net_transaction`
--

/*!40000 ALTER TABLE `authorize_net_transaction` DISABLE KEYS */;
LOCK TABLES `authorize_net_transaction` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `authorize_net_transaction` ENABLE KEYS */;


--
-- Definition of table `block_location_type`
--

DROP TABLE IF EXISTS `block_location_type`;
CREATE TABLE  `block_location_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_block_location_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `block_location_type`
--

/*!40000 ALTER TABLE `block_location_type` DISABLE KEYS */;
LOCK TABLES `block_location_type` WRITE;
INSERT INTO `block_location_type` VALUES  (4,'CenterPanel'),
 (6,'ExtraPanel1'),
 (7,'ExtraPanel2'),
 (8,'ExtraPanel3'),
 (9,'ExtraPanel4'),
 (5,'LeftPanel'),
 (10,'PageBody'),
 (2,'PageFooter'),
 (1,'PageHeader'),
 (3,'RightPanel');
UNLOCK TABLES;
/*!40000 ALTER TABLE `block_location_type` ENABLE KEYS */;


--
-- Definition of table `content_block`
--

DROP TABLE IF EXISTS `content_block`;
CREATE TABLE  `content_block` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  `cssclass` varchar(128) default NULL,
  `title` varchar(128) default NULL,
  `description` varchar(256) default NULL,
  `show_title` tinyint(1) NOT NULL default '0',
  `show_description` tinyint(1) NOT NULL default '0',
  `collapsable` tinyint(1) NOT NULL default '0',
  `sort_order` tinyint(3) unsigned NOT NULL default '0',
  `parent_content_block_id` mediumint(8) unsigned default NULL,
  `location_id` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_content_block_name` (`name`),
  KEY `idx_content_block_parent` (`parent_content_block_id`),
  KEY `idx_content_block_location` (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `content_block`
--

/*!40000 ALTER TABLE `content_block` DISABLE KEYS */;
LOCK TABLES `content_block` WRITE;
INSERT INTO `content_block` VALUES  (1,'About Quasi','aboutUs','About QuasiCMS','A little about Quasi ..',1,0,1,2,NULL,4),
 (2,'HomeNews','homenews','Recent News','Latest QuasiCMS development news',1,1,0,3,NULL,4),
 (4,'TopLeft','','A Test Block','',1,0,0,0,NULL,3),
 (5,'LeftMenuBlock','','','',0,0,0,0,NULL,5),
 (7,'LoginBox','Login','Member Login','',0,0,0,0,NULL,1),
 (8,'TopMenuBlock','MainMenu','','',0,0,0,0,NULL,4),
 (9,'CreateAccountBlock','','','',0,0,0,1,NULL,4),
 (10,'MemberHomeMenuBlock','MemberHomeMenu','','',0,0,0,0,5,5),
 (12,'AccountManagerBlock','','','',0,0,0,1,NULL,4),
 (15,'ShoppingCartBox','CartBox','','',0,0,1,1,NULL,5),
 (16,'ProductDisplayBlock','ProductDisplay','','',0,0,0,1,NULL,4),
 (17,'ShoppingCartView','','','',0,0,0,1,NULL,4),
 (18,'CheckOutBlock','CheckOut','','',0,0,0,1,NULL,4),
 (19,'PayPalExpressReturn','OrderConfirmation','','',0,0,0,1,NULL,4),
 (20,'LostPasswordBlock','LostPassword','Password / Username Retrieval','Here you can reset your username or password',1,1,0,1,NULL,4),
 (21,'FooterInfo','','','',0,0,0,0,NULL,2);
INSERT INTO `content_block` VALUES  (22,'Help Section','','Quasi CMS Help','This is an example content block description ..',1,1,0,1,NULL,4);
UNLOCK TABLES;
/*!40000 ALTER TABLE `content_block` ENABLE KEYS */;


--
-- Definition of table `content_block_page_assn`
--

DROP TABLE IF EXISTS `content_block_page_assn`;
CREATE TABLE  `content_block_page_assn` (
  `content_block_id` mediumint(8) unsigned NOT NULL,
  `page_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`page_id`,`content_block_id`),
  KEY `idx_content_block_page_content_block` (`content_block_id`),
  KEY `idx_content_block_page_page` (`page_id`),
  CONSTRAINT `content_block_page_assn_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE,
  CONSTRAINT `content_block_page_assn_ibfk_2` FOREIGN KEY (`content_block_id`) REFERENCES `content_block` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `content_block_page_assn`
--

/*!40000 ALTER TABLE `content_block_page_assn` DISABLE KEYS */;
LOCK TABLES `content_block_page_assn` WRITE;
INSERT INTO `content_block_page_assn` VALUES  (1,1),
 (2,1),
 (4,1),
 (5,1),
 (5,2),
 (5,5),
 (5,6),
 (5,7),
 (5,9),
 (5,10),
 (7,1),
 (7,2),
 (7,5),
 (7,6),
 (7,7),
 (7,8),
 (7,9),
 (7,10),
 (8,1),
 (8,2),
 (8,4),
 (8,5),
 (8,6),
 (8,7),
 (8,8),
 (8,9),
 (8,10),
 (9,4),
 (10,8),
 (12,5),
 (15,1),
 (15,2),
 (15,5),
 (15,6),
 (15,7),
 (15,8),
 (16,8),
 (17,9),
 (18,10),
 (20,12),
 (21,1),
 (21,2),
 (21,4),
 (21,5),
 (21,6),
 (21,7),
 (21,8),
 (21,9),
 (21,10),
 (21,11),
 (21,12),
 (22,7);
UNLOCK TABLES;
/*!40000 ALTER TABLE `content_block_page_assn` ENABLE KEYS */;


--
-- Definition of table `content_category`
--

DROP TABLE IF EXISTS `content_category`;
CREATE TABLE  `content_category` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  `title` varchar(128) default NULL,
  `description` varchar(256) default NULL,
  `image_uri` varchar(256) default NULL,
  `parent_content_category_id` mediumint(8) unsigned default NULL,
  `public_permissions_id` tinyint(3) unsigned NOT NULL default '2',
  `user_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  `group_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_content_category_name` (`name`),
  KEY `idx_content_category_parent` (`parent_content_category_id`),
  KEY `idx_content_category_public_perms` (`public_permissions_id`),
  KEY `idx_content_category_user_perms` (`user_permissions_id`),
  KEY `idx_content_category_group_perms` (`group_permissions_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `content_category`
--

/*!40000 ALTER TABLE `content_category` DISABLE KEYS */;
LOCK TABLES `content_category` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `content_category` ENABLE KEYS */;


--
-- Definition of table `content_item`
--

DROP TABLE IF EXISTS `content_item`;
CREATE TABLE  `content_item` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(128) NOT NULL,
  `cssclass` varchar(128) default NULL,
  `title` varchar(128) default NULL,
  `description` varchar(256) default NULL,
  `text` text,
  `sort_order` mediumint(8) unsigned default '0',
  `show_title` tinyint(1) NOT NULL default '1',
  `show_description` tinyint(1) NOT NULL default '0',
  `show_creator` tinyint(1) NOT NULL default '1',
  `show_creation_date` tinyint(1) NOT NULL default '1',
  `show_last_modification` tinyint(1) NOT NULL default '1',
  `creator_id` mediumint(8) unsigned default '1',
  `copyright_notice` varchar(256) default NULL,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `last_modification` timestamp NOT NULL default '0000-00-00 00:00:00',
  `public_permissions_id` tinyint(3) unsigned NOT NULL default '2',
  `user_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  `group_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  `type_id` tinyint(3) unsigned NOT NULL default '1',
  `status_id` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_content_name` (`name`),
  KEY `idx_content_title` (`title`),
  KEY `idx_content_creator` (`creator_id`),
  KEY `idx_content_type` (`type_id`),
  KEY `idx_content_status` (`status_id`),
  KEY `idx_content_public_perms` (`public_permissions_id`),
  KEY `idx_content_user_perms` (`user_permissions_id`),
  KEY `idx_content_group_perms` (`group_permissions_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `content_item`
--

/*!40000 ALTER TABLE `content_item` DISABLE KEYS */;
LOCK TABLES `content_item` WRITE;
INSERT INTO `content_item` VALUES  (1,'About Quasi','aboutUsContent','Welcome to Quasi CMS!','An extensible AJAX based ecommerce CMS','<p>Quasi CMS is a system for building and maintaining websites based on the QCodo web development framework. It is designed to be simple to use and extend and very flexible.</p><p>As of this release (Version 0.2), Quasi is in a very early stage - this release is functional in some basic facilities and totally unfinished in others, it is provided mostly as a preview and to give those who are curious a look at the general architecture.</p>\n<p><strong>WARNING:</strong> At the risk of repeating myself, <i><u>this is a very early version!</u></i> I am not a security expert and both Quasi and QCodo have yet to be audited for security!! We have done what we can but there is <strong> No Guarantee - USE AT YOUR OWN RISK!!</strong>\n</p>\n<p><strong>NOTE:</strong> You can log in above as \"joeshmoe\" with the password \"password\" and use the account management tools. You can also visit the\n<a href=\"admin/\"> Administration interface </a> (currently \"The Dashboard\" ..)\n by replacing index.php with admin/ in your browser. Also, you can see <a href=\"doc/core/\">the documentation</a> for more details about Quasi under doc/ (like admin/)  to learn more about the API and architecture.</p>\n<p> You are free to modify content in the Admin UI, add Pages, Content Items, Menus etc .. The database will be cleared and set back to the default periodically so do not worry about breaking anything - in fact, please do and file a bug report!\n</p><p>\nHave Fun!\n</p>',0,1,1,0,0,0,1,'','2008-09-25 18:57:46','0000-00-00 00:00:00',2,2,2,2,1),
 (2,'Quasi gets demo site','','QuasiCMS now live','QuasiCMS demo site now available to the Public','<p>\nYay! Now you can see Quasi in action! \n</p><p>\nYes, this is still an early version but thanks to Riccardo (who has generously provided hosting) you can play around with a \"real\" example site. Experiment, have fun with it.\n</p><p>\nEnjoy!\n</p>\n',1,1,1,1,1,0,1,'','2008-09-26 14:53:22','0000-00-00 00:00:00',1,1,1,2,1);
INSERT INTO `content_item` VALUES  (3,'Sed ut perspiciatis unde omnis','','Sed ut perspiciatis','Sed ut perspiciatis unde omnisiste natus error','Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?',2,1,1,1,1,0,1,'','2008-09-27 03:22:08','0000-00-00 00:00:00',1,1,1,1,1),
 (4,'Pollform','poll','Rate the new site','a poll ...','How do you like the new site? \n<br />Good\n<br />Bad\n<br />Cowboy Neal\n',0,1,0,0,1,0,1,NULL,'2008-06-02 08:03:59','0000-00-00 00:00:00',1,1,1,1,1);
INSERT INTO `content_item` VALUES  (5,'FooterText','','','','<a href=\"http://sf.net/projects/quasicms\">Powered by QuasiCMS </a>\n&nbsp;&nbsp;&nbsp;\n | Boulder, Colorado | \n<a href=\"/index.php/Contact\">contact</a> | \n<a href=\"/index.php/Help\">help</a> |\n<a href=\"/index.php/Policies\">policies</a> | \n<a href=\"/index.php/Terms\">terms</a> &nbsp;&nbsp;&nbsp;&nbsp;\n',0,0,0,0,0,0,1,'','2008-09-17 09:57:21','0000-00-00 00:00:00',2,1,1,4,1),
 (6,'Introduction to QuasiCMS','','A Brief Introduction to QuasiCMS','This is an example of a content item description .. something brief about the item here','<p>This is a quick introduction - hopefully just enough to get you started. Ah yes - one day perhaps there will be \nextensive documentation, but as yet I have written this \nCMS almost entirely alone (a testament to QCodo\'s power!) and time is limited .. Feel free to help out! \n</p>\n<p>\n Until that wonderful day, experimentation is the key. And , of course, if you really want to know how QuasiCMS works: Read the source, Luke.\n</p>\n<div class=\"header\">How to add a Content Item</div>\n<p>\n<ul>\n<li>  In the admin UI, create a new content item - click the button at the bottom of the list and an area will appear below the list of items. \n</li><li>\nAdd a name. Title, and description, CSS class, etc. are optional.\n</li><li>\nAdd a sort order - this determines where in the content block it will appear, 0 is at the top.\n</li><li>\nAssign the new item to a Content Block in the list of Content blocks.\n</li><li>\nClick Save.\n</li><li>\nEt Voila! A new content item will appear in that content block. If you want to assign it to a new content block, see the next section to create a new block and then return and assign the item to the new block..\n</li></ul>\n\n</p>\n<div class=\"header\">How to add a Content Block</div>\n<p>\n<ul>\n</li><li>\n As with content items, create a new content block.\n</li><li>\n Again, give it a name - other things as desired.\n</li><li>\n Assign the block to a \"Location\" - these are the default areas of the page (ie. Header, LeftPanel, Center, etc.). Alternately you can assign the new block to a parent block and it will be rendered inside that one.\n</li></li>\nAssign the block to pages in the list.\n</li><li>\nClick Save.\n</li><li>\nEt Voila! A new content block will appear on those Pages. If you want to assign it to a new page, see the next section to create a new page.\n</li></ul>\n\n</p>\n<div class=\"header\">How to add a Page</div>\n<p>\n<ul>\n</li><li>\nAs with the others, create a new page\n</li><li>\nGive the Page a name - Important! This will be used as the URL thus: index.php/PageName.\n</li><li>\nOptionally assign some content blocks.\n</li><li>\nClick Save.\n</li><li>\nEt Voila! If you put the page name in your browser location bar you will see the new page. If you want a menu to get there, see the next section.\n</li></ul>\n\n</p>\n<div class=\"header\">How to add a Menu</div>\n<p>\n<ul>\n</li><li>\nAgain, create a new menu.\n</li><li>\nAdd the label - this text that will appear on the menu.\n</li><li>\nAdd a Page Name (eg. the page you created above) as the URL. Alternately, you can put a full URL, eg: http://othersite.net.\n</li><li>\nClick Save.\n</li><li>\nTry it out! It should send you to the new page\n</li></ul>\n\n</p>\n',1,1,1,1,1,1,1,'','2008-09-27 05:45:49','0000-00-00 00:00:00',1,1,1,1,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `content_item` ENABLE KEYS */;


--
-- Definition of table `content_item_content_block_assn`
--

DROP TABLE IF EXISTS `content_item_content_block_assn`;
CREATE TABLE  `content_item_content_block_assn` (
  `content_item_id` int(10) unsigned NOT NULL,
  `content_block_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`content_block_id`,`content_item_id`),
  KEY `idx_content_item_content_block_content_item` (`content_item_id`),
  KEY `idx_content_item_content_block_content_block` (`content_block_id`),
  CONSTRAINT `content_item_content_block_assn_ibfk_1` FOREIGN KEY (`content_block_id`) REFERENCES `content_block` (`id`) ON DELETE CASCADE,
  CONSTRAINT `content_item_content_block_assn_ibfk_2` FOREIGN KEY (`content_item_id`) REFERENCES `content_item` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `content_item_content_block_assn`
--

/*!40000 ALTER TABLE `content_item_content_block_assn` DISABLE KEYS */;
LOCK TABLES `content_item_content_block_assn` WRITE;
INSERT INTO `content_item_content_block_assn` VALUES  (1,1),
 (2,2),
 (3,2),
 (4,4),
 (5,21),
 (6,22);
UNLOCK TABLES;
/*!40000 ALTER TABLE `content_item_content_block_assn` ENABLE KEYS */;


--
-- Definition of table `content_item_content_category_assn`
--

DROP TABLE IF EXISTS `content_item_content_category_assn`;
CREATE TABLE  `content_item_content_category_assn` (
  `content_item_id` int(10) unsigned NOT NULL,
  `content_category_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`content_category_id`,`content_item_id`),
  KEY `idx_content_item_content_category_content_item` (`content_item_id`),
  KEY `idx_content_item_content_category_content_category` (`content_category_id`),
  CONSTRAINT `content_item_content_category_assn_ibfk_1` FOREIGN KEY (`content_category_id`) REFERENCES `content_category` (`id`) ON DELETE CASCADE,
  CONSTRAINT `content_item_content_category_assn_ibfk_2` FOREIGN KEY (`content_item_id`) REFERENCES `content_item` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `content_item_content_category_assn`
--

/*!40000 ALTER TABLE `content_item_content_category_assn` DISABLE KEYS */;
LOCK TABLES `content_item_content_category_assn` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `content_item_content_category_assn` ENABLE KEYS */;


--
-- Definition of table `content_item_usergroup_assn`
--

DROP TABLE IF EXISTS `content_item_usergroup_assn`;
CREATE TABLE  `content_item_usergroup_assn` (
  `content_item_id` int(10) unsigned NOT NULL,
  `usergroup_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`usergroup_id`,`content_item_id`),
  KEY `idx_content_item_usergroup_content_item` (`content_item_id`),
  KEY `idx_content_item_usergroup_usergroup` (`usergroup_id`),
  CONSTRAINT `content_item_usergroup_assn_ibfk_1` FOREIGN KEY (`usergroup_id`) REFERENCES `usergroup` (`id`) ON DELETE CASCADE,
  CONSTRAINT `content_item_usergroup_assn_ibfk_2` FOREIGN KEY (`content_item_id`) REFERENCES `content_item` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `content_item_usergroup_assn`
--

/*!40000 ALTER TABLE `content_item_usergroup_assn` DISABLE KEYS */;
LOCK TABLES `content_item_usergroup_assn` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `content_item_usergroup_assn` ENABLE KEYS */;


--
-- Definition of table `content_status_type`
--

DROP TABLE IF EXISTS `content_status_type`;
CREATE TABLE  `content_status_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_content_status_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `content_status_type`
--

/*!40000 ALTER TABLE `content_status_type` DISABLE KEYS */;
LOCK TABLES `content_status_type` WRITE;
INSERT INTO `content_status_type` VALUES  (3,'Draft'),
 (4,'Internal'),
 (1,'Published'),
 (2,'Unpublished');
UNLOCK TABLES;
/*!40000 ALTER TABLE `content_status_type` ENABLE KEYS */;


--
-- Definition of table `content_type`
--

DROP TABLE IF EXISTS `content_type`;
CREATE TABLE  `content_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_content_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `content_type`
--

/*!40000 ALTER TABLE `content_type` DISABLE KEYS */;
LOCK TABLES `content_type` WRITE;
INSERT INTO `content_type` VALUES  (2,'Article'),
 (6,'Audio'),
 (10,'BlogPost'),
 (7,'Comment'),
 (9,'Description'),
 (8,'ForumPost'),
 (4,'Image'),
 (1,'PageBody'),
 (3,'Product'),
 (5,'Video');
UNLOCK TABLES;
/*!40000 ALTER TABLE `content_type` ENABLE KEYS */;


--
-- Definition of table `country_type`
--

DROP TABLE IF EXISTS `country_type`;
CREATE TABLE  `country_type` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `iso_code_2` char(2) NOT NULL default '',
  `iso_code_3` char(3) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_country_name` (`name`),
  KEY `idx_iso_2` (`iso_code_2`),
  KEY `idx_iso_3` (`iso_code_3`)
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `country_type`
--

/*!40000 ALTER TABLE `country_type` DISABLE KEYS */;
LOCK TABLES `country_type` WRITE;
INSERT INTO `country_type` VALUES  (1,'Afghanistan','AF','AFG'),
 (2,'Albania','AL','ALB'),
 (3,'Algeria','DZ','DZA'),
 (4,'American Samoa','AS','ASM'),
 (5,'Andorra','AD','AND'),
 (6,'Angola','AO','AGO'),
 (7,'Anguilla','AI','AIA'),
 (8,'Antarctica','AQ','ATA'),
 (9,'Antigua and Barbuda','AG','ATG'),
 (10,'Argentina','AR','ARG'),
 (11,'Armenia','AM','ARM'),
 (12,'Aruba','AW','ABW'),
 (13,'Australia','AU','AUS'),
 (14,'Austria','AT','AUT'),
 (15,'Azerbaijan','AZ','AZE'),
 (16,'Bahamas','BS','BHS'),
 (17,'Bahrain','BH','BHR'),
 (18,'Bangladesh','BD','BGD'),
 (19,'Barbados','BB','BRB'),
 (20,'Belarus','BY','BLR'),
 (21,'Belgium','BE','BEL'),
 (22,'Belize','BZ','BLZ'),
 (23,'Benin','BJ','BEN'),
 (24,'Bermuda','BM','BMU'),
 (25,'Bhutan','BT','BTN'),
 (26,'Bolivia','BO','BOL'),
 (27,'Bosnia and Herzegowina','BA','BIH'),
 (28,'Botswana','BW','BWA'),
 (29,'Bouvet Island','BV','BVT'),
 (30,'Brazil','BR','BRA'),
 (31,'British Indian Ocean Territory','IO','IOT'),
 (32,'Brunei Darussalam','BN','BRN'),
 (33,'Bulgaria','BG','BGR');
INSERT INTO `country_type` VALUES  (34,'Burkina Faso','BF','BFA'),
 (35,'Burundi','BI','BDI'),
 (36,'Cambodia','KH','KHM'),
 (37,'Cameroon','CM','CMR'),
 (38,'Canada','CA','CAN'),
 (39,'Cape Verde','CV','CPV'),
 (40,'Cayman Islands','KY','CYM'),
 (41,'Central African Republic','CF','CAF'),
 (42,'Chad','TD','TCD'),
 (43,'Chile','CL','CHL'),
 (44,'China','CN','CHN'),
 (45,'Christmas Island','CX','CXR'),
 (46,'Cocos (Keeling) Islands','CC','CCK'),
 (47,'Colombia','CO','COL'),
 (48,'Comoros','KM','COM'),
 (49,'Congo','CG','COG'),
 (50,'Cook Islands','CK','COK'),
 (51,'Costa Rica','CR','CRI'),
 (52,'Cote D\'Ivoire','CI','CIV'),
 (53,'Croatia','HR','HRV'),
 (54,'Cuba','CU','CUB'),
 (55,'Cyprus','CY','CYP'),
 (56,'Czech Republic','CZ','CZE'),
 (57,'Denmark','DK','DNK'),
 (58,'Djibouti','DJ','DJI'),
 (59,'Dominica','DM','DMA'),
 (60,'Dominican Republic','DO','DOM'),
 (61,'East Timor','TP','TMP'),
 (62,'Ecuador','EC','ECU'),
 (63,'Egypt','EG','EGY'),
 (64,'El Salvador','SV','SLV'),
 (65,'Equatorial Guinea','GQ','GNQ');
INSERT INTO `country_type` VALUES  (66,'Eritrea','ER','ERI'),
 (67,'Estonia','EE','EST'),
 (68,'Ethiopia','ET','ETH'),
 (69,'Falkland Islands (Malvinas)','FK','FLK'),
 (70,'Faroe Islands','FO','FRO'),
 (71,'Fiji','FJ','FJI'),
 (72,'Finland','FI','FIN'),
 (73,'France','FR','FRA'),
 (74,'France, Metropolitan','FX','FXX'),
 (75,'French Guiana','GF','GUF'),
 (76,'French Polynesia','PF','PYF'),
 (77,'French Southern Territories','TF','ATF'),
 (78,'Gabon','GA','GAB'),
 (79,'Gambia','GM','GMB'),
 (80,'Georgia','GE','GEO'),
 (81,'Germany','DE','DEU'),
 (82,'Ghana','GH','GHA'),
 (83,'Gibraltar','GI','GIB'),
 (84,'Greece','GR','GRC'),
 (85,'Greenland','GL','GRL'),
 (86,'Grenada','GD','GRD'),
 (87,'Guadeloupe','GP','GLP'),
 (88,'Guam','GU','GUM'),
 (89,'Guatemala','GT','GTM'),
 (90,'Guinea','GN','GIN'),
 (91,'Guinea-bissau','GW','GNB'),
 (92,'Guyana','GY','GUY'),
 (93,'Haiti','HT','HTI'),
 (94,'Heard and Mc Donald Islands','HM','HMD'),
 (95,'Honduras','HN','HND'),
 (96,'Hong Kong','HK','HKG'),
 (97,'Hungary','HU','HUN');
INSERT INTO `country_type` VALUES  (98,'Iceland','IS','ISL'),
 (99,'India','IN','IND'),
 (100,'Indonesia','ID','IDN'),
 (101,'Iran (Islamic Republic of)','IR','IRN'),
 (102,'Iraq','IQ','IRQ'),
 (103,'Ireland','IE','IRL'),
 (104,'Israel','IL','ISR'),
 (105,'Italy','IT','ITA'),
 (106,'Jamaica','JM','JAM'),
 (107,'Japan','JP','JPN'),
 (108,'Jordan','JO','JOR'),
 (109,'Kazakhstan','KZ','KAZ'),
 (110,'Kenya','KE','KEN'),
 (111,'Kiribati','KI','KIR'),
 (112,'Korea, Democratic People\'s Republic of','KP','PRK'),
 (113,'Korea, Republic of','KR','KOR'),
 (114,'Kuwait','KW','KWT'),
 (115,'Kyrgyzstan','KG','KGZ'),
 (116,'Lao People\'s Democratic Republic','LA','LAO'),
 (117,'Latvia','LV','LVA'),
 (118,'Lebanon','LB','LBN'),
 (119,'Lesotho','LS','LSO'),
 (120,'Liberia','LR','LBR'),
 (121,'Libyan Arab Jamahiriya','LY','LBY'),
 (122,'Liechtenstein','LI','LIE'),
 (123,'Lithuania','LT','LTU'),
 (124,'Luxembourg','LU','LUX'),
 (125,'Macau','MO','MAC'),
 (126,'Macedonia, The Former Yugoslav Republic of','MK','MKD'),
 (127,'Madagascar','MG','MDG');
INSERT INTO `country_type` VALUES  (128,'Malawi','MW','MWI'),
 (129,'Malaysia','MY','MYS'),
 (130,'Maldives','MV','MDV'),
 (131,'Mali','ML','MLI'),
 (132,'Malta','MT','MLT'),
 (133,'Marshall Islands','MH','MHL'),
 (134,'Martinique','MQ','MTQ'),
 (135,'Mauritania','MR','MRT'),
 (136,'Mauritius','MU','MUS'),
 (137,'Mayotte','YT','MYT'),
 (138,'Mexico','MX','MEX'),
 (139,'Micronesia, Federated States of','FM','FSM'),
 (140,'Moldova, Republic of','MD','MDA'),
 (141,'Monaco','MC','MCO'),
 (142,'Mongolia','MN','MNG'),
 (143,'Montserrat','MS','MSR'),
 (144,'Morocco','MA','MAR'),
 (145,'Mozambique','MZ','MOZ'),
 (146,'Myanmar','MM','MMR'),
 (147,'Namibia','NA','NAM'),
 (148,'Nauru','NR','NRU'),
 (149,'Nepal','NP','NPL'),
 (150,'Netherlands','NL','NLD'),
 (151,'Netherlands Antilles','AN','ANT'),
 (152,'New Caledonia','NC','NCL'),
 (153,'New Zealand','NZ','NZL'),
 (154,'Nicaragua','NI','NIC'),
 (155,'Niger','NE','NER'),
 (156,'Nigeria','NG','NGA'),
 (157,'Niue','NU','NIU'),
 (158,'Norfolk Island','NF','NFK'),
 (159,'Northern Mariana Islands','MP','MNP');
INSERT INTO `country_type` VALUES  (160,'Norway','NO','NOR'),
 (161,'Oman','OM','OMN'),
 (162,'Pakistan','PK','PAK'),
 (163,'Palau','PW','PLW'),
 (164,'Panama','PA','PAN'),
 (165,'Papua New Guinea','PG','PNG'),
 (166,'Paraguay','PY','PRY'),
 (167,'Peru','PE','PER'),
 (168,'Philippines','PH','PHL'),
 (169,'Pitcairn','PN','PCN'),
 (170,'Poland','PL','POL'),
 (171,'Portugal','PT','PRT'),
 (172,'Puerto Rico','PR','PRI'),
 (173,'Qatar','QA','QAT'),
 (174,'Reunion','RE','REU'),
 (175,'Romania','RO','ROM'),
 (176,'Russian Federation','RU','RUS'),
 (177,'Rwanda','RW','RWA'),
 (178,'Saint Kitts and Nevis','KN','KNA'),
 (179,'Saint Lucia','LC','LCA'),
 (180,'Saint Vincent and the Grenadines','VC','VCT'),
 (181,'Samoa','WS','WSM'),
 (182,'San Marino','SM','SMR'),
 (183,'Sao Tome and Principe','ST','STP'),
 (184,'Saudi Arabia','SA','SAU'),
 (185,'Senegal','SN','SEN'),
 (186,'Seychelles','SC','SYC'),
 (187,'Sierra Leone','SL','SLE'),
 (188,'Singapore','SG','SGP'),
 (189,'Slovakia (Slovak Republic)','SK','SVK'),
 (190,'Slovenia','SI','SVN');
INSERT INTO `country_type` VALUES  (191,'Solomon Islands','SB','SLB'),
 (192,'Somalia','SO','SOM'),
 (193,'South Africa','ZA','ZAF'),
 (194,'South Georgia and the South Sandwich Islands','GS','SGS'),
 (195,'Spain','ES','ESP'),
 (196,'Sri Lanka','LK','LKA'),
 (197,'St. Helena','SH','SHN'),
 (198,'St. Pierre and Miquelon','PM','SPM'),
 (199,'Sudan','SD','SDN'),
 (200,'Suriname','SR','SUR'),
 (201,'Svalbard and Jan Mayen Islands','SJ','SJM'),
 (202,'Swaziland','SZ','SWZ'),
 (203,'Sweden','SE','SWE'),
 (204,'Switzerland','CH','CHE'),
 (205,'Syrian Arab Republic','SY','SYR'),
 (206,'Taiwan','TW','TWN'),
 (207,'Tajikistan','TJ','TJK'),
 (208,'Tanzania, United Republic of','TZ','TZA'),
 (209,'Thailand','TH','THA'),
 (210,'Togo','TG','TGO'),
 (211,'Tokelau','TK','TKL'),
 (212,'Tonga','TO','TON'),
 (213,'Trinidad and Tobago','TT','TTO'),
 (214,'Tunisia','TN','TUN'),
 (215,'Turkey','TR','TUR'),
 (216,'Turkmenistan','TM','TKM'),
 (217,'Turks and Caicos Islands','TC','TCA'),
 (218,'Tuvalu','TV','TUV'),
 (219,'Uganda','UG','UGA');
INSERT INTO `country_type` VALUES  (220,'Ukraine','UA','UKR'),
 (221,'United Arab Emirates','AE','ARE'),
 (222,'United Kingdom','GB','GBR'),
 (223,'United States','US','USA'),
 (224,'United States Minor Outlying Islands','UM','UMI'),
 (225,'Uruguay','UY','URY'),
 (226,'Uzbekistan','UZ','UZB'),
 (227,'Vanuatu','VU','VUT'),
 (228,'Vatican City State (Holy See)','VA','VAT'),
 (229,'Venezuela','VE','VEN'),
 (230,'Viet Nam','VN','VNM'),
 (231,'Virgin Islands (British)','VG','VGB'),
 (232,'Virgin Islands (U.S.)','VI','VIR'),
 (233,'Wallis and Futuna Islands','WF','WLF'),
 (234,'Western Sahara','EH','ESH'),
 (235,'Yemen','YE','YEM'),
 (236,'Yugoslavia','YU','YUG'),
 (237,'Zaire','ZR','ZAR'),
 (238,'Zambia','ZM','ZMB'),
 (239,'Zimbabwe','ZW','ZWE'),
 (240,'Aaland Islands','AX','ALA'),
 (255,'World','--','---');
UNLOCK TABLES;
/*!40000 ALTER TABLE `country_type` ENABLE KEYS */;


--
-- Definition of table `html_meta_tag`
--

DROP TABLE IF EXISTS `html_meta_tag`;
CREATE TABLE  `html_meta_tag` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  `content` varchar(256) default NULL,
  `type` enum('NAME','HTTP-EQUIV') default 'NAME',
  PRIMARY KEY  (`id`),
  KEY `idx_html_meta_tag_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `html_meta_tag`
--

/*!40000 ALTER TABLE `html_meta_tag` DISABLE KEYS */;
LOCK TABLES `html_meta_tag` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `html_meta_tag` ENABLE KEYS */;


--
-- Definition of table `image_size_type`
--

DROP TABLE IF EXISTS `image_size_type`;
CREATE TABLE  `image_size_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_image_size_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `image_size_type`
--

/*!40000 ALTER TABLE `image_size_type` DISABLE KEYS */;
LOCK TABLES `image_size_type` WRITE;
INSERT INTO `image_size_type` VALUES  (6,'FullScreen'),
 (1,'Icon'),
 (7,'Intergalactic'),
 (5,'Large'),
 (4,'Medium'),
 (3,'Small'),
 (2,'Thumb');
UNLOCK TABLES;
/*!40000 ALTER TABLE `image_size_type` ENABLE KEYS */;


--
-- Definition of table `java_script`
--

DROP TABLE IF EXISTS `java_script`;
CREATE TABLE  `java_script` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  `description` varchar(256) default NULL,
  `filename` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_java_script_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `java_script`
--

/*!40000 ALTER TABLE `java_script` DISABLE KEYS */;
LOCK TABLES `java_script` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `java_script` ENABLE KEYS */;


--
-- Definition of table `menu`
--

DROP TABLE IF EXISTS `menu`;
CREATE TABLE  `menu` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `title` varchar(32) default NULL,
  `css_class` varchar(32) default NULL,
  `sort_order` tinyint(3) unsigned default '0',
  `show_title` tinyint(1) default '1',
  `menu_item_id` mediumint(8) unsigned default '0',
  `public_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  `user_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  `group_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  `status_id` tinyint(3) unsigned NOT NULL default '1',
  `type_id` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_menu_name` (`name`),
  KEY `idx_menu_item` (`menu_item_id`),
  KEY `idx_menu_type` (`type_id`),
  KEY `idx_menu_status` (`status_id`),
  KEY `idx_menu_public_perms` (`public_permissions_id`),
  KEY `idx_menu_user_perms` (`user_permissions_id`),
  KEY `idx_menu_group_perms` (`group_permissions_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `menu`
--

/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
LOCK TABLES `menu` WRITE;
INSERT INTO `menu` VALUES  (1,'MainMenu','Main Menu','',0,0,NULL,1,1,1,1,3),
 (2,'MemberMenu','My Account',NULL,0,1,NULL,1,2,1,1,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;


--
-- Definition of table `menu_content_block_assn`
--

DROP TABLE IF EXISTS `menu_content_block_assn`;
CREATE TABLE  `menu_content_block_assn` (
  `menu_id` smallint(5) unsigned NOT NULL,
  `content_block_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`content_block_id`,`menu_id`),
  KEY `idx_menu_content_block_menu` (`menu_id`),
  KEY `idx_menu_content_block_content_block` (`content_block_id`),
  CONSTRAINT `menu_content_block_assn_ibfk_1` FOREIGN KEY (`content_block_id`) REFERENCES `content_block` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_content_block_assn_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `menu_content_block_assn`
--

/*!40000 ALTER TABLE `menu_content_block_assn` DISABLE KEYS */;
LOCK TABLES `menu_content_block_assn` WRITE;
INSERT INTO `menu_content_block_assn` VALUES  (1,8),
 (2,10);
UNLOCK TABLES;
/*!40000 ALTER TABLE `menu_content_block_assn` ENABLE KEYS */;


--
-- Definition of table `menu_item`
--

DROP TABLE IF EXISTS `menu_item`;
CREATE TABLE  `menu_item` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `css_class` varchar(32) default NULL,
  `label` varchar(32) default NULL,
  `uri` varchar(256) NOT NULL,
  `is_local` tinyint(1) NOT NULL default '1',
  `is_ssl` tinyint(1) NOT NULL default '0',
  `sort_order` tinyint(3) unsigned NOT NULL default '0',
  `public_permissions_id` tinyint(3) unsigned NOT NULL default '2',
  `user_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  `group_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  `status_id` tinyint(3) unsigned NOT NULL default '1',
  `type_id` tinyint(3) unsigned NOT NULL default '1',
  `page_id` mediumint(8) unsigned default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_menu_item_name` (`name`),
  KEY `idx_menu_item_type` (`type_id`),
  KEY `idx_menu_item_status` (`status_id`),
  KEY `idx_menu_item_public_perms` (`public_permissions_id`),
  KEY `idx_menu_item_user_perms` (`user_permissions_id`),
  KEY `idx_menu_item_group_perms` (`group_permissions_id`),
  KEY `page_id` (`page_id`),
  CONSTRAINT `menu_item_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `menu_item`
--

/*!40000 ALTER TABLE `menu_item` DISABLE KEYS */;
LOCK TABLES `menu_item` WRITE;
INSERT INTO `menu_item` VALUES  (1,'ContactUs',NULL,'Contact Us','ContactUs',1,0,1,1,1,1,1,1,2),
 (2,'HomeLink','','Home','Home',1,0,0,1,1,1,1,2,1),
 (4,'HelpLink','','Help','Help',1,0,3,1,1,1,1,2,NULL),
 (5,'KnowledgeLink',NULL,'Knowledge Base','KnowledgeBase',1,0,4,1,1,1,1,1,NULL),
 (6,'UploadLink','','Upload','Upload',1,0,5,1,1,1,1,2,NULL),
 (8,'ForumsLink','','Forum','Forum',1,0,6,1,1,1,1,2,NULL),
 (12,'LinksPageLink','','Other Links','LinksPage',1,0,5,1,1,1,1,2,NULL),
 (14,'AccountAddress',NULL,'Addresses','AccountHome/Address',1,0,1,1,1,1,1,1,5),
 (15,'Account Orders',NULL,'View Orders','AccountHome/Order',1,0,2,1,1,1,1,1,5),
 (16,'Account Settings',NULL,'Preferences','AccountHome/Settings',1,0,3,1,2,1,1,1,5);
UNLOCK TABLES;
/*!40000 ALTER TABLE `menu_item` ENABLE KEYS */;


--
-- Definition of table `menu_item_menu_assn`
--

DROP TABLE IF EXISTS `menu_item_menu_assn`;
CREATE TABLE  `menu_item_menu_assn` (
  `menu_item_id` mediumint(8) unsigned NOT NULL,
  `menu_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`menu_id`,`menu_item_id`),
  KEY `idx_menu_item_menu_menu_item` (`menu_item_id`),
  KEY `idx_menu_item_menu_menu` (`menu_id`),
  CONSTRAINT `menu_item_menu_assn_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_item_menu_assn_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_item` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `menu_item_menu_assn`
--

/*!40000 ALTER TABLE `menu_item_menu_assn` DISABLE KEYS */;
LOCK TABLES `menu_item_menu_assn` WRITE;
INSERT INTO `menu_item_menu_assn` VALUES  (2,1),
 (4,1),
 (6,1),
 (8,1),
 (14,2),
 (15,2),
 (16,2);
UNLOCK TABLES;
/*!40000 ALTER TABLE `menu_item_menu_assn` ENABLE KEYS */;


--
-- Definition of table `menu_item_type`
--

DROP TABLE IF EXISTS `menu_item_type`;
CREATE TABLE  `menu_item_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_menu_item_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `menu_item_type`
--

/*!40000 ALTER TABLE `menu_item_type` DISABLE KEYS */;
LOCK TABLES `menu_item_type` WRITE;
INSERT INTO `menu_item_type` VALUES  (3,'BlockMenuItem'),
 (4,'LinkMenuItem'),
 (1,'ListMenuItem'),
 (2,'TabMenuItem');
UNLOCK TABLES;
/*!40000 ALTER TABLE `menu_item_type` ENABLE KEYS */;


--
-- Definition of table `menu_status_type`
--

DROP TABLE IF EXISTS `menu_status_type`;
CREATE TABLE  `menu_status_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_menu_status_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `menu_status_type`
--

/*!40000 ALTER TABLE `menu_status_type` DISABLE KEYS */;
LOCK TABLES `menu_status_type` WRITE;
INSERT INTO `menu_status_type` VALUES  (1,'Active'),
 (2,'Disabled');
UNLOCK TABLES;
/*!40000 ALTER TABLE `menu_status_type` ENABLE KEYS */;


--
-- Definition of table `menu_type`
--

DROP TABLE IF EXISTS `menu_type`;
CREATE TABLE  `menu_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_menu_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `menu_type`
--

/*!40000 ALTER TABLE `menu_type` DISABLE KEYS */;
LOCK TABLES `menu_type` WRITE;
INSERT INTO `menu_type` VALUES  (4,'Footer'),
 (2,'Header'),
 (1,'SideBar'),
 (3,'Tabbed');
UNLOCK TABLES;
/*!40000 ALTER TABLE `menu_type` ENABLE KEYS */;


--
-- Definition of table `module`
--

DROP TABLE IF EXISTS `module`;
CREATE TABLE  `module` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(128) NOT NULL,
  `cssclass` varchar(128) default NULL,
  `title` varchar(128) default NULL,
  `description` varchar(256) default NULL,
  `class_name` varchar(256) default NULL,
  `show_title` tinyint(1) NOT NULL default '1',
  `show_description` tinyint(1) NOT NULL default '0',
  `content_block_id` mediumint(8) unsigned default NULL,
  `parent_module_id` int(10) unsigned default NULL,
  `public_permissions_id` tinyint(3) unsigned NOT NULL default '2',
  `user_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  `group_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_module_name` (`name`),
  KEY `idx_module_block` (`content_block_id`),
  KEY `idx_module_public_perms` (`public_permissions_id`),
  KEY `idx_module_user_perms` (`user_permissions_id`),
  KEY `idx_module_group_perms` (`group_permissions_id`),
  KEY `idx_module_title` (`title`),
  KEY `fk_parent_module` (`parent_module_id`),
  CONSTRAINT `fk_parent_module` FOREIGN KEY (`parent_module_id`) REFERENCES `module` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `module`
--

/*!40000 ALTER TABLE `module` DISABLE KEYS */;
LOCK TABLES `module` WRITE;
INSERT INTO `module` VALUES  (1,'Login Module','LoginModule','Member Login','Enter a Username and Password','LoginModule',1,1,7,NULL,1,1,1),
 (3,'Registration Module','','Quasi CMS Registration','Please fill in the fields below to register','CreateAccountModule',1,1,9,NULL,2,1,1),
 (4,'Account Home Module','AccountHome','','','AccountHomeModule',0,0,NULL,NULL,1,1,1),
 (5,'Account Manager','AccountManager','','','AccountManagerModule',0,0,12,NULL,1,1,1),
 (7,'Shopping Cart','','','','ShoppingCartModule',0,0,15,NULL,1,1,1),
 (8,'Product Display','','','','ProductDisplayModule',0,0,16,NULL,1,1,1),
 (9,'Shopping Cart View','CartView','Shopping Cart Contents','You can adust the quantities for products here too','ShoppingCartViewModule',1,1,17,NULL,1,1,1),
 (10,'Checkout Module','CheckOutModule','','','CheckOutModule',0,0,18,NULL,1,1,1),
 (11,'PayPalExpressReturnModule','','','','PayPalExpressReturnModule',0,0,19,NULL,1,1,1),
 (12,'Lost Password Module','','','','LostPasswordModule',0,0,20,NULL,2,1,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `module` ENABLE KEYS */;


--
-- Definition of table `name_type`
--

DROP TABLE IF EXISTS `name_type`;
CREATE TABLE  `name_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_name_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `name_type`
--

/*!40000 ALTER TABLE `name_type` DISABLE KEYS */;
LOCK TABLES `name_type` WRITE;
INSERT INTO `name_type` VALUES  (5,'Alias'),
 (3,'Billing'),
 (6,'Friend'),
 (4,'Historical'),
 (1,'Primary'),
 (2,'Shipping');
UNLOCK TABLES;
/*!40000 ALTER TABLE `name_type` ENABLE KEYS */;


--
-- Definition of table `order`
--

DROP TABLE IF EXISTS `order`;
CREATE TABLE  `order` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `account_id` mediumint(8) unsigned NOT NULL default '1',
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `last_modification_date` timestamp NOT NULL default '0000-00-00 00:00:00',
  `completion_date` datetime default '0000-00-00 00:00:00',
  `shipping_cost` decimal(12,2) default NULL,
  `product_total_cost` decimal(12,2) default NULL,
  `shipping_charged` decimal(12,2) default NULL,
  `handling_charged` decimal(12,2) default NULL,
  `tax` decimal(12,2) default NULL,
  `product_total_charged` decimal(12,2) default NULL,
  `shipping_name_prefix` varchar(8) default NULL,
  `shipping_first_name` varchar(128) default NULL,
  `shipping_middle_name` varchar(128) default NULL,
  `shipping_last_name` varchar(128) default NULL,
  `shipping_name_suffix` varchar(8) default NULL,
  `shipping_company` varchar(128) default NULL,
  `shipping_street1` varchar(128) default NULL,
  `shipping_street2` varchar(128) default NULL,
  `shipping_suburb` varchar(128) default NULL,
  `shipping_county` varchar(128) default NULL,
  `shipping_city` varchar(128) default NULL,
  `shipping_zone_id` smallint(5) unsigned default NULL,
  `shipping_country_id` smallint(5) unsigned default NULL,
  `shipping_postal_code` varchar(16) default NULL,
  `billing_name_prefix` varchar(8) default NULL,
  `billing_first_name` varchar(128) default NULL,
  `billing_middle_name` varchar(128) default NULL,
  `billing_last_name` varchar(128) default NULL,
  `billing_name_suffix` varchar(8) default NULL,
  `billing_company` varchar(128) default NULL,
  `billing_street1` varchar(128) default NULL,
  `billing_street2` varchar(128) default NULL,
  `billing_suburb` varchar(128) default NULL,
  `billing_county` varchar(128) default NULL,
  `billing_city` varchar(128) default NULL,
  `billing_zone_id` smallint(5) unsigned default NULL,
  `billing_country_id` smallint(5) unsigned default NULL,
  `billing_postal_code` varchar(16) default NULL,
  `notes` text,
  `shipping_method_id` tinyint(3) unsigned default '1',
  `payment_method_id` tinyint(3) unsigned default '1',
  `status_id` tinyint(3) unsigned NOT NULL default '1',
  `type_id` tinyint(3) unsigned default '1',
  PRIMARY KEY  (`id`),
  KEY `idx_order_account` (`account_id`),
  KEY `idx_shipping_method` (`shipping_method_id`),
  KEY `idx_order_status` (`status_id`),
  KEY `idx_order_delivery_zone` (`shipping_zone_id`),
  KEY `idx_order_billing_zone` (`billing_zone_id`),
  KEY `idx_order_delivery_country` (`shipping_country_id`),
  KEY `idx_order_billing_country` (`billing_country_id`),
  KEY `idx_payment_method` (`payment_method_id`),
  KEY `idx_order_type` (`type_id`),
  CONSTRAINT `order_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `order`
--

/*!40000 ALTER TABLE `order` DISABLE KEYS */;
LOCK TABLES `order` WRITE;
INSERT INTO `order` VALUES  (1,1,'2008-09-28 19:29:24','2008-09-28 12:29:24','0000-00-00 00:00:00',NULL,'0.00',NULL,'10.00',NULL,'32.50','Mr.','Joe','','Shmoe','Sr.',NULL,'123 Any Street','Apt. 3','Bronx','','New York',43,223,'12345','Mr.','Joe','','Shmoe','Sr.',NULL,'431 Office Street','','','Laredo','Commerce City',13,223,'09889',NULL,1,2,2,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `order` ENABLE KEYS */;


--
-- Definition of table `order_change`
--

DROP TABLE IF EXISTS `order_change`;
CREATE TABLE  `order_change` (
  `order_id` bigint(20) unsigned NOT NULL,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `notes` text,
  `value` decimal(15,2) default NULL,
  `type_id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`order_id`,`date`),
  KEY `idx_order_total_orderid` (`order_id`),
  KEY `idx_order_total_typeid` (`type_id`),
  CONSTRAINT `order_change_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `order_change`
--

/*!40000 ALTER TABLE `order_change` DISABLE KEYS */;
LOCK TABLES `order_change` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `order_change` ENABLE KEYS */;


--
-- Definition of table `order_change_type`
--

DROP TABLE IF EXISTS `order_change_type`;
CREATE TABLE  `order_change_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_order_total_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `order_change_type`
--

/*!40000 ALTER TABLE `order_change_type` DISABLE KEYS */;
LOCK TABLES `order_change_type` WRITE;
INSERT INTO `order_change_type` VALUES  (5,'ItemAddition'),
 (3,'ItemDiscount'),
 (4,'ItemQuantity'),
 (2,'OrderDiscount'),
 (1,'Refund'),
 (6,'ShippingAddition');
UNLOCK TABLES;
/*!40000 ALTER TABLE `order_change_type` ENABLE KEYS */;


--
-- Definition of table `order_item`
--

DROP TABLE IF EXISTS `order_item`;
CREATE TABLE  `order_item` (
  `product_id` mediumint(8) unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `quantity` mediumint(8) unsigned NOT NULL default '1',
  `status_id` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`product_id`,`order_id`),
  KEY `idx_product_order_order` (`order_id`),
  KEY `idx_product_order_product` (`product_id`),
  CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `order_item`
--

/*!40000 ALTER TABLE `order_item` DISABLE KEYS */;
LOCK TABLES `order_item` WRITE;
INSERT INTO `order_item` VALUES  (2,1,1,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `order_item` ENABLE KEYS */;


--
-- Definition of table `order_item_status_type`
--

DROP TABLE IF EXISTS `order_item_status_type`;
CREATE TABLE  `order_item_status_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_order_item_status_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `order_item_status_type`
--

/*!40000 ALTER TABLE `order_item_status_type` DISABLE KEYS */;
LOCK TABLES `order_item_status_type` WRITE;
INSERT INTO `order_item_status_type` VALUES  (3,'BackOrdered'),
 (6,'Cancelled'),
 (7,'Internal'),
 (1,'Ordered'),
 (2,'Processing'),
 (5,'Returned'),
 (4,'Shipped');
UNLOCK TABLES;
/*!40000 ALTER TABLE `order_item_status_type` ENABLE KEYS */;


--
-- Definition of table `order_status_history`
--

DROP TABLE IF EXISTS `order_status_history`;
CREATE TABLE  `order_status_history` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `order_id` bigint(20) unsigned NOT NULL,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `notes` text,
  `status_id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_order_status_history_order` (`order_id`),
  KEY `idx_order_status_history_status` (`status_id`),
  CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `order_status_history`
--

/*!40000 ALTER TABLE `order_status_history` DISABLE KEYS */;
LOCK TABLES `order_status_history` WRITE;
INSERT INTO `order_status_history` VALUES  (1,1,'2008-09-28 19:29:24',NULL,2);
UNLOCK TABLES;
/*!40000 ALTER TABLE `order_status_history` ENABLE KEYS */;


--
-- Definition of table `order_status_type`
--

DROP TABLE IF EXISTS `order_status_type`;
CREATE TABLE  `order_status_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_order_status_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `order_status_type`
--

/*!40000 ALTER TABLE `order_status_type` DISABLE KEYS */;
LOCK TABLES `order_status_type` WRITE;
INSERT INTO `order_status_type` VALUES  (8,'Cancelled'),
 (6,'Packaged'),
 (3,'Paid'),
 (4,'Panelized'),
 (2,'Pending'),
 (11,'Problem'),
 (5,'Processing'),
 (10,'Refunded'),
 (9,'Returned'),
 (7,'Shipped'),
 (1,'Shopping');
UNLOCK TABLES;
/*!40000 ALTER TABLE `order_status_type` ENABLE KEYS */;


--
-- Definition of table `order_type`
--

DROP TABLE IF EXISTS `order_type`;
CREATE TABLE  `order_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_order_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `order_type`
--

/*!40000 ALTER TABLE `order_type` DISABLE KEYS */;
LOCK TABLES `order_type` WRITE;
INSERT INTO `order_type` VALUES  (4,'Affiliate'),
 (3,'Employee'),
 (2,'Internal'),
 (1,'Normal');
UNLOCK TABLES;
/*!40000 ALTER TABLE `order_type` ENABLE KEYS */;


--
-- Definition of table `page`
--

DROP TABLE IF EXISTS `page`;
CREATE TABLE  `page` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `last_modification` timestamp NOT NULL default '0000-00-00 00:00:00',
  `name` varchar(128) default NULL,
  `title` varchar(256) default NULL,
  `uri` varchar(256) default 'index.php',
  `has_header` tinyint(1) NOT NULL default '1',
  `has_left_column` tinyint(1) NOT NULL default '1',
  `has_right_column` tinyint(1) NOT NULL default '1',
  `has_footer` tinyint(1) NOT NULL default '1',
  `public_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  `user_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  `group_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  `type_id` tinyint(3) unsigned default '1',
  `doc_type_id` tinyint(3) unsigned NOT NULL default '1',
  `status_id` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_page_name` (`name`),
  KEY `idx_page_doc_type` (`doc_type_id`),
  KEY `idx_page_type` (`type_id`),
  KEY `idx_page_status` (`status_id`),
  KEY `idx_page_public_perms` (`public_permissions_id`),
  KEY `idx_page_user_perms` (`user_permissions_id`),
  KEY `idx_page_group_perms` (`group_permissions_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `page`
--

/*!40000 ALTER TABLE `page` DISABLE KEYS */;
LOCK TABLES `page` WRITE;
INSERT INTO `page` VALUES  (1,'2008-05-31 18:30:11','0000-00-00 00:00:00','Home','The Amazing Test Page','testpage',1,1,0,1,2,2,3,2,1,1),
 (2,'2008-06-02 08:46:28','0000-00-00 00:00:00','ContactUs','Contact Us','contact-us',1,1,0,1,1,1,1,1,1,1),
 (4,'2008-06-05 18:00:06','0000-00-00 00:00:00','CreateAccount','Create an Account','',1,0,0,1,1,1,1,1,1,1),
 (5,'2008-06-07 14:00:48','0000-00-00 00:00:00','AccountHome','Quasi CMS Account','',1,1,0,1,1,2,1,4,1,1),
 (6,'2008-06-12 09:24:32','0000-00-00 00:00:00','Upload','Upload a Design','Upload',1,1,0,1,1,1,1,NULL,1,1),
 (7,'2008-06-22 16:55:59','0000-00-00 00:00:00','Help','','',1,1,1,1,2,1,1,NULL,1,1),
 (8,'2008-07-03 05:55:18','0000-00-00 00:00:00','Products','Products','products',1,1,0,1,2,1,1,8,1,1),
 (9,'2008-07-07 10:22:58','0000-00-00 00:00:00','ShoppingCart','Quasi CMS Shopping Cart ','shopping-cart',1,1,0,1,1,3,1,9,1,1),
 (10,'2008-07-07 14:59:46','0000-00-00 00:00:00','CheckOut','Check Out','check-out',1,1,0,1,1,1,1,10,1,1),
 (11,'2008-08-04 15:21:17','0000-00-00 00:00:00','PayPalExpressReturn','Thank You for using Quasi CMS!','paypalexpressreturn',1,1,0,1,1,1,1,11,1,1);
INSERT INTO `page` VALUES  (12,'2008-09-03 09:44:24','0000-00-00 00:00:00','LostPassword','Lost Password Retrieval','',1,1,0,1,2,1,1,1,1,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `page` ENABLE KEYS */;


--
-- Definition of table `page_content_category_assn`
--

DROP TABLE IF EXISTS `page_content_category_assn`;
CREATE TABLE  `page_content_category_assn` (
  `page_id` mediumint(8) unsigned NOT NULL,
  `content_category_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`content_category_id`,`page_id`),
  KEY `idx_page_content_category_page` (`page_id`),
  KEY `idx_page_content_category_content_category` (`content_category_id`),
  CONSTRAINT `page_content_category_assn_ibfk_1` FOREIGN KEY (`content_category_id`) REFERENCES `content_category` (`id`) ON DELETE CASCADE,
  CONSTRAINT `page_content_category_assn_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `page_content_category_assn`
--

/*!40000 ALTER TABLE `page_content_category_assn` DISABLE KEYS */;
LOCK TABLES `page_content_category_assn` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `page_content_category_assn` ENABLE KEYS */;


--
-- Definition of table `page_doc_type`
--

DROP TABLE IF EXISTS `page_doc_type`;
CREATE TABLE  `page_doc_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_page_doc_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `page_doc_type`
--

/*!40000 ALTER TABLE `page_doc_type` DISABLE KEYS */;
LOCK TABLES `page_doc_type` WRITE;
INSERT INTO `page_doc_type` VALUES  (7,'<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\" \"http://www.w3.org/TR/html4/frameset.dtd\">'),
 (6,'<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">'),
 (5,'<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">'),
 (9,'<!DOCTYPE HTML PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">'),
 (8,'<!DOCTYPE HTML PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">'),
 (1,'<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">'),
 (10,'<!DOCTYPE HTML PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">'),
 (11,'<!DOCTYPE HTML PUBLIC \"-//WAPFORUM//DTD XHTML Mobile 1.0//EN\" \"http://www.wapforum.org/DTD/xhtml-mobile10.dtd\">'),
 (12,'<!DOCTYPE HTML PUBLIC \"-//WAPFORUM//DTD XHTML Mobile 1.1//EN\" \"http://www.openmobilealliance.org/tech/DTD/xhtml-mobile11.dtd\">');
INSERT INTO `page_doc_type` VALUES  (2,'PDF'),
 (4,'RSS'),
 (3,'TEXT');
UNLOCK TABLES;
/*!40000 ALTER TABLE `page_doc_type` ENABLE KEYS */;


--
-- Definition of table `page_html_meta_tag_assn`
--

DROP TABLE IF EXISTS `page_html_meta_tag_assn`;
CREATE TABLE  `page_html_meta_tag_assn` (
  `page_id` mediumint(8) unsigned NOT NULL,
  `html_meta_tag_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`html_meta_tag_id`,`page_id`),
  KEY `idx_page_html_meta_tag_page` (`page_id`),
  KEY `idx_page_html_meta_tag_html_meta_tag` (`html_meta_tag_id`),
  CONSTRAINT `page_html_meta_tag_assn_ibfk_1` FOREIGN KEY (`html_meta_tag_id`) REFERENCES `html_meta_tag` (`id`) ON DELETE CASCADE,
  CONSTRAINT `page_html_meta_tag_assn_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `page_html_meta_tag_assn`
--

/*!40000 ALTER TABLE `page_html_meta_tag_assn` DISABLE KEYS */;
LOCK TABLES `page_html_meta_tag_assn` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `page_html_meta_tag_assn` ENABLE KEYS */;


--
-- Definition of table `page_java_script_assn`
--

DROP TABLE IF EXISTS `page_java_script_assn`;
CREATE TABLE  `page_java_script_assn` (
  `page_id` mediumint(8) unsigned NOT NULL,
  `java_script_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`java_script_id`,`page_id`),
  KEY `idx_page_java_script_page` (`page_id`),
  KEY `idx_page_java_script_java_script` (`java_script_id`),
  CONSTRAINT `page_java_script_assn_ibfk_1` FOREIGN KEY (`java_script_id`) REFERENCES `java_script` (`id`) ON DELETE CASCADE,
  CONSTRAINT `page_java_script_assn_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `page_java_script_assn`
--

/*!40000 ALTER TABLE `page_java_script_assn` DISABLE KEYS */;
LOCK TABLES `page_java_script_assn` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `page_java_script_assn` ENABLE KEYS */;


--
-- Definition of table `page_status_type`
--

DROP TABLE IF EXISTS `page_status_type`;
CREATE TABLE  `page_status_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_page_status_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `page_status_type`
--

/*!40000 ALTER TABLE `page_status_type` DISABLE KEYS */;
LOCK TABLES `page_status_type` WRITE;
INSERT INTO `page_status_type` VALUES  (3,'Draft'),
 (4,'Internal'),
 (1,'Published'),
 (2,'Unpublished');
UNLOCK TABLES;
/*!40000 ALTER TABLE `page_status_type` ENABLE KEYS */;


--
-- Definition of table `page_style_sheet_assn`
--

DROP TABLE IF EXISTS `page_style_sheet_assn`;
CREATE TABLE  `page_style_sheet_assn` (
  `page_id` mediumint(8) unsigned NOT NULL,
  `style_sheet_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`style_sheet_id`,`page_id`),
  KEY `idx_page_style_sheet_page` (`page_id`),
  KEY `idx_page_style_sheet_style_sheet` (`style_sheet_id`),
  CONSTRAINT `page_style_sheet_assn_ibfk_1` FOREIGN KEY (`style_sheet_id`) REFERENCES `style_sheet` (`id`) ON DELETE CASCADE,
  CONSTRAINT `page_style_sheet_assn_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `page_style_sheet_assn`
--

/*!40000 ALTER TABLE `page_style_sheet_assn` DISABLE KEYS */;
LOCK TABLES `page_style_sheet_assn` WRITE;
INSERT INTO `page_style_sheet_assn` VALUES  (1,1),
 (4,1),
 (5,1),
 (6,1),
 (7,1),
 (8,1),
 (9,1),
 (10,1),
 (11,1),
 (12,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `page_style_sheet_assn` ENABLE KEYS */;


--
-- Definition of table `page_type`
--

DROP TABLE IF EXISTS `page_type`;
CREATE TABLE  `page_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_page_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `page_type`
--

/*!40000 ALTER TABLE `page_type` DISABLE KEYS */;
LOCK TABLES `page_type` WRITE;
INSERT INTO `page_type` VALUES  (18,'Admin'),
 (16,'Article'),
 (17,'ArticleList'),
 (1,'Basic'),
 (10,'Checkout'),
 (5,'EditContactInfo'),
 (6,'EditPersons'),
 (14,'Forum'),
 (15,'ForumList'),
 (2,'Home'),
 (3,'Login'),
 (13,'OrderHistory'),
 (11,'Payment'),
 (7,'Product'),
 (8,'ProductList'),
 (12,'ShippingInfo'),
 (9,'ShoppingCartView'),
 (4,'UserHome');
UNLOCK TABLES;
/*!40000 ALTER TABLE `page_type` ENABLE KEYS */;


--
-- Definition of table `page_usergroup_assn`
--

DROP TABLE IF EXISTS `page_usergroup_assn`;
CREATE TABLE  `page_usergroup_assn` (
  `page_id` mediumint(8) unsigned NOT NULL,
  `usergroup_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`usergroup_id`,`page_id`),
  KEY `idx_page_usergroup_page` (`page_id`),
  KEY `idx_page_usergroup_usergroup` (`usergroup_id`),
  CONSTRAINT `page_usergroup_assn_ibfk_1` FOREIGN KEY (`usergroup_id`) REFERENCES `usergroup` (`id`) ON DELETE CASCADE,
  CONSTRAINT `page_usergroup_assn_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `page_usergroup_assn`
--

/*!40000 ALTER TABLE `page_usergroup_assn` DISABLE KEYS */;
LOCK TABLES `page_usergroup_assn` WRITE;
INSERT INTO `page_usergroup_assn` VALUES  (11,2),
 (12,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `page_usergroup_assn` ENABLE KEYS */;


--
-- Definition of table `payment_method`
--

DROP TABLE IF EXISTS `payment_method`;
CREATE TABLE  `payment_method` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `title` varchar(128) default '''Payment Method''',
  `service_provider` varchar(64) default NULL,
  `service_type` varchar(64) default NULL,
  `action_class_name` varchar(128) default NULL,
  `description` text,
  `image_uri` varchar(128) default NULL,
  `active` tinyint(1) default '0',
  `requires_cc_number` tinyint(1) default '0',
  `save_cc_number` tinyint(1) default '0',
  `test_mode` tinyint(1) default '1',
  `sort_order` tinyint(3) unsigned default '0',
  `payment_type_id` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `payment_method`
--

/*!40000 ALTER TABLE `payment_method` DISABLE KEYS */;
LOCK TABLES `payment_method` WRITE;
INSERT INTO `payment_method` VALUES  (1,'Check/Money Order','Our Store','Mail','PayByMailAction','Payment sent by mail',NULL,1,0,0,1,0,3),
 (2,'PayPal Express Checkout','Paypal.com','Express Checkout','PayPalNVPAction','Implementation of PayPal Express Checkout','https://www.paypal.com/en_US/i/logo/PayPal_mark_37x23.gif',1,0,0,1,2,1),
 (3,'Credit Card','Authorize.net','Credit Card','AuthorizeNetAIMAction','Authorize.net credit card payment processing','creditcards_sm.gif',1,1,0,1,3,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `payment_method` ENABLE KEYS */;


--
-- Definition of table `payment_type`
--

DROP TABLE IF EXISTS `payment_type`;
CREATE TABLE  `payment_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_payment_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `payment_type`
--

/*!40000 ALTER TABLE `payment_type` DISABLE KEYS */;
LOCK TABLES `payment_type` WRITE;
INSERT INTO `payment_type` VALUES  (3,'Cash'),
 (6,'Complimentary'),
 (4,'Credit'),
 (5,'GiftCertificate'),
 (2,'MailIn'),
 (1,'Online');
UNLOCK TABLES;
/*!40000 ALTER TABLE `payment_type` ENABLE KEYS */;


--
-- Definition of table `paypal_transaction`
--

DROP TABLE IF EXISTS `paypal_transaction`;
CREATE TABLE  `paypal_transaction` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `order_id` bigint(20) unsigned NOT NULL,
  `correlation_id` varchar(128) default NULL,
  `transaction_id` varchar(128) default NULL,
  `pp_token` varchar(128) default NULL,
  `payer_id` varchar(128) default NULL,
  `payer_status` varchar(128) default NULL,
  `payment_status` varchar(128) default NULL,
  `ack_returned` varchar(32) default NULL,
  `api_action` varchar(32) default NULL,
  `time_stamp` datetime default NULL,
  `api_version` varchar(4) default NULL,
  `messages` text,
  `amount` decimal(12,2) default NULL,
  `pp_fee` decimal(12,2) default NULL,
  `payment_method_id` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `idx_pp_transaction_correlationid` (`correlation_id`),
  KEY `idx_pp_transaction_orderid` (`order_id`),
  KEY `idx_pp_transaction_payment_methodid` (`payment_method_id`),
  CONSTRAINT `paypal_transaction_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `paypal_transaction`
--

/*!40000 ALTER TABLE `paypal_transaction` DISABLE KEYS */;
LOCK TABLES `paypal_transaction` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `paypal_transaction` ENABLE KEYS */;


--
-- Definition of table `permission_type`
--

DROP TABLE IF EXISTS `permission_type`;
CREATE TABLE  `permission_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_permssion_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `permission_type`
--

/*!40000 ALTER TABLE `permission_type` DISABLE KEYS */;
LOCK TABLES `permission_type` WRITE;
INSERT INTO `permission_type` VALUES  (4,'Delete'),
 (3,'Modify'),
 (1,'None'),
 (2,'View');
UNLOCK TABLES;
/*!40000 ALTER TABLE `permission_type` ENABLE KEYS */;


--
-- Definition of table `person`
--

DROP TABLE IF EXISTS `person`;
CREATE TABLE  `person` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name_prefix` varchar(32) default '',
  `first_name` varchar(256) NOT NULL,
  `middle_name` varchar(128) default '',
  `last_name` varchar(256) NOT NULL,
  `name_suffix` varchar(32) default '',
  `nick_name` varchar(128) default 'Anonymous',
  `email_address` varchar(128) NOT NULL,
  `phone_number` varchar(32) default 'N/A',
  `avatar_uri` varchar(256) default NULL,
  `company_name` varchar(256) default '',
  `owner_person_id` mediumint(8) unsigned default NULL,
  `is_virtual` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_person_firstname` (`first_name`(255)),
  KEY `idx_person_last_name` (`last_name`(255)),
  KEY `idx_person_email_address` (`email_address`),
  KEY `owner_person_id` (`owner_person_id`),
  CONSTRAINT `person_ibfk_1` FOREIGN KEY (`owner_person_id`) REFERENCES `person` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `person`
--

/*!40000 ALTER TABLE `person` DISABLE KEYS */;
LOCK TABLES `person` WRITE;
INSERT INTO `person` VALUES  (1,'Mr.','Joe','','Shmoe','Sr.','joe','joe@shmoe.com','','','JoeCo LLC.',NULL,0),
 (2,'Ms','Jane ','','Eyre','','','jane@eyre.com','','','',1,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `person` ENABLE KEYS */;


--
-- Definition of table `person_usergroup_assn`
--

DROP TABLE IF EXISTS `person_usergroup_assn`;
CREATE TABLE  `person_usergroup_assn` (
  `person_id` mediumint(8) unsigned NOT NULL,
  `usergroup_id` mediumint(8) unsigned NOT NULL default '1',
  PRIMARY KEY  (`usergroup_id`,`person_id`),
  KEY `idx_person_usergroup_person` (`person_id`),
  KEY `idx_person_usergroup_usergroup` (`usergroup_id`),
  CONSTRAINT `person_usergroup_assn_ibfk_1` FOREIGN KEY (`usergroup_id`) REFERENCES `usergroup` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_usergroup_assn_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `person_usergroup_assn`
--

/*!40000 ALTER TABLE `person_usergroup_assn` DISABLE KEYS */;
LOCK TABLES `person_usergroup_assn` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `person_usergroup_assn` ENABLE KEYS */;


--
-- Definition of table `product`
--

DROP TABLE IF EXISTS `product`;
CREATE TABLE  `product` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `manufacturer_id` mediumint(8) unsigned default '1',
  `supplier_id` mediumint(8) unsigned default '1',
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `name` varchar(128) NOT NULL,
  `model` varchar(128) NOT NULL,
  `short_description` varchar(128) default NULL,
  `long_description` text,
  `msrp` decimal(12,2) unsigned default NULL,
  `wholesale_price` decimal(12,2) unsigned default NULL,
  `retail_price` decimal(12,2) unsigned default NULL,
  `cost` decimal(12,2) unsigned default NULL,
  `weight` float(10,6) unsigned default NULL,
  `height` float(10,6) unsigned default NULL,
  `width` float(10,6) unsigned default NULL,
  `depth` float(10,6) unsigned default NULL,
  `is_virtual` tinyint(1) NOT NULL default '0',
  `type_id` tinyint(3) unsigned NOT NULL default '1',
  `status_id` tinyint(3) unsigned NOT NULL default '1',
  `view_count` bigint(20) unsigned default NULL,
  `user_permissions_id` tinyint(3) unsigned NOT NULL default '2',
  `public_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  `group_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `model` (`model`),
  KEY `idx_product_retail` (`retail_price`),
  KEY `idx_product_status` (`status_id`),
  KEY `manufacturer_id` (`manufacturer_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `idx_public_permissions` (`public_permissions_id`),
  KEY `idx_user_permissions` (`user_permissions_id`),
  KEY `idx_group_permissions` (`group_permissions_id`),
  KEY `idx_product_type` (`type_id`),
  CONSTRAINT `product_ibfk_1` FOREIGN KEY (`manufacturer_id`) REFERENCES `person` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `person` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `product`
--

/*!40000 ALTER TABLE `product` DISABLE KEYS */;
LOCK TABLES `product` WRITE;
INSERT INTO `product` VALUES  (1,NULL,NULL,'2008-07-01 14:52:50','','fooo',NULL,'',NULL,NULL,'132.50',NULL,2.619002,7.485000,6.998000,0.155000,1,4,1,NULL,2,1,1),
 (2,NULL,NULL,'2008-07-01 16:38:21','TEST 2','whatever model2',NULL,'',NULL,NULL,'32.50',NULL,0.610000,3.050000,4.000000,0.155000,1,2,1,NULL,2,1,1),
 (3,NULL,NULL,'2008-07-02 06:50:54','very nice product','An Amazing Model','','',NULL,NULL,'0.00',NULL,0.000000,0.000000,0.000000,0.155000,1,3,1,NULL,2,1,1),
 (7,NULL,NULL,'2008-07-13 18:30:31','TEST 2','whatever model',NULL,'',NULL,NULL,'0.00',NULL,0.000000,0.000000,0.000000,0.155000,1,5,1,NULL,4,1,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `product` ENABLE KEYS */;


--
-- Definition of table `product_category`
--

DROP TABLE IF EXISTS `product_category`;
CREATE TABLE  `product_category` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  `title` varchar(128) default NULL,
  `description` varchar(256) default NULL,
  `image_uri` varchar(256) default NULL,
  `parent_product_category_id` mediumint(8) unsigned default NULL,
  `public_permissions_id` tinyint(3) unsigned NOT NULL default '2',
  `user_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  `group_permissions_id` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_product_category_name` (`name`),
  KEY `idx_product_category_parent` (`parent_product_category_id`),
  KEY `idx_product_category_public_perms` (`public_permissions_id`),
  KEY `idx_product_category_user_perms` (`user_permissions_id`),
  KEY `idx_product_category_group_perms` (`group_permissions_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `product_category`
--

/*!40000 ALTER TABLE `product_category` DISABLE KEYS */;
LOCK TABLES `product_category` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `product_category` ENABLE KEYS */;


--
-- Definition of table `product_image`
--

DROP TABLE IF EXISTS `product_image`;
CREATE TABLE  `product_image` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `product_id` mediumint(8) unsigned NOT NULL,
  `title` varchar(128) default NULL,
  `alt_tag` varchar(128) default NULL,
  `description` varchar(256) default NULL,
  `uri` varchar(256) default NULL,
  `x_size` smallint(5) unsigned default NULL,
  `y_size` smallint(5) unsigned default NULL,
  `size_type` tinyint(3) unsigned default '2',
  PRIMARY KEY  (`id`),
  KEY `idx_size_type` (`size_type`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_image_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `product_image`
--

/*!40000 ALTER TABLE `product_image` DISABLE KEYS */;
LOCK TABLES `product_image` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `product_image` ENABLE KEYS */;


--
-- Definition of table `product_product_category_assn`
--

DROP TABLE IF EXISTS `product_product_category_assn`;
CREATE TABLE  `product_product_category_assn` (
  `product_id` mediumint(8) unsigned NOT NULL,
  `product_category_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`product_category_id`,`product_id`),
  KEY `idx_product_product_category_product` (`product_id`),
  KEY `idx_product_product_category_product_category` (`product_category_id`),
  CONSTRAINT `product_product_category_assn_ibfk_1` FOREIGN KEY (`product_category_id`) REFERENCES `product_category` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_product_category_assn_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `product_product_category_assn`
--

/*!40000 ALTER TABLE `product_product_category_assn` DISABLE KEYS */;
LOCK TABLES `product_product_category_assn` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `product_product_category_assn` ENABLE KEYS */;


--
-- Definition of table `product_status_type`
--

DROP TABLE IF EXISTS `product_status_type`;
CREATE TABLE  `product_status_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_product_status_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `product_status_type`
--

/*!40000 ALTER TABLE `product_status_type` DISABLE KEYS */;
LOCK TABLES `product_status_type` WRITE;
INSERT INTO `product_status_type` VALUES  (1,'Active'),
 (2,'Onsale'),
 (5,'Other'),
 (4,'Retired'),
 (3,'Suspended');
UNLOCK TABLES;
/*!40000 ALTER TABLE `product_status_type` ENABLE KEYS */;


--
-- Definition of table `product_type`
--

DROP TABLE IF EXISTS `product_type`;
CREATE TABLE  `product_type` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_product_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `product_type`
--

/*!40000 ALTER TABLE `product_type` DISABLE KEYS */;
LOCK TABLES `product_type` WRITE;
INSERT INTO `product_type` VALUES  (5,'Assembly'),
 (1,'Design'),
 (6,'Download'),
 (4,'Internal'),
 (8,'Other'),
 (2,'Service'),
 (3,'Storefront'),
 (7,'Virtual');
UNLOCK TABLES;
/*!40000 ALTER TABLE `product_type` ENABLE KEYS */;


--
-- Definition of table `related_product_assn`
--

DROP TABLE IF EXISTS `related_product_assn`;
CREATE TABLE  `related_product_assn` (
  `product_id` mediumint(8) unsigned NOT NULL,
  `related_product_id` mediumint(8) unsigned NOT NULL,
  KEY `product_id` (`product_id`),
  KEY `related_product_id` (`related_product_id`),
  CONSTRAINT `related_product_assn_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `related_product_assn_ibfk_2` FOREIGN KEY (`related_product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `related_product_assn`
--

/*!40000 ALTER TABLE `related_product_assn` DISABLE KEYS */;
LOCK TABLES `related_product_assn` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `related_product_assn` ENABLE KEYS */;


--
-- Definition of table `shipping_method`
--

DROP TABLE IF EXISTS `shipping_method`;
CREATE TABLE  `shipping_method` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `title` varchar(128) default 'Shipping Method',
  `carrier` varchar(128) default NULL,
  `service_type` varchar(128) default NULL,
  `class_name` varchar(128) default NULL,
  `transit_time` varchar(16) default NULL,
  `description` text,
  `active` tinyint(1) default '0',
  `is_international` tinyint(1) default '0',
  `test_mode` tinyint(1) default '1',
  `sort_order` tinyint(3) unsigned default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `shipping_method`
--

/*!40000 ALTER TABLE `shipping_method` DISABLE KEYS */;
LOCK TABLES `shipping_method` WRITE;
INSERT INTO `shipping_method` VALUES  (1,'Local Pickup','PickUp','Counter','NoClass','0','Pick up at the store.',1,0,1,2),
 (2,'United States Postal Service','USPS','FIRST CLASS','USPS','2 - 4','Basic ground delivery',0,0,1,0),
 (3,'United States Postal Service','USPS','PRIORITY','USPS','2 - 3','Priority Mail',0,0,1,0),
 (4,'United States Postal Service','USPS','EXPRESS','USPS','1 - 2','Express Mail',0,0,1,0),
 (5,'United States Postal Service','USPS','Global Express Guaranteed','USPS','3 - 5','Premium international service',0,1,1,0),
 (6,'United States Postal Service','USPS','Express Mail International','USPS','3 - 5','Expedited International service',0,1,1,0),
 (7,'United States Postal Service','USPS','Priority Mail International','USPS','4 - 9','Reliable, economical accelerated airmail',0,1,1,0),
 (8,'United States Postal Service','USPS','First Class Mail International','USPS','5 - 28','Generic, no frills, low cost service',0,1,1,0),
 (9,'Federal Express','FDXG','FEDEX_GROUND','Fedex','4 - 5','Basic ground service',0,1,1,0),
 (10,'Federal Express','FDXE','FEDEX_2_DAY','Fedex','2','2 day service',0,1,1,0);
INSERT INTO `shipping_method` VALUES  (11,'Federal Express','FDXE','PRIORITY_OVERNIGHT','Fedex','1','Standard Overnight service',0,1,1,0),
 (12,'Federal Express','FDXE','INTERNATIONAL_ECONOMY','Fedex','3 - 5','Economy International service',0,1,1,0),
 (13,'Federal Express','FDXE','INTERNATIONAL_PRIORITY','Fedex',' 2 - 3 ','Priority International service',0,1,1,0),
 (14,'Federal Express','FDXE','INTERNATIONAL_FIRST','Fedex','2','First class International service',0,1,1,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `shipping_method` ENABLE KEYS */;


--
-- Definition of table `shipping_rate`
--

DROP TABLE IF EXISTS `shipping_rate`;
CREATE TABLE  `shipping_rate` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `rate` decimal(2,2) NOT NULL,
  `zone_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_shipping_zone` (`zone_id`),
  KEY `idx_shipping_rate` (`id`),
  CONSTRAINT `shipping_rate_ibfk_1` FOREIGN KEY (`zone_id`) REFERENCES `zone_type` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `shipping_rate`
--

/*!40000 ALTER TABLE `shipping_rate` DISABLE KEYS */;
LOCK TABLES `shipping_rate` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `shipping_rate` ENABLE KEYS */;


--
-- Definition of table `shopping_cart`
--

DROP TABLE IF EXISTS `shopping_cart`;
CREATE TABLE  `shopping_cart` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `last_modification` timestamp NOT NULL default '0000-00-00 00:00:00',
  `account_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `account_id` (`account_id`),
  CONSTRAINT `shopping_cart_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `shopping_cart`
--

/*!40000 ALTER TABLE `shopping_cart` DISABLE KEYS */;
LOCK TABLES `shopping_cart` WRITE;
INSERT INTO `shopping_cart` VALUES  (4,'2008-09-26 10:55:28','0000-00-00 00:00:00',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `shopping_cart` ENABLE KEYS */;


--
-- Definition of table `shopping_cart_item`
--

DROP TABLE IF EXISTS `shopping_cart_item`;
CREATE TABLE  `shopping_cart_item` (
  `shopping_cart_id` bigint(20) unsigned NOT NULL,
  `product_id` mediumint(8) unsigned NOT NULL,
  `quantity` mediumint(8) unsigned NOT NULL default '1',
  PRIMARY KEY  (`product_id`,`shopping_cart_id`),
  KEY `idx_shopping_cart_product_shopping_cart` (`shopping_cart_id`),
  KEY `idx_shopping_cart_product_product` (`product_id`),
  CONSTRAINT `shopping_cart_item_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shopping_cart_item_ibfk_2` FOREIGN KEY (`shopping_cart_id`) REFERENCES `shopping_cart` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `shopping_cart_item`
--

/*!40000 ALTER TABLE `shopping_cart_item` DISABLE KEYS */;
LOCK TABLES `shopping_cart_item` WRITE;
INSERT INTO `shopping_cart_item` VALUES  (4,2,4);
UNLOCK TABLES;
/*!40000 ALTER TABLE `shopping_cart_item` ENABLE KEYS */;


--
-- Definition of table `style_sheet`
--

DROP TABLE IF EXISTS `style_sheet`;
CREATE TABLE  `style_sheet` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  `description` varchar(256) default NULL,
  `filename` varchar(128) default NULL,
  `type` enum('HTML','XML') default 'HTML',
  PRIMARY KEY  (`id`),
  KEY `idx_style_sheet_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `style_sheet`
--

/*!40000 ALTER TABLE `style_sheet` DISABLE KEYS */;
LOCK TABLES `style_sheet` WRITE;
INSERT INTO `style_sheet` VALUES  (1,'Quasi CMS default stylesheet','Basic green single left column w/logo','quasi.css',''),
 (2,'Dashboard style sheet','Olive green, styled menu buttons','style.css','HTML');
UNLOCK TABLES;
/*!40000 ALTER TABLE `style_sheet` ENABLE KEYS */;


--
-- Definition of table `tax_rate`
--

DROP TABLE IF EXISTS `tax_rate`;
CREATE TABLE  `tax_rate` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `zone_id` smallint(5) unsigned NOT NULL,
  `rate` decimal(4,4) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_tax_zone` (`zone_id`),
  KEY `idx_tax_rate` (`id`),
  CONSTRAINT `tax_rate_ibfk_1` FOREIGN KEY (`zone_id`) REFERENCES `zone_type` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tax_rate`
--

/*!40000 ALTER TABLE `tax_rate` DISABLE KEYS */;
LOCK TABLES `tax_rate` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tax_rate` ENABLE KEYS */;


--
-- Definition of table `tracking_number`
--

DROP TABLE IF EXISTS `tracking_number`;
CREATE TABLE  `tracking_number` (
  `order_id` bigint(20) unsigned NOT NULL,
  `number` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`order_id`,`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tracking_number`
--

/*!40000 ALTER TABLE `tracking_number` DISABLE KEYS */;
LOCK TABLES `tracking_number` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tracking_number` ENABLE KEYS */;


--
-- Definition of table `usergroup`
--

DROP TABLE IF EXISTS `usergroup`;
CREATE TABLE  `usergroup` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_usergroup` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `usergroup`
--

/*!40000 ALTER TABLE `usergroup` DISABLE KEYS */;
LOCK TABLES `usergroup` WRITE;
INSERT INTO `usergroup` VALUES  (5,'Administrators'),
 (3,'Customers'),
 (4,'Designers'),
 (9,'Editors'),
 (6,'Engineers'),
 (1,'Everyone'),
 (7,'Manufacturers'),
 (2,'Members'),
 (8,'Suppliers');
UNLOCK TABLES;
/*!40000 ALTER TABLE `usergroup` ENABLE KEYS */;


--
-- Definition of table `zone_type`
--

DROP TABLE IF EXISTS `zone_type`;
CREATE TABLE  `zone_type` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(128) NOT NULL,
  `country_id` smallint(5) unsigned NOT NULL default '255',
  `code` varchar(32) NOT NULL default 'N/A',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_zone_type_name` (`name`),
  KEY `idx_zone_code` (`code`),
  KEY `country_id` (`country_id`),
  CONSTRAINT `zone_type_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `country_type` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `zone_type`
--

/*!40000 ALTER TABLE `zone_type` DISABLE KEYS */;
LOCK TABLES `zone_type` WRITE;
INSERT INTO `zone_type` VALUES  (1,'Alabama',223,'AL'),
 (2,'Alaska',223,'AK'),
 (3,'American Samoa',223,'AS'),
 (4,'Arizona',223,'AZ'),
 (5,'Arkansas',223,'AR'),
 (6,'Armed Forces Africa',223,'AF'),
 (7,'Armed Forces Americas',223,'AA'),
 (8,'Armed Forces Canada',223,'AC'),
 (9,'Armed Forces Europe',223,'AE'),
 (10,'Armed Forces Middle East',223,'AM'),
 (11,'Armed Forces Pacific',223,'AP'),
 (12,'California',223,'CA'),
 (13,'Colorado',223,'CO'),
 (14,'Connecticut',223,'CT'),
 (15,'Delaware',223,'DE'),
 (16,'District of Columbia',223,'DC'),
 (17,'Federated States Of Micronesia',223,'FM'),
 (18,'Florida',223,'FL'),
 (19,'Georgia',223,'GA'),
 (20,'Guam',223,'GU'),
 (21,'Hawaii',223,'HI'),
 (22,'Idaho',223,'ID'),
 (23,'Illinois',223,'IL'),
 (24,'Indiana',223,'IN'),
 (25,'Iowa',223,'IA'),
 (26,'Kansas',223,'KS'),
 (27,'Kentucky',223,'KY'),
 (28,'Louisiana',223,'LA'),
 (29,'Maine',223,'ME'),
 (30,'Marshall Islands',223,'MH'),
 (31,'Maryland',223,'MD'),
 (32,'Massachusetts',223,'MA'),
 (33,'Michigan',223,'MI');
INSERT INTO `zone_type` VALUES  (34,'Minnesota',223,'MN'),
 (35,'Mississippi',223,'MS'),
 (36,'Missouri',223,'MO'),
 (37,'Montana',223,'MT'),
 (38,'Nebraska',223,'NE'),
 (39,'Nevada',223,'NV'),
 (40,'New Hampshire',223,'NH'),
 (41,'New Jersey',223,'NJ'),
 (42,'New Mexico',223,'NM'),
 (43,'New York',223,'NY'),
 (44,'North Carolina',223,'NC'),
 (45,'North Dakota',223,'ND'),
 (46,'Northern Mariana Islands',223,'MP'),
 (47,'Ohio',223,'OH'),
 (48,'Oklahoma',223,'OK'),
 (49,'Oregon',223,'OR'),
 (50,'Palau',163,'PW'),
 (51,'Pennsylvania',223,'PA'),
 (52,'Puerto Rico',223,'PR'),
 (53,'Rhode Island',223,'RI'),
 (54,'South Carolina',223,'SC'),
 (55,'South Dakota',223,'SD'),
 (56,'Tennessee',223,'TN'),
 (57,'Texas',223,'TX'),
 (58,'Utah',223,'UT'),
 (59,'Vermont',223,'VT'),
 (60,'Virgin Islands',223,'VI'),
 (61,'Virginia',223,'VA'),
 (62,'Washington',223,'WA'),
 (63,'West Virginia',223,'WV'),
 (64,'Wisconsin',223,'WI'),
 (65,'Wyoming',223,'WY'),
 (66,'Alberta',38,'AB'),
 (67,'British Columbia',38,'BC'),
 (68,'Manitoba',38,'MB');
INSERT INTO `zone_type` VALUES  (69,'Newfoundland',38,'NL'),
 (70,'New Brunswick',38,'NB'),
 (71,'Nova Scotia',38,'NS'),
 (72,'Northwest Territories',38,'NT'),
 (73,'Nunavut',38,'NU'),
 (74,'Ontario',38,'ON'),
 (75,'Prince Edward Island',38,'PE'),
 (76,'Quebec',38,'QC'),
 (77,'Saskatchewan',38,'SK'),
 (78,'Yukon Territory',38,'YT'),
 (79,'Niedersachsen',81,'NDS'),
 (80,'Baden WÃ¼rtemberg',81,'BAW'),
 (81,'Bayern',81,'BAY'),
 (82,'Berlin',81,'BER'),
 (83,'Brandenburg',81,'BRG'),
 (84,'Bremen',81,'BRE'),
 (85,'Hamburg',81,'HAM'),
 (86,'Hessen',81,'HES'),
 (87,'Mecklenburg-Vorpommern',81,'MEC'),
 (88,'Nordrhein-Westfalen',81,'NRW'),
 (89,'Rheinland-Pfalz',81,'RHE'),
 (90,'Saarland',81,'SAR'),
 (91,'Sachsen',81,'SAS'),
 (92,'Sachsen-Anhalt',81,'SAC'),
 (93,'Schleswig-Holstein',81,'SCN'),
 (94,'Thringen',81,'THE'),
 (95,'Wien',14,'WI'),
 (96,'Niedersterreich',14,'NO'),
 (97,'Obersterreich',14,'OO'),
 (98,'Salzburg',14,'SB'),
 (99,'KÃ¤rnten',14,'KN'),
 (100,'Steiermark',14,'ST'),
 (101,'Tirol',14,'TI');
INSERT INTO `zone_type` VALUES  (102,'Burgenland',14,'BL'),
 (103,'Voralberg',14,'VB'),
 (104,'Aargau',204,'AG'),
 (105,'Appenzell Innerrhoden',204,'AI'),
 (106,'Appenzell Ausserrhoden',204,'AR'),
 (107,'Bern',204,'BE'),
 (108,'Basel-Landschaft',204,'BL'),
 (109,'Basel-Stadt',204,'BS'),
 (110,'Freiburg',204,'FR'),
 (111,'Genf',204,'GE'),
 (112,'Glarus',204,'GL'),
 (113,'Graubnden',204,'JU'),
 (114,'Jura',204,'JU'),
 (115,'Luzern',204,'LU'),
 (116,'Neuenburg',204,'NE'),
 (117,'Nidwalden',204,'NW'),
 (118,'Obwalden',204,'OW'),
 (119,'St. Gallen',204,'SG'),
 (120,'Schaffhausen',204,'SH'),
 (121,'Solothurn',204,'SO'),
 (122,'Schwyz',204,'SZ'),
 (123,'Thurgau',204,'TG'),
 (124,'Tessin',204,'TI'),
 (125,'Uri',204,'UR'),
 (126,'Waadt',204,'VD'),
 (127,'Wallis',204,'VS'),
 (128,'Zug',204,'ZG'),
 (129,'Zrich',204,'ZH'),
 (130,'A Corua',195,'A Corua'),
 (131,'Alava',195,'Alava'),
 (132,'Albacete',195,'Albacete'),
 (133,'Alicante',195,'Alicante'),
 (134,'Almeria',195,'Almeria'),
 (135,'Asturias',195,'Asturias'),
 (136,'Avila',195,'Avila');
INSERT INTO `zone_type` VALUES  (137,'Badajoz',195,'Badajoz'),
 (138,'Baleares',195,'Baleares'),
 (139,'Barcelona',195,'Barcelona'),
 (140,'Burgos',195,'Burgos'),
 (141,'Caceres',195,'Caceres'),
 (142,'Cadiz',195,'Cadiz'),
 (143,'Cantabria',195,'Cantabria'),
 (144,'Castellon',195,'Castellon'),
 (145,'Ceuta',195,'Ceuta'),
 (146,'Ciudad Real',195,'Ciudad Real'),
 (147,'Cordoba',195,'Cordoba'),
 (148,'Cuenca',195,'Cuenca'),
 (149,'Girona',195,'Girona'),
 (150,'Granada',195,'Granada'),
 (151,'Guadalajara',195,'Guadalajara'),
 (152,'Guipuzcoa',195,'Guipuzcoa'),
 (153,'Huelva',195,'Huelva'),
 (154,'Huesca',195,'Huesca'),
 (155,'Jaen',195,'Jaen'),
 (156,'La Rioja',195,'La Rioja'),
 (157,'Las Palmas',195,'Las Palmas'),
 (158,'Leon',195,'Leon'),
 (159,'Lleida',195,'Lleida'),
 (160,'Lugo',195,'Lugo'),
 (161,'Madrid',195,'Madrid'),
 (162,'Malaga',195,'Malaga'),
 (163,'Melilla',195,'Melilla'),
 (164,'Murcia',195,'Murcia'),
 (165,'Navarra',195,'Navarra'),
 (166,'Ourense',195,'Ourense'),
 (167,'Palencia',195,'Palencia');
INSERT INTO `zone_type` VALUES  (168,'Pontevedra',195,'Pontevedra'),
 (169,'Salamanca',195,'Salamanca'),
 (170,'Santa Cruz de Tenerife',195,'Santa Cruz de Tenerife'),
 (171,'Segovia',195,'Segovia'),
 (172,'Sevilla',195,'Sevilla'),
 (173,'Soria',195,'Soria'),
 (174,'Tarragona',195,'Tarragona'),
 (175,'Teruel',195,'Teruel'),
 (176,'Toledo',195,'Toledo'),
 (177,'Valencia',195,'Valencia'),
 (178,'Valladolid',195,'Valladolid'),
 (179,'Vizcaya',195,'Vizcaya'),
 (180,'Zamora',195,'Zamora'),
 (181,'Zaragoza',195,'Zaragoza'),
 (182,'Australian Capital Territory',13,'ACT'),
 (183,'New South Wales',13,'NSW'),
 (184,'Northern Territory',13,'NT'),
 (185,'Queensland',13,'QLD'),
 (186,'South Australia',13,'SA'),
 (187,'Tasmania',13,'TAS'),
 (188,'Victoria',13,'VIC'),
 (189,'Western Australia',13,'WA'),
 (255,'No Zone',255,'--');
UNLOCK TABLES;
/*!40000 ALTER TABLE `zone_type` ENABLE KEYS */;




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
