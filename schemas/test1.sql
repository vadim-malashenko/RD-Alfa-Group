CREATE DATABASE test1;

USE test1;

CREATE TABLE `hashes` (
    `id` int NOT NULL AUTO_INCREMENT,
    `hash` varchar(8) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `tokens` (
    `id` int NOT NULL AUTO_INCREMENT,
    `token` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `token_hashes` (
    `token_id` int NOT NULL,
    `hash_id` int NOT NULL,
    UNIQUE KEY `hash_id` (`hash_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
