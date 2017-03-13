-- CREATE TABLES
CREATE TABLE `Groups` (
    `group_pk` BIGINT UNSIGNED NOT NULL,
    `group_name` VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL,
    UNIQUE (`group_pk`),
    INDEX (`group_pk`),
    INDEX (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `Users` (
    `user_pk` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_name` VARCHAR(150) COLLATE utf8_unicode_ci NOT NULL,
    `user_password` VARCHAR(2048) COLLATE utf8_unicode_ci NOT NULL,
    `user_salt` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `user_email_address` VARCHAR(255) COLLATE utf8_unicode_ci,
    `group_fk` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`user_pk`),
    INDEX (`user_name`),
    INDEX (`user_email_address`),
    INDEX (`group_fk`),
    CONSTRAINT `Users_group_fk` FOREIGN KEY (`group_fk`) REFERENCES `Groups` (`group_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `RememberMe` (
    `rememberme_pk` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_fk` BIGINT UNSIGNED NOT NULL,
    `token` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
    `ip_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`rememberme_pk`),
    INDEX (`user_fk`),
    INDEX (`ip_address`),
    CONSTRAINT `RememberMe_user_fk` FOREIGN KEY (`user_fk`) REFERENCES `Users` (`user_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `Files` (
    `file_pk` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `file_name` VARCHAR(150) COLLATE utf8_unicode_ci NOT NULL,
    `file_visible` BOOLEAN NOT NULL DEFAULT TRUE,
    `file_created_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_fk` BIGINT UNSIGNED NOT NULL,
    `file_downloads` BIGINT UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`file_pk`),
    INDEX (`file_name`),
    INDEX (`file_visible`),
    INDEX (`user_fk`),
    CONSTRAINT `Files_user_fk` FOREIGN KEY (`user_fk`) REFERENCES `Users` (`user_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `RevisionStatus` (
    `rev_status_pk` TINYINT UNSIGNED NOT NULL,
    `status` VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL,
    UNIQUE (`rev_status_pk`),
    INDEX (`rev_status_pk`),
    INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `Revisions` (
    `rev_pk` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `file_fk` BIGINT UNSIGNED NOT NULL,
    `rev_file_name` VARCHAR(150) COLLATE utf8_unicode_ci NOT NULL,
    `rev_file_path` VARCHAR(512) COLLATE utf8_unicode_ci NOT NULL,
    `rev_file_version` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `rev_file_description_short` VARCHAR(512) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    `rev_file_description` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    `rev_upload_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `rev_status_fk` TINYINT UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`rev_pk`),
    INDEX (`file_fk`),
    INDEX (`rev_file_name`),
    INDEX (`rev_status_fk`),
    CONSTRAINT `Revisions_file_fk` FOREIGN KEY (`file_fk`) REFERENCES `Files` (`file_pk`),
    CONSTRAINT `Revisions_rev_status_fk` FOREIGN KEY (`rev_status_fk`) REFERENCES `RevisionStatus` (`rev_status_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `Screenshots` (
    `screen_pk` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `rev_fk` BIGINT UNSIGNED NOT NULL,
    `screen_title` VARCHAR(512) COLLATE utf8_unicode_ci NOT NULL,
    `screen_alt` VARCHAR(150) COLLATE utf8_unicode_ci NOT NULL,
    `screen_filename` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `screen_path` VARCHAR(512) COLLATE utf8_unicode_ci NOT NULL,
    `screen_order` VARCHAR(512) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`screen_pk`),
    INDEX (`rev_fk`),
    CONSTRAINT `Screenshots_rev_fk` FOREIGN KEY (`rev_fk`) REFERENCES `Revisions` (`rev_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `Comments` (
    `comment_pk` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `comment_title` VARCHAR(512) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    `comment_text` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    `comment_parent_fk` BIGINT UNSIGNED NULL DEFAULT NULL,
    `user_fk` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`comment_pk`),
    INDEX (`comment_title`),
    INDEX (`comment_parent_fk`),
    INDEX (`user_fk`),
    CONSTRAINT `Comments_comment_parent_fk` FOREIGN KEY (`comment_parent_fk`) REFERENCES `Comments` (`comment_pk`),
    CONSTRAINT `Comments_user_fk` FOREIGN KEY (`user_fk`) REFERENCES `Users` (`user_pk`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `Ratings` (
    `rating_pk` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `file_fk` BIGINT UNSIGNED NOT NULL,
    `rating_amount` TINYINT UNSIGNED NOT NULL,
    `rating_ip` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`rating_pk`),
    INDEX (`file_fk`),
    INDEX (`rating_amount`),
    INDEX (`rating_ip`),
    CONSTRAINT `Ratings_file_fk` FOREIGN KEY (`file_fk`) REFERENCES `Files` (`file_pk`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `FlagStatus` (
    `flag_status_pk` TINYINT UNSIGNED NOT NULL,
    `status` VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL,
    UNIQUE (`flag_status_pk`),
    INDEX (`flag_status_pk`),
    INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `Flags` (
    `flag_pk` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `rev_fk` BIGINT UNSIGNED NOT NULL,
    `flag_status_fk` TINYINT UNSIGNED NOT NULL,
    `flag_assigned_user_fk` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`flag_pk`),
    INDEX (`rev_fk`),
    INDEX (`flag_status_fk`),
    INDEX (`flag_assigned_user_fk`)
    CONSTRAINT `Flags_rev_fk` FOREIGN KEY (`rev_fk`) REFERENCES `Revisions` (`rev_pk`),
    CONSTRAINT `Flags_flag_status_fk` FOREIGN KEY (`flag_status_fk`) REFERENCES `FlagStatus` (`flag_status_pk`),
    CONSTRAINT `Flags_flag_assigned_user_fk` FOREIGN KEY (`flag_assigned_user_fk`) REFERENCES `Users` (`user_pk`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

-- INSERT INITIAL DATA
INSERT INTO
    `Groups` (`group_pk`, `group_name`)
VALUES
    (1, 'User'),
    (5, 'Contributor'),
    (9, 'Moderator'),
    (10, 'Administrator');

INSERT INTO
    `RevisionStatus` (`rev_status_pk`, `status`)
VALUES
    (0, 'Queued'),
    (1, 'Current'),
    (2, 'Refused'),
    (3, 'Disabled'),
    (4, 'Removed');

INSERT INTO
    `FlagStatus` (`flag_status_pk`, `status`)
VALUES
    (0, 'Open'),
    (1, 'Assigned'),
    (2, 'Closed');
