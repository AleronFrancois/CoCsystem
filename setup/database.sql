CREATE TABLE `User` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `role` ENUM('supervisor', 'investigator') NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(60) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `Case` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL,
  `creation_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `close_date` DATETIME,
  `description` VARCHAR(1024),
  `creator_id` INT NOT NULL,
  FOREIGN KEY (`creator_id`) REFERENCES `User`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `Evidence` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `approved` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `locked` BOOLEAN NOT NULL DEFAULT FALSE,
  `uploader_id` INT NOT NULL,
  FOREIGN KEY (`uploader_id`) REFERENCES `User`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `EvidenceCustodyAction` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `timestamp` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `action` ENUM('download', 'upload', 'lock', 'unlock', 'comment', 'assign', 'rehash', 'unassign') NOT NULL,
  `description` TEXT NOT NULL,
  `evidence_hash` CHAR(64) NOT NULL,
  `action_hash` CHAR(64) NOT NULL,
  `user_id` INT NOT NULL,
  `evidence_id` INT NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `User`(`id`),
  FOREIGN KEY (`evidence_id`) REFERENCES `Evidence`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `Metadata` (
  `evidence_id` INT NOT NULL,
  `key` VARCHAR(64) NOT NULL,
  `value` VARCHAR(1024) NOT NULL,
  PRIMARY KEY (`evidence_id`, `key`),
  FOREIGN KEY (`evidence_id`) REFERENCES `Evidence`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `CaseCustodyAction` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `timestamp` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `action` ENUM('create', 'close', 'reopen', 'assign', 'unassign') NOT NULL,
  `user_id` INT NOT NULL,
  `case_id` INT NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `User`(`id`),
  FOREIGN KEY (`case_id`) REFERENCES `Case`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `Case_User` (
  `case_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  PRIMARY KEY (`case_id`, `user_id`),
  FOREIGN KEY (`case_id`) REFERENCES `Case`(`id`),
  FOREIGN KEY (`user_id`) REFERENCES `User`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `Comment` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `timestamp` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  `content` VARCHAR(512) NOT NULL,
  `commenter_id` INT NOT NULL,
  `case_id` INT NOT NULL,
  `evidence_id` INT NOT NULL,
  FOREIGN KEY (`commenter_id`) REFERENCES `User`(`id`),
  FOREIGN KEY (`case_id`) REFERENCES `Case`(`id`),
  FOREIGN KEY (`evidence_id`) REFERENCES `Evidence`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `Case_Evidence` (
  `case_id` INT NOT NULL,
  `evidence_id` INT NOT NULL,
  PRIMARY KEY (`case_id`, `evidence_id`),
  FOREIGN KEY (`case_id`) REFERENCES `Case`(`id`),
  FOREIGN KEY (`evidence_id`) REFERENCES `Evidence`(`id`)
) ENGINE=InnoDB;
