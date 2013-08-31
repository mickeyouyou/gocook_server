ALTER TABLE user ADD COLUMN msix_id int;
ALTER TABLE user ADD COLUMN msix_token varchar(22);
ALTER TABLE user ADD UNIQUE KEY (`msix_id`);
ALTER TABLE user ADD UNIQUE KEY (`tel`);