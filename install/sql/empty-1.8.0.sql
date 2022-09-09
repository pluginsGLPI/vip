-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_vip_groups'
-- --------------------------------------------------------
CREATE TABLE `glpi_plugin_vip_groups` (
  `id` int unsigned NOT NULL default 0 COMMENT 'RELATION to glpi_groups(id)',
  `name` varchar(100) DEFAULT 'VIP',
  `isvip` tinyint default '0',
  `vip_color` varchar(10) DEFAULT '#ff0000' NOT NULL,
  `vip_icon` varchar(100) DEFAULT 'fa-exclamation-triangle',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `glpi_plugin_vip_groups` (`id`, `isvip`) VALUES ('0', '0');
