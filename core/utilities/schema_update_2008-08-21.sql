ALTER TABLE `quasicms`.`paypal_transaction`
 DROP FOREIGN KEY `paypal_transaction_ibfk_1`;

ALTER TABLE `quasicms`.`paypal_transaction` ADD CONSTRAINT `paypal_transaction_ibfk_1` FOREIGN KEY `paypal_transaction_ibfk_1` (`order_id`)
    REFERENCES `order` (`id`)
    ON DELETE CASCADE
    ON UPDATE RESTRICT;
