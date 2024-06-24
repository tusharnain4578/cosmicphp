CREATE TABLE IF NOT EXISTS migrations (
  `id` bigint UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `batch` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
);