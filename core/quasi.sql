USE quasicms;

CREATE TABLE `usergroup` (
    `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(128),
    CONSTRAINT pk_usergroup PRIMARY KEY (`id`),
    UNIQUE KEY uk_usergroup (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO usergroup (name) VALUES ('Everyone');
INSERT INTO usergroup (name) VALUES ('Members');
INSERT INTO usergroup (name) VALUES ('Customers');
INSERT INTO usergroup (name) VALUES ('Designers');
INSERT INTO usergroup (name) VALUES ('Administrators');
INSERT INTO usergroup (name) VALUES ('Engineers');
INSERT INTO usergroup (name) VALUES ('Manufacturers');
INSERT INTO usergroup (name) VALUES ('Suppliers');
INSERT INTO usergroup (name) VALUES ('Editors');

CREATE TABLE `permission_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64),
    CONSTRAINT pk_permission_type PRIMARY KEY (`id`),
    UNIQUE KEY uk_permssion_type (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;


INSERT INTO permission_type (name) VALUES ('None');
INSERT INTO permission_type (name) VALUES ('View');
INSERT INTO permission_type (name) VALUES ('Modify');
INSERT INTO permission_type (name) VALUES ('Delete');

/***********************************************************************
    Notes on the person table and its associate dependant tables
    
    1. INSERT INTO person(is_virtual) VALUES(false);
    2. INSERT INTO account(username,password,now(),true,1,1,person.id)
    3. INSERT INTO address, using person.id FK
                
    So, we create a row in the person table using data in other tables.
   This means we need to insert the person first, get the insert id, then insert the others
   with the id and then update the person row with the ids from the others to maintain
   referential integrity.
        
     Case 2 illustrates the purpose of the design - we want to allow members to send gifts to friends
    periodically. So, they can create a new "virtual" person with name and address and store it
    making it available as an selection when they choose a shipping address during a checkout.
    This way, we can have other names and addresses tied to the Person instead of the Account
    thus keeping the one to one relationship of person - account while allowing multiple names
    and addresses within normal forms (without duplication). Any additional name and addresses
    must be other than type primary and type primary must exist for a Person in order for them
    to make a purchase.
    

   Use case - Member Registration:
    * Chooses username and login, optional address info (this would define them as a customer) ..
        1. create person object with defaults
        2. create account object using person.id - an account is tied to a single person
        3. if address info, create address object FK person_id gets person.id, type is set to primary
            else - error.
        4. repeat for phone, email, shipping, billing - all optional
        
    Use case - Member addition (ie, adds a gift address/person ):
        1. User fills in fields - collect and perform inserts as above
        2. Set address.person_id to new person.id
        3. Set type to billing, friend, etc - mandatory, if not, then this defaults to an address change

    Use case - Administrator addition:
        1. If no account exists, steps 1 - 2 of  Use Case 1.
        2. Else, (or after 1.) proceed as in Use Case 2  assigning FK id columns to an existing
        or newly created row in person (eg. address.person_id = person.id)
        
******************************************************************************/

CREATE TABLE `person` (
  `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_prefix` VARCHAR(32) DEFAULT '', -- 'eg. Mr., Ms ..',
  `first_name` VARCHAR(256) NOT NULL,
  `middle_name` VARCHAR(128) DEFAULT '',
  `last_name` VARCHAR(256) NOT NULL,
  `name_suffix` VARCHAR(32) DEFAULT '', -- 'eg. PhD. MD, etc ..',
  `nick_name` VARCHAR(128) DEFAULT 'Anonymous',
  `email_address` VARCHAR(128) NOT NULL,
  `phone_number` VARCHAR(32) DEFAULT 'N/A',
  `avatar_uri` VARCHAR(256) , -- optional avatar image for person
  `company_name` VARCHAR(256) DEFAULT '',
  `owner_person_id` MEDIUMINT UNSIGNED,
  `is_virtual` BOOL DEFAULT FALSE, -- true for persons in members' addressbook, suppliers, manufacturers ...
  CONSTRAINT pk_person PRIMARY KEY (`id`),
  INDEX idx_person_firstname (`first_name`),
  INDEX idx_person_last_name (`last_name`),
  INDEX idx_person_email_address (`email_address`),
--  INDEX idx_person_address (`address_id`),
  FOREIGN KEY (`owner_person_id`) REFERENCES person(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `account_status_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64),
    CONSTRAINT pk_account_status_type PRIMARY KEY (`id`),
    UNIQUE KEY uk_account_status_type (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;


INSERT INTO account_status_type (name) VALUES ('Active');
INSERT INTO account_status_type (name) VALUES ('Cancelled');
INSERT INTO account_status_type (name) VALUES ('Suspended');


CREATE TABLE `account_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64),
    CONSTRAINT pk_account_type PRIMARY KEY (`id`),
    UNIQUE KEY uk_account_type (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO account_type (name) VALUES ('Member');
INSERT INTO account_type (name) VALUES ('Customer');
INSERT INTO account_type (name) VALUES ('Employee');
INSERT INTO account_type (name) VALUES ('Administrator');
INSERT INTO account_type (name) VALUES ('Supplier');
INSERT INTO account_type (name) VALUES ('Manufacturer');


CREATE TABLE `account` (
  `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `registration_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `username` VARCHAR(128) NOT NULL,
  `password` VARCHAR(128) NOT NULL,
  `notes` TEXT,
  `last_login` TIMESTAMP,
  `login_count` INT UNSIGNED DEFAULT 0,
  `online` BOOL NOT NULL DEFAULT FALSE,
  `onetime_password` BOOL NOT NULL DEFAULT FALSE,
  `valid_password` BOOL NOT NULL DEFAULT TRUE,
  `type_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `status_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `person_id` MEDIUMINT UNSIGNED NOT NULL,
  CONSTRAINT pk_account PRIMARY KEY (`id`),
  UNIQUE KEY idx_account_username (`username`),
  UNIQUE KEY idx_account_person (`person_id`),
  INDEX idx_account_type (`type_id`),
  INDEX idx_account_status (`status_id`),
  FOREIGN KEY (`person_id`) REFERENCES person(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci
COMMENT = 'Corresponds to the normal users | customers | members table';


CREATE TABLE `name_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64),
    CONSTRAINT pk_name_type PRIMARY KEY (`id`),
    UNIQUE KEY idx_name_type(name)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO name_type (name) VALUES ('Primary');
INSERT INTO name_type (name) VALUES ('Shipping');
INSERT INTO name_type (name) VALUES ('Billing');
INSERT INTO name_type (name) VALUES ('Historical'); -- people sometimes change names ..
INSERT INTO name_type (name) VALUES ('Alias');
INSERT INTO name_type (name) VALUES ('Friend');

CREATE TABLE `country_type` (
  `id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `iso_code_2` CHAR(2) NOT NULL DEFAULT '',
  `iso_code_3` CHAR(3) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY  `idx_country_name` (`name`),
  INDEX `idx_iso_2` (`iso_code_2`),
  INDEX `idx_iso_3` (`iso_code_3`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `zone_type` (
  `id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL,
  `country_id` SMALLINT UNSIGNED  NOT NULL DEFAULT 255,
  `code` VARCHAR(32) NOT NULL DEFAULT 'N/A',
  PRIMARY KEY  (`id`),
  UNIQUE KEY uk_zone_type_name (`name`),
  INDEX idx_zone_code (`code`),
  FOREIGN KEY (`country_id`) REFERENCES country_type(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;


CREATE TABLE `address_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64),
    CONSTRAINT pk_address_type PRIMARY KEY (`id`),
    UNIQUE KEY idx_address_type(name)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO address_type (name) VALUES ('Primary');
INSERT INTO address_type (name) VALUES ('Shipping');
INSERT INTO address_type (name) VALUES ('Billing');
INSERT INTO address_type (name) VALUES ('Company');
INSERT INTO address_type (name) VALUES ('Friend');
INSERT INTO address_type (name) VALUES ('Manufacturer');
INSERT INTO address_type (name) VALUES ('Supplier');
INSERT INTO address_type (name) VALUES ('Historical');

/*historical includes typo fixes and moved - for order lookups, eventually dated should move to address_history
*/

CREATE TABLE `address` (
  `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(256) DEFAULT 'My Address', -- for human reference, eg. "Dad" or "Uncle Ed"
  `person_id` MEDIUMINT UNSIGNED NOT NULL,
  `street_1` VARCHAR(256) ,
  `street_2` VARCHAR(256) ,
  `suburb` VARCHAR(256) ,
  `city` VARCHAR(256) ,
  `county` VARCHAR(256) , -- text version of zone/district 
  `zone_id` SMALLINT UNSIGNED NOT NULL DEFAULT 13,  -- 'state, province or district
  `country_id` SMALLINT UNSIGNED NOT NULL DEFAULT 223,
  `postal_code` VARCHAR(32), -- get a table for this 
  `is_current` BOOL NOT NULL DEFAULT TRUE,
  `type_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modification_date` TIMESTAMP,
  CONSTRAINT pk_address PRIMARY KEY (`id`),
  INDEX idx_address_person (`id`),
  INDEX idx_address_type (`type_id`),
  INDEX idx_address_zone (`zone_id`),
  INDEX idx_address_country (`country_id`),
  FOREIGN KEY (`person_id`) REFERENCES person(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `product_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64),
    CONSTRAINT pk_product_type PRIMARY KEY (`id`),
    UNIQUE KEY idx_product_type(name)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO product_type (name) VALUES ('Service');
INSERT INTO product_type (name) VALUES ('Storefront');
INSERT INTO product_type (name) VALUES ('Internal');
INSERT INTO product_type (name) VALUES ('Assembly');
INSERT INTO product_type (name) VALUES ('Download');
INSERT INTO product_type (name) VALUES ('Virtual');
INSERT INTO product_type (name) VALUES ('Other');

CREATE TABLE `product_status_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64),
    CONSTRAINT pk_product_status_type PRIMARY KEY (`id`),
    UNIQUE KEY idx_product_status_type(name)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO product_status_type (name) VALUES ('Restricted');
INSERT INTO product_status_type (name) VALUES ('Active');
INSERT INTO product_status_type (name) VALUES ('Disabled');
INSERT INTO product_status_type (name) VALUES ('Retired');

CREATE TABLE `product` (
  `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `manufacturer_id` MEDIUMINT UNSIGNED DEFAULT 1, -- in house
  `supplier_id` MEDIUMINT UNSIGNED DEFAULT 1,
  `creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` VARCHAR(128) NOT NULL,
  `model` VARCHAR(128) NOT NULL,
  `short_description` VARCHAR(128) ,
  `long_description` TEXT, 
  `msrp` DECIMAL(12,2) UNSIGNED,
  `wholesale_price` DECIMAL(12,2) UNSIGNED,
  `retail_price` DECIMAL(12,2) UNSIGNED,
  `cost` DECIMAL(12,2) UNSIGNED,
  `weight` FLOAT(10,6) UNSIGNED, -- ounces ..
  `height` FLOAT(10,6) UNSIGNED, -- inches, X ..
  `width` FLOAT(10,6) UNSIGNED, -- inches, Y
  `depth` FLOAT(10,6) UNSIGNED, -- inches, Z
  `is_virtual` BOOL NOT NULL DEFAULT FALSE,
  `type_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `status_id` TINYINT UNSIGNED NOT NULL DEFAULT 1, -- active, disabled ..
  `view_count` BIGINT UNSIGNED,
  `public_permissions_id` TINYINT UNSIGNED NOT NULL DEFAULT 1, -- ie. none ..
  `user_permissions_id` TINYINT UNSIGNED NOT NULL DEFAULT 2,
  `group_permissions_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  CONSTRAINT pk_product PRIMARY KEY (`id`),
  UNIQUE KEY (`model`),
  INDEX idx_product_retail (`retail_price`),
  INDEX idx_product_type (`type_id`),
  INDEX idx_product_status (`status_id`),
  INDEX idx_product_public_perms (`public_permissions_id`),
  INDEX idx_product_user_perms (`user_permissions_id`),
  INDEX idx_product_group_perms (`group_permissions_id`),
  FOREIGN KEY (`manufacturer_id`) REFERENCES person(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`supplier_id`) REFERENCES person(`id`) ON DELETE SET NULL
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `image_size_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64),
    CONSTRAINT pk_image_size_type PRIMARY KEY (`id`),
    UNIQUE KEY idx_image_size_type(name)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO image_size_type (name) VALUES ('Icon');
INSERT INTO image_size_type (name) VALUES ('Thumb');
INSERT INTO image_size_type (name) VALUES ('Small');
INSERT INTO image_size_type (name) VALUES ('Medium');
INSERT INTO image_size_type (name) VALUES ('Large');
INSERT INTO image_size_type (name) VALUES ('FullScreen');
INSERT INTO image_size_type (name) VALUES ('Intergalactic');

CREATE TABLE `product_image` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` MEDIUMINT UNSIGNED NOT NULL,
  `title` VARCHAR(128),
  `alt_tag` VARCHAR(128),
  `description` VARCHAR(256) ,
  `uri` VARCHAR(256),
  `x_size` SMALLINT UNSIGNED, -- pixels, X ..
  `y_size` SMALLINT UNSIGNED, -- pixels, Y
  `size_type` TINYINT UNSIGNED DEFAULT 2,
  CONSTRAINT pk_product_image PRIMARY KEY (`id`),
  INDEX idx_size_type(`size_type`),
  FOREIGN KEY (`product_id`) REFERENCES product(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `order_status_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64),
    CONSTRAINT pk_order_status_type PRIMARY KEY (`id`),
    UNIQUE KEY idx_order_status_type(name)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO order_status_type (name) VALUES ('Shopping'); -- ie. in the cart ..
INSERT INTO order_status_type (name) VALUES ('Pending');
INSERT INTO order_status_type (name) VALUES ('Paid');
INSERT INTO order_status_type (name) VALUES ('Processing');
INSERT INTO order_status_type (name) VALUES ('Packaged');
INSERT INTO order_status_type (name) VALUES ('Shipped');
INSERT INTO order_status_type (name) VALUES ('Cancelled');
INSERT INTO order_status_type (name) VALUES ('Returned'); -- Note: may need to move to order_item ..
INSERT INTO order_status_type (name) VALUES ('Problem');

CREATE TABLE `tax_rate` (
    `id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `zone_id` SMALLINT UNSIGNED NOT NULL,  -- 'state, province or district
    `rate` DECIMAL(4,4) UNSIGNED NOT NULL,
    CONSTRAINT pk_tax_rate PRIMARY KEY (`id`),
    UNIQUE INDEX idx_tax_zone (`zone_id`),
    INDEX idx_tax_rate(`rate`),
    FOREIGN KEY (`zone_id`) REFERENCES zone_type(`id`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

-- INSERT INTO tax_rate ( `rate` ) VALUES (19.6);
-- INSERT INTO tax_rate ( `rate` ) VALUES (5.5);


CREATE TABLE `shipping_rate` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `rate` DECIMAL(2,2) NOT NULL,
    `zone_id` SMALLINT UNSIGNED NOT NULL,  -- 'state, province or district
    CONSTRAINT pk_shipping_rate PRIMARY KEY (`id`),
    UNIQUE INDEX idx_shipping_zone (`zone_id`),
    INDEX idx_shipping_rate(`id`),
    FOREIGN KEY (`zone_id`) REFERENCES zone_type(`id`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;
-- INSERT INTO shipping_rate ( `rate` ) VALUES (19.6);
-- INSERT INTO shipping_rate ( `rate` ) VALUES (5.5);

CREATE TABLE `shipping_method` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(128) DEFAULT 'Shipping Method', -- for display
    `carrier` VARCHAR(128),
    `service_type` VARCHAR(128),
    `class_name` VARCHAR(128),
    `transit_time` VARCHAR(16), -- in days, eg. 2 - 3
    `description` TEXT,
    `image_filename` VARCHAR(128),
    `active` BOOL DEFAULT FALSE,
    `is_international` BOOL DEFAULT FALSE,
    `test_mode` BOOL DEFAULT TRUE,
    `sort_order` TINYINT UNSIGNED DEFAULT 0, -- ie, first or top
    CONSTRAINT pk_shipping_method PRIMARY KEY (`id`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO shipping_method (title, carrier, service_type, class_name, description, transit_time, is_international)
    VALUES ('Local Pickup', 'PickUp', 'Counter', 'NoClass', 'Pick up at the store.', '0',false);
INSERT INTO shipping_method (title, carrier, service_type, class_name, description, transit_time, is_international)
    VALUES ('United States Postal Service', 'USPS','FIRST CLASS', 'USPS','Basic ground delivery','2 - 4',false);
INSERT INTO shipping_method (title, carrier, service_type, class_name, description, transit_time, is_international)
    VALUES ('United States Postal Service', 'USPS','PRIORITY', 'USPS','Priority Mail','2 - 3',false);
INSERT INTO shipping_method (title, carrier, service_type, class_name, description, transit_time, is_international)
    VALUES ('United States Postal Service', 'USPS','EXPRESS', 'USPS','Express Mail','1 - 2',false);
INSERT INTO shipping_method (title, carrier, service_type, class_name, description, transit_time, is_international)
    VALUES ('United States Postal Service', 'USPS','Global Express Guaranteed', 'USPSIntlRateCalculator','Premium international service','3 - 5',true);
INSERT INTO shipping_method (title, carrier, service_type, class_name, description, transit_time, is_international)
    VALUES ('United States Postal Service', 'USPS','Express Mail International', 'USPSIntlRateCalculator','Expedited International service','3 - 5',true);
INSERT INTO shipping_method (title, carrier, service_type, class_name, description, transit_time, is_international)
    VALUES ('United States Postal Service', 'USPS','Priority Mail International', 'USPSIntlRateCalculator','Reliable, economical accelerated airmail','4 - 9',true);
INSERT INTO shipping_method (title, carrier, service_type, class_name, description, transit_time, is_international)
    VALUES ('United States Postal Service', 'USPS','First Class Mail International', 'USPSIntlRateCalculator','Generic, no frills, low cost service','5 - 28',true);
INSERT INTO shipping_method (title, carrier, service_type, class_name, description, transit_time, is_international)
    VALUES ('Federal Express', 'FDXG','FEDEX_GROUND','Fedex', 'Basic ground service','4 - 5',true);
INSERT INTO shipping_method (title, carrier, service_type, class_name, description, transit_time, is_international)
    VALUES ('Federal Express', 'FDXE','FEDEX_2_DAY','Fedex','2 day service','2',true);
INSERT INTO shipping_method (title, carrier, service_type, class_name, description, transit_time, is_international)
    VALUES ('Federal Express', 'FDXE','STANDARD_OVERNIGHT','Fedex','Standard Overnight service','1',true);
INSERT INTO shipping_method (title, carrier, service_type, class_name, description, transit_time, is_international)
    VALUES ('Federal Express', 'FDXE','INTERNATIONAL_ECONOMY','Fedex','Economy International service','3 - 5',true);
INSERT INTO shipping_method (title, carrier, service_type, class_name, description, transit_time, is_international)
    VALUES ('Federal Express', 'FDXE','INTERNATIONAL_PRIORITY','Fedex','Priority International service',' 2 - 3 ',true);
INSERT INTO shipping_method (title, carrier, service_type, class_name, description, transit_time, is_international)
    VALUES ('Federal Express', 'FDXE','INTERNATIONAL_FIRST','Fedex','First class International service','2',true);

CREATE TABLE `payment_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64),
    CONSTRAINT pk_payment_type PRIMARY KEY (`id`),
    UNIQUE KEY idx_payment_type(name)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO payment_type (name) VALUES ('Online');
INSERT INTO payment_type (name) VALUES ('MailIn');
INSERT INTO payment_type (name) VALUES ('Cash');
INSERT INTO payment_type (name) VALUES ('Credit');
INSERT INTO payment_type (name) VALUES ('GiftCertificate');
INSERT INTO payment_type (name) VALUES ('Complimentary');


CREATE TABLE `payment_method` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(128) DEFAULT 'Payment Method',
    `service_provider` VARCHAR(128),
    `service_type` VARCHAR(128),
    `action_class_name` VARCHAR(128),
    `description` TEXT,
    `image_uri` VARCHAR(128),
    `active` BOOL DEFAULT FALSE,
    `requires_cc_number` BOOL DEFAULT FALSE,
    `save_cc_number` BOOL DEFAULT FALSE,
    `test_mode` BOOL DEFAULT TRUE,
    `sort_order` TINYINT UNSIGNED DEFAULT 0, -- ie, first or top
    `payment_type_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    CONSTRAINT pk_payment_method PRIMARY KEY (`id`),
    INDEX idx_payment_method_type(`payment_type_id`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO `payment_method` ( title, service_provider, service_type,  action_class_name, description, payment_type_id, active, sort_order)
    VALUES ('Check/Money Order', 'Our Store', 'Mail', 'PayByMailAction', 'Payment sent by mail', 3, true, 0);
INSERT INTO `payment_method` ( title, service_provider, service_type, action_class_name, description, payment_type_id, sort_order)
    VALUES ('PayPal Express Checkout', 'Paypal.com', 'Express Checkout', 'PayPalNVPAction', 'Implementation of PayPal Express Checkout', 1, 2);
INSERT INTO `payment_method` ( title, service_provider, service_type, action_class_name, description, payment_type_id, sort_order)
    VALUES ('Credit Card', 'Authorize.net', 'Credit Card', 'AuthorizeNetAction', 'Authorize.net credit card payment processing', 1, 3);

CREATE TABLE `paypal_transaction` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `correlation_id` VARCHAR(128),
    `transaction_id` VARCHAR(128),
    `pp_token` VARCHAR(128),
    `payer_id` VARCHAR (128),
    `payer_status` VARCHAR (128),
    `payment_status` VARCHAR (128),
    `ack_returned` VARCHAR(32),
    `api_action` VARCHAR(32),
    `time_stamp` DATETIME,
    `api_version` VARCHAR(4),
    `messages` TEXT,
    `amount` DECIMAL(12,2),
/*    `shipping_charged` DECIMAL(12,2),
    `handling_charged` DECIMAL(12,2),
    `tax` DECIMAL(12,2),
    `product_total` DECIMAL(12,2),*/
    `pp_fee` DECIMAL(12,2),
    `payment_method_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    CONSTRAINT pk_pp_transaction PRIMARY KEY (`id`),
    INDEX idx_pp_transaction_correlationid(`correlation_id`),
    INDEX idx_pp_transaction_orderid(`order_id`),
    INDEX idx_pp_transaction_payment_methodid(`payment_method_id`),
    FOREIGN KEY (`order_id`) REFERENCES `order`(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `authorize_net_transaction` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `response_code` VARCHAR(8),
    `response_subcode` VARCHAR(8),
    `response_reason_code` VARCHAR(8),
    `response_reason_text` TEXT,
    `authorization_code` VARCHAR(8),
    `transaction_id` VARCHAR(128),
    `transaction_type` VARCHAR(128),
    `amount` DECIMAL(12,2),
    `avs_response_code` VARCHAR(8),    
    `ccv_response_code` VARCHAR(8),
    `cav_response_code` VARCHAR(8),
    CONSTRAINT pk_authnet_transaction PRIMARY KEY (`id`),
    INDEX idx_authnet_transaction_transactionid(`transaction_id`),
    INDEX idx_authnet_transaction_orderid(`order_id`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `order_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64),
    CONSTRAINT pk_order_type PRIMARY KEY (`id`),
    UNIQUE KEY idx_order_type(name)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO order_type (name) VALUES ('Normal');
INSERT INTO order_type (name) VALUES ('Internal');
INSERT INTO order_type (name) VALUES ('Employee');
INSERT INTO order_type (name) VALUES ('Affiliate');

CREATE TABLE `order` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT 1, -- in house
  `creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modification_date` TIMESTAMP,
  `completion_date` DATETIME DEFAULT '0000-00-00 00:00:00',
  `product_total_cost`  DECIMAL(12,2),
  `shipping_cost` DECIMAL(12,2),
  `shipping_charged` DECIMAL(12,2),
--  `handling_cost` DECIMAL(12,2),
  `handling_charged` DECIMAL(12,2),
  `tax` DECIMAL(12,2),
  `product_total_charged` DECIMAL(12,2),
  `shipping_name_prefix` VARCHAR(8),
  `shipping_first_name` VARCHAR(128),
  `shipping_middle_name` VARCHAR(128),
  `shipping_last_name` VARCHAR(128),
  `shipping_name_suffix` VARCHAR(8),
  `shipping_company` VARCHAR(128),
  `shipping_street1` VARCHAR(128),
  `shipping_street2` VARCHAR(128),
  `shipping_suburb` VARCHAR(128),
  `shipping_county` VARCHAR(128),
  `shipping_city` VARCHAR(128),
  `shipping_zone_id` SMALLINT UNSIGNED,
  `shipping_country_id`  SMALLINT UNSIGNED,
  `shipping_postal_code` VARCHAR(16),
  `billing_name_prefix` VARCHAR(8),
  `billing_first_name` VARCHAR(128),
  `billing_middle_name` VARCHAR(128),
  `billing_last_name` VARCHAR(128),
  `billing_name_suffix` VARCHAR(8),
  `billing_company` VARCHAR(128),
  `billing_street1` VARCHAR(128),
  `billing_street2` VARCHAR(128),
  `billing_suburb` VARCHAR(128),
  `billing_county` VARCHAR(128),
  `billing_city` VARCHAR(128),
  `billing_zone_id` SMALLINT UNSIGNED,
  `billing_country_id`  SMALLINT UNSIGNED,
  `billing_postal_code` VARCHAR(16),
  `notes` TEXT ,
  `shipping_method_id` TINYINT UNSIGNED DEFAULT 1,
  `payment_method_id` TINYINT UNSIGNED DEFAULT 1,
  `status_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `type_id` TINYINT UNSIGNED DEFAULT 1,
  CONSTRAINT pk_order PRIMARY KEY (`id`),
  INDEX idx_order_account (`account_id`),
  INDEX idx_order_shipping_method (`shipping_method_id`),
  INDEX idx_order_payment_method (`payment_method_id`),
  INDEX idx_order_status (`status_id`),
  INDEX idx_order_type (`type_id`),
  INDEX idx_order_shipping_zone (`shipping_zone_id`),
  INDEX idx_order_billing_zone (`billing_zone_id`),
  INDEX idx_order_shipping_country (`shipping_country_id`),
  INDEX idx_order_billing_country (`billing_country_id`),
  FOREIGN KEY (`account_id`) REFERENCES `account`(`id`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `tracking_number` (
    `order_id` BIGINT UNSIGNED NOT NULL,
    `number` VARCHAR(64),
  CONSTRAINT pk_tracking_number PRIMARY KEY (`order_id`, `number`),
  INDEX idx_tracking_number_orderid(`order_id`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `order_change_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64),
    CONSTRAINT pk_order_change_type PRIMARY KEY (`id`),
    UNIQUE KEY idx_order_change_type(name)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO order_change_type (name) VALUES ('Refund');
INSERT INTO order_change_type (name) VALUES ('OrderDiscount');
INSERT INTO order_change_type (name) VALUES ('ItemDiscount');
INSERT INTO order_change_type (name) VALUES ('ItemQuantity');
INSERT INTO order_change_type (name) VALUES ('ItemAddition'); -- i _think_ this can support back orders
INSERT INTO order_change_type (name) VALUES ('ShippingAddition');

/* Note: we may have to add discount type, nullable ..*/
CREATE TABLE  `order_change` (
  `order_id` BIGINT UNSIGNED NOT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` TEXT,
  `value` DECIMAL(12,2),
  `type_id` TINYINT UNSIGNED NOT NULL,
  CONSTRAINT pk_order_change PRIMARY KEY  (`order_id`,`date`),
  INDEX `idx_order_change_type` (`type_id`),
  FOREIGN KEY (`order_id`) REFERENCES `order`(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `order_status_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` TEXT ,
  `status_id` TINYINT UNSIGNED NOT NULL,
  CONSTRAINT pk_order_status_history PRIMARY KEY (`id`),
  INDEX idx_order_status_history_order (`order_id`),
  INDEX idx_order_status_history_status (`status_id`),
  FOREIGN KEY (`order_id`) REFERENCES `order`(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;


CREATE TABLE `shopping_cart` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modification` TIMESTAMP,
  `account_id` MEDIUMINT UNSIGNED NOT NULL,
  CONSTRAINT pk_shopping_cart PRIMARY KEY (`id`),
 -- INDEX idx_shopping_cart_status (`order_id`),
  FOREIGN KEY (`account_id`) REFERENCES `account`(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `block_location_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(128),
    CONSTRAINT pk_block_location_type PRIMARY KEY (`id`),
    UNIQUE KEY uk_block_location_type (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO block_location_type (name) VALUES ('PageHeader');
INSERT INTO block_location_type (name) VALUES ('PageFooter');
INSERT INTO block_location_type (name) VALUES ('RightPanel');
INSERT INTO block_location_type (name) VALUES ('CenterPanel');
INSERT INTO block_location_type (name) VALUES ('LeftPanel');
INSERT INTO block_location_type (name) VALUES ('ExtraPanel1');
INSERT INTO block_location_type (name) VALUES ('ExtraPanel2');
INSERT INTO block_location_type (name) VALUES ('ExtraPanel3');
INSERT INTO block_location_type (name) VALUES ('ExtraPanel4');
INSERT INTO block_location_type (name) VALUES ('PageBody'); -- ie. <body> is parent, render outside container ..

CREATE TABLE `content_block` (
  `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) , -- human reference, also used for CSS id
  `cssclass` VARCHAR(128) , -- CSS class
  `title` VARCHAR(128) , -- optionally visible text
  `description` VARCHAR(256) , -- optionally visible text, eg. on list pages
  `show_title` BOOL NOT NULL DEFAULT FALSE,
  `show_description` BOOL NOT NULL DEFAULT FALSE,
  `collapsable` BOOL NOT NULL DEFAULT FALSE , -- optionally visible text, eg. on list pages
  `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0, -- ie, first or top in ul
  `parent_content_block_id` MEDIUMINT UNSIGNED, 
  `location_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  CONSTRAINT pk_content_block PRIMARY KEY (`id`),
  INDEX idx_content_block_parent (`parent_content_block_id`),
  INDEX idx_content_block_location (`location_id`),
  UNIQUE KEY idx_content_block_name (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `content_category` (
  `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) , -- human reference
  `title` VARCHAR(128) , -- optionally visible text
  `description` VARCHAR(256) , -- optionally visible text, eg. on list pages
  `image_uri` VARCHAR(256) , -- optional image for the category
  `parent_content_category_id` MEDIUMINT UNSIGNED,
  `public_permissions_id` TINYINT UNSIGNED NOT NULL DEFAULT 2, -- view ..
  `user_permissions_id` TINYINT UNSIGNED  NOT NULL DEFAULT 1,
  `group_permissions_id` TINYINT UNSIGNED  NOT NULL DEFAULT 1,
  CONSTRAINT pk_content_category PRIMARY KEY (`id`),
  INDEX idx_content_category_parent (`parent_content_category_id`),
  INDEX idx_content_category_public_perms (`public_permissions_id`),
  INDEX idx_content_category_user_perms (`user_permissions_id`),
  INDEX idx_content_category_group_perms (`group_permissions_id`),
  UNIQUE KEY idx_content_category_name (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `product_category` (
  `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) , -- human reference
  `title` VARCHAR(128) , -- optionally visible text
  `description` VARCHAR(256) , -- optionally visible text, eg. on list pages
  `image_uri` VARCHAR(256) , -- optional image for the category
  `parent_product_category_id` MEDIUMINT UNSIGNED,
  `public_permissions_id` TINYINT UNSIGNED NOT NULL DEFAULT 2, -- view ..
  `user_permissions_id` TINYINT UNSIGNED  NOT NULL DEFAULT 1,
  `group_permissions_id` TINYINT UNSIGNED  NOT NULL DEFAULT 1,
  CONSTRAINT pk_product_category PRIMARY KEY (`id`),
  INDEX idx_product_category_parent (`parent_product_category_id`),
  INDEX idx_product_category_public_perms (`public_permissions_id`),
  INDEX idx_product_category_user_perms (`user_permissions_id`),
  INDEX idx_product_category_group_perms (`group_permissions_id`),
  UNIQUE KEY idx_product_category_name (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `content_status_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(128),
    CONSTRAINT pk_content_status_type PRIMARY KEY (`id`),
    UNIQUE KEY uk_content_status_type (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `content_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(128),
    CONSTRAINT pk_content_type PRIMARY KEY (`id`),
    UNIQUE KEY uk_content_type (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;


INSERT INTO content_status_type (name) VALUES ('Published');
INSERT INTO content_status_type (name) VALUES ('Unpublished');
INSERT INTO content_status_type (name) VALUES ('Draft');
INSERT INTO content_status_type (name) VALUES ('Internal');
 
 -- eg. the home page center column, or "about us" ..
INSERT INTO content_type (name) VALUES ('PageBody');
 -- a news article, faq, blog - something likely listed in a pagebody with teasers ..
INSERT INTO content_type (name) VALUES ('Article');
 -- likely to be listed, or displayed and also can be in a shopping cart ..
INSERT INTO content_type (name) VALUES ('Product');
INSERT INTO content_type (name) VALUES ('Image');
INSERT INTO content_type (name) VALUES ('Video');
INSERT INTO content_type (name) VALUES ('Audio');
INSERT INTO content_type (name) VALUES ('Comment');
INSERT INTO content_type (name) VALUES ('ForumPost');
INSERT INTO content_type (name) VALUES ('BlogPost');
 -- little things that go near something else - a form instruction, image descript - small text area; ie a <span>
INSERT INTO content_type (name) VALUES ('Description');

CREATE TABLE `content_item` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL, -- human reference, also used for CSS id
  `cssclass` VARCHAR(128) , -- CSS class
  `title` VARCHAR(128) , -- optionally visible text
  `description` VARCHAR(256) , -- optionally visible text
  `text` TEXT, -- use either this or uri, if this is null, we try to get uri ..
  `sort_order` MEDIUMINT UNSIGNED DEFAULT 0, -- ie, first or top in ul
  `show_title` BOOL NOT NULL DEFAULT TRUE,
  `show_description` BOOL NOT NULL DEFAULT FALSE,
  `show_creator` BOOL NOT NULL DEFAULT TRUE,
  `show_creation_date` BOOL NOT NULL DEFAULT TRUE,
  `show_last_modification` BOOL NOT NULL DEFAULT TRUE,
  `creator_id` MEDIUMINT UNSIGNED DEFAULT 1,
  `copyright_notice` VARCHAR(256),
  `creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modification` TIMESTAMP,
  `public_permissions_id` TINYINT UNSIGNED NOT NULL DEFAULT 2, -- view ..
  `user_permissions_id` TINYINT UNSIGNED  NOT NULL DEFAULT 1,
  `group_permissions_id` TINYINT UNSIGNED  NOT NULL DEFAULT 1,
--  `is_virtual` BOOL DEFAULT FALSE, -- eg. from somewhere else in a frame ..
  `type_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `status_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  CONSTRAINT pk_content_item PRIMARY KEY (`id`),
  UNIQUE KEY uk_content_name (`name`),
  INDEX idx_content_title (`title`),
  INDEX idx_content_creator (`creator_id`),
  INDEX idx_content_type (`type_id`),
  INDEX idx_content_status (`status_id`),
  INDEX idx_content_public_perms (`public_permissions_id`),
  INDEX idx_content_user_perms (`user_permissions_id`),
  INDEX idx_content_group_perms (`group_permissions_id`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `module` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL, -- human reference, also used for CSS id
  `cssclass` VARCHAR(128) , -- CSS class
  `title` VARCHAR(128) , -- optionally visible text
  `description` VARCHAR(256) , -- optionally visible text
  `class_name` VARCHAR(256), -- name of the active class object, a "View"
  `show_title` BOOL NOT NULL DEFAULT TRUE,
  `show_description` BOOL NOT NULL DEFAULT FALSE,
  `content_block_id` MEDIUMINT UNSIGNED,
  `parent_module_id` INT UNSIGNED,
  `public_permissions_id` TINYINT UNSIGNED NOT NULL DEFAULT 2, -- ie. view ..
  `user_permissions_id` TINYINT UNSIGNED  NOT NULL DEFAULT 1,
  `group_permissions_id` TINYINT UNSIGNED  NOT NULL DEFAULT 1,
  CONSTRAINT pk_module PRIMARY KEY (`id`),
  UNIQUE KEY uk_module_name (`name`),
  FOREIGN KEY (`parent_module_id`) REFERENCES module(`id`),
  INDEX idx_module_block (`content_block_id`),
  INDEX idx_module_public_perms (`public_permissions_id`),
  INDEX idx_module_user_perms (`user_permissions_id`),
  INDEX idx_module_group_perms (`group_permissions_id`),
  INDEX idx_module_title (`title`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `page_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(128),
    CONSTRAINT pk_page_type PRIMARY KEY (`id`),
    UNIQUE KEY uk_page_type (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO page_type (name) VALUES ('Basic');
INSERT INTO page_type (name) VALUES ('Home');
INSERT INTO page_type (name) VALUES ('Login');
INSERT INTO page_type (name) VALUES ('UserHome');
INSERT INTO page_type (name) VALUES ('EditContactInfo');
INSERT INTO page_type (name) VALUES ('EditPersons');
INSERT INTO page_type (name) VALUES ('Product');
INSERT INTO page_type (name) VALUES ('ProductList');
INSERT INTO page_type (name) VALUES ('ShoppingCartView');
INSERT INTO page_type (name) VALUES ('Checkout');
INSERT INTO page_type (name) VALUES ('Payment');
INSERT INTO page_type (name) VALUES ('ShippingInfo');
INSERT INTO page_type (name) VALUES ('OrderHistory');
INSERT INTO page_type (name) VALUES ('Forum');
INSERT INTO page_type (name) VALUES ('ForumList');
INSERT INTO page_type (name) VALUES ('Article');
INSERT INTO page_type (name) VALUES ('ArticleList');
INSERT INTO page_type (name) VALUES ('Admin');

CREATE TABLE `page_doc_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(128),
    CONSTRAINT pk_page_doc_type PRIMARY KEY (`id`),
    UNIQUE KEY uk_page_doc_type (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;


INSERT INTO page_doc_type (name) VALUES ('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
INSERT INTO page_doc_type (name) VALUES ('PDF');
INSERT INTO page_doc_type (name) VALUES ('TEXT');
INSERT INTO page_doc_type (name) VALUES ('RSS');
INSERT INTO page_doc_type (name) VALUES ('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">');
INSERT INTO page_doc_type (name) VALUES ('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">');
INSERT INTO page_doc_type (name) VALUES ('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">');
INSERT INTO page_doc_type (name) VALUES ('<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
INSERT INTO page_doc_type (name) VALUES ('<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">');
INSERT INTO page_doc_type (name) VALUES ('<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">');
INSERT INTO page_doc_type (name) VALUES ('<!DOCTYPE HTML PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">');
INSERT INTO page_doc_type (name) VALUES ('<!DOCTYPE HTML PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.1//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile11.dtd">');

CREATE TABLE `page_status_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(128),
    CONSTRAINT pk_page_status_type PRIMARY KEY (`id`),
    UNIQUE KEY uk_page_status_type (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO page_status_type (name) VALUES ('Published');
INSERT INTO page_status_type (name) VALUES ('Unpublished');
INSERT INTO page_status_type (name) VALUES ('Draft');
INSERT INTO page_status_type (name) VALUES ('Internal');

CREATE TABLE `html_meta_tag`(
  `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) , -- human reference
  `content` VARCHAR(256) ,
  `type` ENUM('NAME','HTTP-EQUIV') DEFAULT 'NAME',
  CONSTRAINT pk_html_meta_tag PRIMARY KEY (`id`),
  INDEX idx_html_meta_tag_name (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `style_sheet`(
  `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) , -- human reference
  `description` VARCHAR(256) ,
  `filename` VARCHAR(128) ,
  `type` ENUM('HTML','XML') DEFAULT 'HTML',
  CONSTRAINT pk_style_sheet PRIMARY KEY (`id`),
  INDEX idx_style_sheet_name (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `java_script`(
  `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) , -- human reference
  `description` VARCHAR(256) ,
  `filename` VARCHAR(128) ,
  CONSTRAINT pk_java_script PRIMARY KEY (`id`),
  INDEX idx_java_script_name (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `page` (
  `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modification` TIMESTAMP,
  `name` VARCHAR(128) , -- human reference
  `title` VARCHAR(256) , 
  `uri` VARCHAR(256) DEFAULT 'index.php',
  `has_header` BOOL NOT NULL DEFAULT TRUE,
  `has_left_column` BOOL NOT NULL DEFAULT TRUE,
  `has_right_column` BOOL NOT NULL DEFAULT TRUE,
  `has_footer` BOOL NOT NULL DEFAULT TRUE,
  `public_permissions_id` TINYINT UNSIGNED NOT NULL DEFAULT 1, -- ie. none ..
  `user_permissions_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `group_permissions_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `type_id` TINYINT UNSIGNED DEFAULT 1,
  `doc_type_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `status_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  CONSTRAINT pk_page PRIMARY KEY (`id`),
  INDEX idx_page_doc_type (`doc_type_id`),
  INDEX idx_page_type (`type_id`),
  INDEX idx_page_status (`status_id`),
  INDEX idx_page_public_perms (`public_permissions_id`),
  INDEX idx_page_user_perms (`user_permissions_id`),
  INDEX idx_page_group_perms (`group_permissions_id`),
  UNIQUE KEY idx_page_name (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;


CREATE TABLE `menu_status_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(128),
    CONSTRAINT pk_menu_status_type PRIMARY KEY (`id`),
    UNIQUE KEY uk_menu_status_type (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO menu_status_type (name) VALUES ('Active');
INSERT INTO menu_status_type (name) VALUES ('Disabled');


CREATE TABLE `menu_item_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(128),
    CONSTRAINT pk_menu_item_type PRIMARY KEY (`id`),
    UNIQUE KEY uk_menu_item_type (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO menu_item_type (name) VALUES ('ListMenuItem'); -- the usual <li>
INSERT INTO menu_item_type (name) VALUES ('TabMenuItem'); -- basically show/hide a div, may be AJAX
INSERT INTO menu_item_type (name) VALUES ('BlockMenuItem'); -- a div block, optionally with embedded HTML
INSERT INTO menu_item_type (name) VALUES ('LinkMenuItem'); -- <a> link anywhere in a page ..


CREATE TABLE `menu_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(128),
    CONSTRAINT pk_menu_type PRIMARY KEY (`id`),
    UNIQUE KEY uk_menu_type (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO menu_type (name) VALUES ('SideBar');
INSERT INTO menu_type (name) VALUES ('Header');
INSERT INTO menu_type (name) VALUES ('Tabbed');
INSERT INTO menu_type (name) VALUES ('Footer');

CREATE TABLE `menu_item` (
  `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL, -- human reference
  `css_class` VARCHAR(32) ,
  `label` VARCHAR(32) , -- text on the menu item
  `uri` VARCHAR(256) NOT NULL, -- either a Page name (eg. Home, ContactUs) or remote link
  `is_local` BOOL NOT NULL DEFAULT TRUE, -- false for external links
  `is_ssl` BOOL NOT NULL DEFAULT FALSE, -- false for external links
  `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0, -- ie, first or top in ul
  `public_permissions_id` TINYINT UNSIGNED NOT NULL DEFAULT 2,
  `user_permissions_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `group_permissions_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `status_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `type_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `page_id` MEDIUMINT UNSIGNED DEFAULT 0, -- refers to the page we point to, zero for external links
  CONSTRAINT pk_menu_item PRIMARY KEY (`id`),
  UNIQUE KEY idx_menu_item_name (`name`),
  INDEX idx_menu_item_type (`type_id`),
  INDEX idx_menu_item_status (`status_id`),
  INDEX idx_menu_item_public_perms (`public_permissions_id`),
  INDEX idx_menu_item_user_perms (`user_permissions_id`),
  INDEX idx_menu_item_group_perms (`group_permissions_id`),
  FOREIGN KEY (`page_id`) REFERENCES page(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `menu` (
  `id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL , -- human reference
  `title` VARCHAR(32) , -- visible text
  `css_class` VARCHAR(32) , 
  `sort_order` TINYINT UNSIGNED DEFAULT 0, -- ie, first or top in ul
  `show_title` BOOL DEFAULT TRUE,
  `menu_item_id` MEDIUMINT UNSIGNED DEFAULT 0, -- ie, this is a submenu within a menu_item
  `public_permissions_id` TINYINT UNSIGNED NOT NULL DEFAULT 1, -- ie. none ..
  `user_permissions_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `group_permissions_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `status_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `type_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  CONSTRAINT pk_menu PRIMARY KEY (`id`),
  INDEX idx_menu_item (`menu_item_id`),
  INDEX idx_menu_type (`type_id`),
  INDEX idx_menu_status (`status_id`),
  INDEX idx_menu_public_perms (`public_permissions_id`),
  INDEX idx_menu_user_perms (`user_permissions_id`),
  INDEX idx_menu_group_perms (`group_permissions_id`),
  UNIQUE KEY idx_menu_name (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `order_item_status_type` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(128),
    CONSTRAINT pk_order_item_status_type PRIMARY KEY (`id`),
    UNIQUE KEY uk_order_item_status_type (`name`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO order_item_status_type (name) VALUES ('Ordered');
INSERT INTO order_item_status_type (name) VALUES ('Processing');
INSERT INTO order_item_status_type (name) VALUES ('BackOrdered');
INSERT INTO order_item_status_type (name) VALUES ('Shipped');
INSERT INTO order_item_status_type (name) VALUES ('Returned');
INSERT INTO order_item_status_type (name) VALUES ('Cancelled');
INSERT INTO order_item_status_type (name) VALUES ('Internal');

/*****************************************************************
                                   Association tables
******************************************************************/

CREATE TABLE `order_item` (
  `order_id` BIGINT UNSIGNED NOT NULL,
  `product_id` MEDIUMINT UNSIGNED NOT NULL,
  `quantity` MEDIUMINT UNSIGNED NOT NULL DEFAULT 1, 
  `status_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  CONSTRAINT pk_order_product PRIMARY KEY (`product_id`,`order_id`),
  INDEX idx_order_item_order (`order_id`),
  INDEX idx_order_item_product (`product_id`),
--  FOREIGN KEY (`status_id` ) REFERENCES order_item_status_type(`id`),
  FOREIGN KEY (`product_id` ) REFERENCES product(`id`),
  FOREIGN KEY (`order_id`) REFERENCES `order`(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `shopping_cart_item` (
  `shopping_cart_id` BIGINT UNSIGNED NOT NULL,
  `product_id` MEDIUMINT UNSIGNED NOT NULL,
  `quantity` MEDIUMINT UNSIGNED NOT NULL DEFAULT 1,
  CONSTRAINT pk_shopping_cart_product PRIMARY KEY (`product_id`,`shopping_cart_id`),
  INDEX idx_shopping_cart_product_shopping_cart (`shopping_cart_id`),
  INDEX idx_shopping_cart_product_product (`product_id`),
  FOREIGN KEY (`product_id` ) REFERENCES product(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`shopping_cart_id`) REFERENCES `shopping_cart`(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `related_product_assn` (
    product_id MEDIUMINT UNSIGNED NOT NULL,
    related_product_id MEDIUMINT UNSIGNED NOT NULL,
  FOREIGN KEY (`product_id`) REFERENCES product(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`related_product_id`) REFERENCES product(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `product_product_category_assn` (
    `product_id` MEDIUMINT UNSIGNED NOT NULL,
    `product_category_id` MEDIUMINT UNSIGNED NOT NULL,
    CONSTRAINT pk_product_product_category PRIMARY KEY (`product_category_id`,`product_id`),
    INDEX idx_product_product_category_product(`product_id`),
    INDEX idx_product_product_category_product_category(`product_category_id`),
    FOREIGN KEY (`product_category_id`) REFERENCES product_category(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES product(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `person_usergroup_assn` (
    `person_id` MEDIUMINT UNSIGNED NOT NULL,
    `usergroup_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT 1,
    CONSTRAINT pk_person_usergroup PRIMARY KEY (`usergroup_id`,`person_id`),
    INDEX idx_person_usergroup_person(`person_id`),
    INDEX idx_person_usergroup_usergroup(`usergroup_id`),
    FOREIGN KEY (`usergroup_id`) REFERENCES usergroup(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`person_id`) REFERENCES person(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `content_item_usergroup_assn` (
    `content_item_id` INT UNSIGNED NOT NULL,
    `usergroup_id` MEDIUMINT UNSIGNED NOT NULL,
    CONSTRAINT pk_content_item_usergroup PRIMARY KEY (`usergroup_id`,`content_item_id`),
    INDEX idx_content_item_usergroup_content_item(`content_item_id`),
    INDEX idx_content_item_usergroup_usergroup(`usergroup_id`),
    FOREIGN KEY (`usergroup_id`) REFERENCES usergroup(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`content_item_id`) REFERENCES content_item(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `page_content_category_assn` (
    `page_id` MEDIUMINT UNSIGNED NOT NULL,
    `content_category_id` MEDIUMINT UNSIGNED NOT NULL,
    CONSTRAINT pk_page_content_category PRIMARY KEY (`content_category_id`,`page_id`),
    INDEX idx_page_content_category_page(`page_id`),
    INDEX idx_page_content_category_content_category(`content_category_id`),
    FOREIGN KEY (`content_category_id`) REFERENCES content_category(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`page_id`) REFERENCES page(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;


CREATE TABLE `page_usergroup_assn` (
    `page_id` MEDIUMINT UNSIGNED NOT NULL,
    `usergroup_id` MEDIUMINT UNSIGNED NOT NULL,
    CONSTRAINT pk_page_usergroup PRIMARY KEY (`usergroup_id`,`page_id`),
    INDEX idx_page_usergroup_page(`page_id`),
    INDEX idx_page_usergroup_usergroup(`usergroup_id`),
    FOREIGN KEY (`usergroup_id`) REFERENCES usergroup(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`page_id`) REFERENCES page(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `page_html_meta_tag_assn` (
    `page_id` MEDIUMINT UNSIGNED NOT NULL,
    `html_meta_tag_id` MEDIUMINT UNSIGNED NOT NULL,
    CONSTRAINT pk_page_html_meta_tag PRIMARY KEY (`html_meta_tag_id`,`page_id`),
    INDEX idx_page_html_meta_tag_page(`page_id`),
    INDEX idx_page_html_meta_tag_html_meta_tag(`html_meta_tag_id`),
    FOREIGN KEY (`html_meta_tag_id`) REFERENCES html_meta_tag(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`page_id`) REFERENCES page(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;


CREATE TABLE `page_style_sheet_assn` (
    `page_id` MEDIUMINT UNSIGNED NOT NULL,
    `style_sheet_id` MEDIUMINT UNSIGNED NOT NULL,
    CONSTRAINT pk_page_style_sheet PRIMARY KEY (`style_sheet_id`,`page_id`),
    INDEX idx_page_style_sheet_page(`page_id`),
    INDEX idx_page_style_sheet_style_sheet(`style_sheet_id`),
    FOREIGN KEY (`style_sheet_id`) REFERENCES style_sheet(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`page_id`) REFERENCES page(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `page_java_script_assn` (
    `page_id` MEDIUMINT UNSIGNED NOT NULL,
    `java_script_id` MEDIUMINT UNSIGNED NOT NULL,
    CONSTRAINT pk_page_java_script PRIMARY KEY (`java_script_id`,`page_id`),
    INDEX idx_page_java_script_page(`page_id`),
    INDEX idx_page_java_script_java_script(`java_script_id`),
    FOREIGN KEY (`java_script_id`) REFERENCES java_script(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`page_id`) REFERENCES page(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `menu_content_block_assn` (
    `menu_id` SMALLINT UNSIGNED NOT NULL,
    `content_block_id` MEDIUMINT UNSIGNED NOT NULL,
    CONSTRAINT pk_menu_content_block PRIMARY KEY (`content_block_id`,`menu_id`),
    INDEX idx_menu_content_block_menu(`menu_id`),
    INDEX idx_menu_content_block_content_block(`content_block_id`),
    FOREIGN KEY (`content_block_id`) REFERENCES content_block(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`menu_id`) REFERENCES menu(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `content_item_content_block_assn` (
    `content_item_id` INT UNSIGNED NOT NULL,
    `content_block_id` MEDIUMINT UNSIGNED NOT NULL,
    CONSTRAINT pk_content_item_content_block PRIMARY KEY (`content_block_id`,`content_item_id`),
    INDEX idx_content_item_content_block_content_item(`content_item_id`),
    INDEX idx_content_item_content_block_content_block(`content_block_id`),
    FOREIGN KEY (`content_block_id`) REFERENCES content_block(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`content_item_id`) REFERENCES content_item(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `content_item_content_category_assn` (
    `content_item_id` INT UNSIGNED NOT NULL,
    `content_category_id` MEDIUMINT UNSIGNED NOT NULL,
    CONSTRAINT pk_content_item_content_category PRIMARY KEY (`content_category_id`,`content_item_id`),
    INDEX idx_content_item_content_category_content_item(`content_item_id`),
    INDEX idx_content_item_content_category_content_category(`content_category_id`),
    FOREIGN KEY (`content_category_id`) REFERENCES content_category(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`content_item_id`) REFERENCES content_item(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `content_block_page_assn` (
    `content_block_id` MEDIUMINT UNSIGNED NOT NULL,
    `page_id` MEDIUMINT UNSIGNED NOT NULL,
    CONSTRAINT pk_content_block_page PRIMARY KEY (`page_id`,`content_block_id`),
    INDEX idx_content_block_page_content_block(`content_block_id`),
    INDEX idx_content_block_page_page(`page_id`),
    FOREIGN KEY (`page_id`) REFERENCES page(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`content_block_id`) REFERENCES content_block(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `menu_item_menu_assn` (
    `menu_item_id` MEDIUMINT UNSIGNED NOT NULL,
    `menu_id` SMALLINT UNSIGNED NOT NULL,
    CONSTRAINT pk_menu_item_menu PRIMARY KEY (`menu_id`,`menu_item_id`),
    INDEX idx_menu_item_menu_menu_item(`menu_item_id`),
    INDEX idx_menu_item_menu_menu(`menu_id`),
    FOREIGN KEY (`menu_id`) REFERENCES menu(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`menu_item_id`) REFERENCES menu_item(`id`) ON DELETE CASCADE
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;


INSERT INTO `country_type` (`id`, `name`, `iso_code_2`, `iso_code_3`) VALUES
(255, 'World', '--', '---'),
(1, 'Afghanistan', 'AF', 'AFG'),
(2, 'Albania', 'AL', 'ALB'),
(3, 'Algeria', 'DZ', 'DZA'),
(4, 'American Samoa', 'AS', 'ASM'),
(5, 'Andorra', 'AD', 'AND'),
(6, 'Angola', 'AO', 'AGO'),
(7, 'Anguilla', 'AI', 'AIA'),
(8, 'Antarctica', 'AQ', 'ATA'),
(9, 'Antigua and Barbuda', 'AG', 'ATG'),
(10, 'Argentina', 'AR', 'ARG'),
(11, 'Armenia', 'AM', 'ARM'),
(12, 'Aruba', 'AW', 'ABW'),
(13, 'Australia', 'AU', 'AUS'),
(14, 'Austria', 'AT', 'AUT'),
(15, 'Azerbaijan', 'AZ', 'AZE'),
(16, 'Bahamas', 'BS', 'BHS'),
(17, 'Bahrain', 'BH', 'BHR'),
(18, 'Bangladesh', 'BD', 'BGD'),
(19, 'Barbados', 'BB', 'BRB'),
(20, 'Belarus', 'BY', 'BLR'),
(21, 'Belgium', 'BE', 'BEL'),
(22, 'Belize', 'BZ', 'BLZ'),
(23, 'Benin', 'BJ', 'BEN'),
(24, 'Bermuda', 'BM', 'BMU'),
(25, 'Bhutan', 'BT', 'BTN'),
(26, 'Bolivia', 'BO', 'BOL'),
(27, 'Bosnia-Herzegovina', 'BA', 'BIH'),
(28, 'Botswana', 'BW', 'BWA'),
(29, 'Bouvet Island', 'BV', 'BVT'),
(30, 'Brazil', 'BR', 'BRA'),
(31, 'British Indian Ocean Territory', 'IO', 'IOT'),
(32, 'Brunei Darussalam', 'BN', 'BRN'),
(33, 'Bulgaria', 'BG', 'BGR'),
(34, 'Burkina Faso', 'BF', 'BFA'),
(35, 'Burundi', 'BI', 'BDI'),
(36, 'Cambodia', 'KH', 'KHM'),
(37, 'Cameroon', 'CM', 'CMR'),
(38, 'Canada', 'CA', 'CAN'),
(39, 'Cape Verde', 'CV', 'CPV'),
(40, 'Cayman Islands', 'KY', 'CYM'),
(41, 'Central African Republic', 'CF', 'CAF'),
(42, 'Chad', 'TD', 'TCD'),
(43, 'Chile', 'CL', 'CHL'),
(44, 'China', 'CN', 'CHN'),
(45, 'Christmas Island', 'CX', 'CXR'),
(46, 'Cocos (Keeling) Islands', 'CC', 'CCK'),
(47, 'Colombia', 'CO', 'COL'),
(48, 'Comoros', 'KM', 'COM'),
(49, 'Congo', 'CG', 'COG'),
(50, 'Cook Islands', 'CK', 'COK'),
(51, 'Costa Rica', 'CR', 'CRI'),
(52, 'Cote D''Ivoire', 'CI', 'CIV'),
(53, 'Croatia', 'HR', 'HRV'),
(54, 'Cuba', 'CU', 'CUB'),
(55, 'Cyprus', 'CY', 'CYP'),
(56, 'Czech Republic', 'CZ', 'CZE'),
(57, 'Denmark', 'DK', 'DNK'),
(58, 'Djibouti', 'DJ', 'DJI'),
(59, 'Dominica', 'DM', 'DMA'),
(60, 'Dominican Republic', 'DO', 'DOM'),
(61, 'East Timor', 'TP', 'TMP'),
(62, 'Ecuador', 'EC', 'ECU'),
(63, 'Egypt', 'EG', 'EGY'),
(64, 'El Salvador', 'SV', 'SLV'),
(65, 'Equatorial Guinea', 'GQ', 'GNQ'),
(66, 'Eritrea', 'ER', 'ERI'),
(67, 'Estonia', 'EE', 'EST'),
(68, 'Ethiopia', 'ET', 'ETH'),
(69, 'Falkland Islands (Malvinas)', 'FK', 'FLK'),
(70, 'Faroe Islands', 'FO', 'FRO'),
(71, 'Fiji', 'FJ', 'FJI'),
(72, 'Finland', 'FI', 'FIN'),
(73, 'France', 'FR', 'FRA'),
(74, 'France, Metropolitan', 'FX', 'FXX'),
(75, 'French Guiana', 'GF', 'GUF'),
(76, 'French Polynesia', 'PF', 'PYF'),
(77, 'French Southern Territories', 'TF', 'ATF'),
(78, 'Gabon', 'GA', 'GAB'),
(79, 'Gambia', 'GM', 'GMB'),
(80, 'Georgia', 'GE', 'GEO'),
(81, 'Germany', 'DE', 'DEU'),
(82, 'Ghana', 'GH', 'GHA'),
(83, 'Gibraltar', 'GI', 'GIB'),
(84, 'Greece', 'GR', 'GRC'),
(85, 'Greenland', 'GL', 'GRL'),
(86, 'Grenada', 'GD', 'GRD'),
(87, 'Guadeloupe', 'GP', 'GLP'),
(88, 'Guam', 'GU', 'GUM'),
(89, 'Guatemala', 'GT', 'GTM'),
(90, 'Guinea', 'GN', 'GIN'),
(91, 'Guinea-bissau', 'GW', 'GNB'),
(92, 'Guyana', 'GY', 'GUY'),
(93, 'Haiti', 'HT', 'HTI'),
(94, 'Heard and Mc Donald Islands', 'HM', 'HMD'),
(95, 'Honduras', 'HN', 'HND'),
(96, 'Hong Kong', 'HK', 'HKG'),
(97, 'Hungary', 'HU', 'HUN'),
(98, 'Iceland', 'IS', 'ISL'),
(99, 'India', 'IN', 'IND'),
(100, 'Indonesia', 'ID', 'IDN'),
(101, 'Iran (Islamic Republic of)', 'IR', 'IRN'),
(102, 'Iraq', 'IQ', 'IRQ'),
(103, 'Ireland', 'IE', 'IRL'),
(104, 'Israel', 'IL', 'ISR'),
(105, 'Italy', 'IT', 'ITA'),
(106, 'Jamaica', 'JM', 'JAM'),
(107, 'Japan', 'JP', 'JPN'),
(108, 'Jordan', 'JO', 'JOR'),
(109, 'Kazakhstan', 'KZ', 'KAZ'),
(110, 'Kenya', 'KE', 'KEN'),
(111, 'Kiribati', 'KI', 'KIR'),
(112, 'Korea, Democratic People''s Republic of', 'KP', 'PRK'),
(113, 'Korea, Republic of', 'KR', 'KOR'),
(114, 'Kuwait', 'KW', 'KWT'),
(115, 'Kyrgyzstan', 'KG', 'KGZ'),
(116, 'Lao People''s Democratic Republic', 'LA', 'LAO'),
(117, 'Latvia', 'LV', 'LVA'),
(118, 'Lebanon', 'LB', 'LBN'),
(119, 'Lesotho', 'LS', 'LSO'),
(120, 'Liberia', 'LR', 'LBR'),
(121, 'Libyan Arab Jamahiriya', 'LY', 'LBY'),
(122, 'Liechtenstein', 'LI', 'LIE'),
(123, 'Lithuania', 'LT', 'LTU'),
(124, 'Luxembourg', 'LU', 'LUX'),
(125, 'Macau', 'MO', 'MAC'),
(126, 'Macedonia, The Former Yugoslav Republic of', 'MK', 'MKD'),
(127, 'Madagascar', 'MG', 'MDG'),
(128, 'Malawi', 'MW', 'MWI'),
(129, 'Malaysia', 'MY', 'MYS'),
(130, 'Maldives', 'MV', 'MDV'),
(131, 'Mali', 'ML', 'MLI'),
(132, 'Malta', 'MT', 'MLT'),
(133, 'Marshall Islands', 'MH', 'MHL'),
(134, 'Martinique', 'MQ', 'MTQ'),
(135, 'Mauritania', 'MR', 'MRT'),
(136, 'Mauritius', 'MU', 'MUS'),
(137, 'Mayotte', 'YT', 'MYT'),
(138, 'Mexico', 'MX', 'MEX'),
(139, 'Micronesia, Federated States of', 'FM', 'FSM'),
(140, 'Moldova, Republic of', 'MD', 'MDA'),
(141, 'Monaco', 'MC', 'MCO'),
(142, 'Mongolia', 'MN', 'MNG'),
(143, 'Montserrat', 'MS', 'MSR'),
(144, 'Morocco', 'MA', 'MAR'),
(145, 'Mozambique', 'MZ', 'MOZ'),
(146, 'Myanmar', 'MM', 'MMR'),
(147, 'Namibia', 'NA', 'NAM'),
(148, 'Nauru', 'NR', 'NRU'),
(149, 'Nepal', 'NP', 'NPL'),
(150, 'Netherlands', 'NL', 'NLD'),
(151, 'Netherlands Antilles', 'AN', 'ANT'),
(152, 'New Caledonia', 'NC', 'NCL'),
(153, 'New Zealand', 'NZ', 'NZL'),
(154, 'Nicaragua', 'NI', 'NIC'),
(155, 'Niger', 'NE', 'NER'),
(156, 'Nigeria', 'NG', 'NGA'),
(157, 'Niue', 'NU', 'NIU'),
(158, 'Norfolk Island', 'NF', 'NFK'),
(159, 'Northern Mariana Islands', 'MP', 'MNP'),
(160, 'Norway', 'NO', 'NOR'),
(161, 'Oman', 'OM', 'OMN'),
(162, 'Pakistan', 'PK', 'PAK'),
(163, 'Palau', 'PW', 'PLW'),
(164, 'Panama', 'PA', 'PAN'),
(165, 'Papua New Guinea', 'PG', 'PNG'),
(166, 'Paraguay', 'PY', 'PRY'),
(167, 'Peru', 'PE', 'PER'),
(168, 'Philippines', 'PH', 'PHL'),
(169, 'Pitcairn', 'PN', 'PCN'),
(170, 'Poland', 'PL', 'POL'),
(171, 'Portugal', 'PT', 'PRT'),
(172, 'Puerto Rico', 'PR', 'PRI'),
(173, 'Qatar', 'QA', 'QAT'),
(174, 'Reunion', 'RE', 'REU'),
(175, 'Romania', 'RO', 'ROM'),
(176, 'Russian Federation', 'RU', 'RUS'),
(177, 'Rwanda', 'RW', 'RWA'),
(178, 'Saint Kitts and Nevis', 'KN', 'KNA'),
(179, 'Saint Lucia', 'LC', 'LCA'),
(180, 'Saint Vincent and the Grenadines', 'VC', 'VCT'),
(181, 'Samoa', 'WS', 'WSM'),
(182, 'San Marino', 'SM', 'SMR'),
(183, 'Sao Tome and Principe', 'ST', 'STP'),
(184, 'Saudi Arabia', 'SA', 'SAU'),
(185, 'Senegal', 'SN', 'SEN'),
(186, 'Seychelles', 'SC', 'SYC'),
(187, 'Sierra Leone', 'SL', 'SLE'),
(188, 'Singapore', 'SG', 'SGP'),
(189, 'Slovakia (Slovak Republic)', 'SK', 'SVK'),
(190, 'Slovenia', 'SI', 'SVN'),
(191, 'Solomon Islands', 'SB', 'SLB'),
(192, 'Somalia', 'SO', 'SOM'),
(193, 'South Africa', 'ZA', 'ZAF'),
(194, 'South Georgia and the South Sandwich Islands', 'GS', 'SGS'),
(195, 'Spain', 'ES', 'ESP'),
(196, 'Sri Lanka', 'LK', 'LKA'),
(197, 'St. Helena', 'SH', 'SHN'),
(198, 'St. Pierre and Miquelon', 'PM', 'SPM'),
(199, 'Sudan', 'SD', 'SDN'),
(200, 'Suriname', 'SR', 'SUR'),
(201, 'Svalbard and Jan Mayen Islands', 'SJ', 'SJM'),
(202, 'Swaziland', 'SZ', 'SWZ'),
(203, 'Sweden', 'SE', 'SWE'),
(204, 'Switzerland', 'CH', 'CHE'),
(205, 'Syrian Arab Republic', 'SY', 'SYR'),
(206, 'Taiwan', 'TW', 'TWN'),
(207, 'Tajikistan', 'TJ', 'TJK'),
(208, 'Tanzania, United Republic of', 'TZ', 'TZA'),
(209, 'Thailand', 'TH', 'THA'),
(210, 'Togo', 'TG', 'TGO'),
(211, 'Tokelau', 'TK', 'TKL'),
(212, 'Tonga', 'TO', 'TON'),
(213, 'Trinidad and Tobago', 'TT', 'TTO'),
(214, 'Tunisia', 'TN', 'TUN'),
(215, 'Turkey', 'TR', 'TUR'),
(216, 'Turkmenistan', 'TM', 'TKM'),
(217, 'Turks and Caicos Islands', 'TC', 'TCA'),
(218, 'Tuvalu', 'TV', 'TUV'),
(219, 'Uganda', 'UG', 'UGA'),
(220, 'Ukraine', 'UA', 'UKR'),
(221, 'United Arab Emirates', 'AE', 'ARE'),
(222, 'United Kingdom', 'GB', 'GBR'),
(223, 'United States', 'US', 'USA'),
(224, 'United States Minor Outlying Islands', 'UM', 'UMI'),
(225, 'Uruguay', 'UY', 'URY'),
(226, 'Uzbekistan', 'UZ', 'UZB'),
(227, 'Vanuatu', 'VU', 'VUT'),
(228, 'Vatican City State (Holy See)', 'VA', 'VAT'),
(229, 'Venezuela', 'VE', 'VEN'),
(230, 'Viet Nam', 'VN', 'VNM'),
(231, 'Virgin Islands (British)', 'VG', 'VGB'),
(232, 'Virgin Islands (U.S.)', 'VI', 'VIR'),
(233, 'Wallis and Futuna Islands', 'WF', 'WLF'),
(234, 'Western Sahara', 'EH', 'ESH'),
(235, 'Yemen', 'YE', 'YEM'),
(236, 'Yugoslavia', 'YU', 'YUG'),
(237, 'Zaire', 'ZR', 'ZAR'),
(238, 'Zambia', 'ZM', 'ZMB'),
(239, 'Zimbabwe', 'ZW', 'ZWE'),
(240, 'Aaland Islands', 'AX', 'ALA');



INSERT INTO `zone_type` (`id`, `country_id`, `code`, `name`) VALUES
(255, 255, '--', 'No Zone'),
(1, 223, 'AL', 'Alabama'),
(2, 223, 'AK', 'Alaska'),
(3, 223, 'AS', 'American Samoa'),
(4, 223, 'AZ', 'Arizona'),
(5, 223, 'AR', 'Arkansas'),
(6, 223, 'AF', 'Armed Forces Africa'),
(7, 223, 'AA', 'Armed Forces Americas'),
(8, 223, 'AC', 'Armed Forces Canada'),
(9, 223, 'AE', 'Armed Forces Europe'),
(10, 223, 'AM', 'Armed Forces Middle East'),
(11, 223, 'AP', 'Armed Forces Pacific'),
(12, 223, 'CA', 'California'),
(13, 223, 'CO', 'Colorado'),
(14, 223, 'CT', 'Connecticut'),
(15, 223, 'DE', 'Delaware'),
(16, 223, 'DC', 'District of Columbia'),
(17, 223, 'FM', 'Federated States Of Micronesia'),
(18, 223, 'FL', 'Florida'),
(19, 223, 'GA', 'Georgia'),
(20, 223, 'GU', 'Guam'),
(21, 223, 'HI', 'Hawaii'),
(22, 223, 'ID', 'Idaho'),
(23, 223, 'IL', 'Illinois'),
(24, 223, 'IN', 'Indiana'),
(25, 223, 'IA', 'Iowa'),
(26, 223, 'KS', 'Kansas'),
(27, 223, 'KY', 'Kentucky'),
(28, 223, 'LA', 'Louisiana'),
(29, 223, 'ME', 'Maine'),
(30, 223, 'MH', 'Marshall Islands'),
(31, 223, 'MD', 'Maryland'),
(32, 223, 'MA', 'Massachusetts'),
(33, 223, 'MI', 'Michigan'),
(34, 223, 'MN', 'Minnesota'),
(35, 223, 'MS', 'Mississippi'),
(36, 223, 'MO', 'Missouri'),
(37, 223, 'MT', 'Montana'),
(38, 223, 'NE', 'Nebraska'),
(39, 223, 'NV', 'Nevada'),
(40, 223, 'NH', 'New Hampshire'),
(41, 223, 'NJ', 'New Jersey'),
(42, 223, 'NM', 'New Mexico'),
(43, 223, 'NY', 'New York'),
(44, 223, 'NC', 'North Carolina'),
(45, 223, 'ND', 'North Dakota'),
(46, 223, 'MP', 'Northern Mariana Islands'),
(47, 223, 'OH', 'Ohio'),
(48, 223, 'OK', 'Oklahoma'),
(49, 223, 'OR', 'Oregon'),
(50, 163, 'PW', 'Palau'),
(51, 223, 'PA', 'Pennsylvania'),
(52, 223, 'PR', 'Puerto Rico'),
(53, 223, 'RI', 'Rhode Island'),
(54, 223, 'SC', 'South Carolina'),
(55, 223, 'SD', 'South Dakota'),
(56, 223, 'TN', 'Tennessee'),
(57, 223, 'TX', 'Texas'),
(58, 223, 'UT', 'Utah'),
(59, 223, 'VT', 'Vermont'),
(60, 223, 'VI', 'Virgin Islands'),
(61, 223, 'VA', 'Virginia'),
(62, 223, 'WA', 'Washington'),
(63, 223, 'WV', 'West Virginia'),
(64, 223, 'WI', 'Wisconsin'),
(65, 223, 'WY', 'Wyoming'),
(66, 38, 'AB', 'Alberta'),
(67, 38, 'BC', 'British Columbia'),
(68, 38, 'MB', 'Manitoba'),
(69, 38, 'NL', 'Newfoundland'),
(70, 38, 'NB', 'New Brunswick'),
(71, 38, 'NS', 'Nova Scotia'),
(72, 38, 'NT', 'Northwest Territories'),
(73, 38, 'NU', 'Nunavut'),
(74, 38, 'ON', 'Ontario'),
(75, 38, 'PE', 'Prince Edward Island'),
(76, 38, 'QC', 'Quebec'),
(77, 38, 'SK', 'Saskatchewan'),
(78, 38, 'YT', 'Yukon Territory'),
(79, 81, 'NDS', 'Niedersachsen'),
(80, 81, 'BAW', 'Baden Würtemberg'),
(81, 81, 'BAY', 'Bayern'),
(82, 81, 'BER', 'Berlin'),
(83, 81, 'BRG', 'Brandenburg'),
(84, 81, 'BRE', 'Bremen'),
(85, 81, 'HAM', 'Hamburg'),
(86, 81, 'HES', 'Hessen'),
(87, 81, 'MEC', 'Mecklenburg-Vorpommern'),
(88, 81, 'NRW', 'Nordrhein-Westfalen'),
(89, 81, 'RHE', 'Rheinland-Pfalz'),
(90, 81, 'SAR', 'Saarland'),
(91, 81, 'SAS', 'Sachsen'),
(92, 81, 'SAC', 'Sachsen-Anhalt'),
(93, 81, 'SCN', 'Schleswig-Holstein'),
(94, 81, 'THE', 'Thringen'),
(95, 14, 'WI', 'Wien'),
(96, 14, 'NO', 'Niedersterreich'),
(97, 14, 'OO', 'Obersterreich'),
(98, 14, 'SB', 'Salzburg'),
(99, 14, 'KN', 'Kärnten'),
(100, 14, 'ST', 'Steiermark'),
(101, 14, 'TI', 'Tirol'),
(102, 14, 'BL', 'Burgenland'),
(103, 14, 'VB', 'Voralberg'),
(104, 204, 'AG', 'Aargau'),
(105, 204, 'AI', 'Appenzell Innerrhoden'),
(106, 204, 'AR', 'Appenzell Ausserrhoden'),
(107, 204, 'BE', 'Bern'),
(108, 204, 'BL', 'Basel-Landschaft'),
(109, 204, 'BS', 'Basel-Stadt'),
(110, 204, 'FR', 'Freiburg'),
(111, 204, 'GE', 'Genf'),
(112, 204, 'GL', 'Glarus'),
(113, 204, 'JU', 'Graubnden'),
(114, 204, 'JU', 'Jura'),
(115, 204, 'LU', 'Luzern'),
(116, 204, 'NE', 'Neuenburg'),
(117, 204, 'NW', 'Nidwalden'),
(118, 204, 'OW', 'Obwalden'),
(119, 204, 'SG', 'St. Gallen'),
(120, 204, 'SH', 'Schaffhausen'),
(121, 204, 'SO', 'Solothurn'),
(122, 204, 'SZ', 'Schwyz'),
(123, 204, 'TG', 'Thurgau'),
(124, 204, 'TI', 'Tessin'),
(125, 204, 'UR', 'Uri'),
(126, 204, 'VD', 'Waadt'),
(127, 204, 'VS', 'Wallis'),
(128, 204, 'ZG', 'Zug'),
(129, 204, 'ZH', 'Zrich'),
(130, 195, 'A Corua', 'A Corua'),
(131, 195, 'Alava', 'Alava'),
(132, 195, 'Albacete', 'Albacete'),
(133, 195, 'Alicante', 'Alicante'),
(134, 195, 'Almeria', 'Almeria'),
(135, 195, 'Asturias', 'Asturias'),
(136, 195, 'Avila', 'Avila'),
(137, 195, 'Badajoz', 'Badajoz'),
(138, 195, 'Baleares', 'Baleares'),
(139, 195, 'Barcelona', 'Barcelona'),
(140, 195, 'Burgos', 'Burgos'),
(141, 195, 'Caceres', 'Caceres'),
(142, 195, 'Cadiz', 'Cadiz'),
(143, 195, 'Cantabria', 'Cantabria'),
(144, 195, 'Castellon', 'Castellon'),
(145, 195, 'Ceuta', 'Ceuta'),
(146, 195, 'Ciudad Real', 'Ciudad Real'),
(147, 195, 'Cordoba', 'Cordoba'),
(148, 195, 'Cuenca', 'Cuenca'),
(149, 195, 'Girona', 'Girona'),
(150, 195, 'Granada', 'Granada'),
(151, 195, 'Guadalajara', 'Guadalajara'),
(152, 195, 'Guipuzcoa', 'Guipuzcoa'),
(153, 195, 'Huelva', 'Huelva'),
(154, 195, 'Huesca', 'Huesca'),
(155, 195, 'Jaen', 'Jaen'),
(156, 195, 'La Rioja', 'La Rioja'),
(157, 195, 'Las Palmas', 'Las Palmas'),
(158, 195, 'Leon', 'Leon'),
(159, 195, 'Lleida', 'Lleida'),
(160, 195, 'Lugo', 'Lugo'),
(161, 195, 'Madrid', 'Madrid'),
(162, 195, 'Malaga', 'Malaga'),
(163, 195, 'Melilla', 'Melilla'),
(164, 195, 'Murcia', 'Murcia'),
(165, 195, 'Navarra', 'Navarra'),
(166, 195, 'Ourense', 'Ourense'),
(167, 195, 'Palencia', 'Palencia'),
(168, 195, 'Pontevedra', 'Pontevedra'),
(169, 195, 'Salamanca', 'Salamanca'),
(170, 195, 'Santa Cruz de Tenerife', 'Santa Cruz de Tenerife'),
(171, 195, 'Segovia', 'Segovia'),
(172, 195, 'Sevilla', 'Sevilla'),
(173, 195, 'Soria', 'Soria'),
(174, 195, 'Tarragona', 'Tarragona'),
(175, 195, 'Teruel', 'Teruel'),
(176, 195, 'Toledo', 'Toledo'),
(177, 195, 'Valencia', 'Valencia'),
(178, 195, 'Valladolid', 'Valladolid'),
(179, 195, 'Vizcaya', 'Vizcaya'),
(180, 195, 'Zamora', 'Zamora'),
(181, 195, 'Zaragoza', 'Zaragoza'),
(182, 13, 'ACT', 'Australian Capital Territory'),
(183, 13, 'NSW', 'New South Wales'),
(184, 13, 'NT', 'Northern Territory'),
(185, 13, 'QLD', 'Queensland'),
(186, 13, 'SA', 'South Australia'),
(187, 13, 'TAS', 'Tasmania'),
(188, 13, 'VIC', 'Victoria'),
(189, 13, 'WA', 'Western Australia');


-- now we need some views for order totals, shipping, tax etc ..
/*
CREATE IF NOT EXISTS VIEW order_totals AS (
    SELECT sum(p.retail_price) AS gross,
                     sum(p.cost) AS total_cost,
                     sum(p.weight) AS total_weight,
                     o.shipping_rate,
                     o.tax_rate,
                     (p.weight * o.shipping rate) AS shipping_cost
        FROM order_product op
        LEFT JOIN product p ON p.id = op.id
        LEFT JOIN order o ON op.order_id = o.order_id
        GROUP BY o.order_id
        GROUP BY p.id
        
);
*/
