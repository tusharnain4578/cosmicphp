CREATE TABLE IF NOT EXISTS news (
  `id` bigint UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `news` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);