-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_vip_groups'
-- --------------------------------------------------------
CREATE TABLE `glpi_plugin_vip_groups` (
  `id` int(11) NOT NULL default 0 COMMENT 'RELATION to glpi_groups(id)',
  `isvip` tinyint(1) default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_vip_groups` (`id`, `isvip`) VALUES ('0', '0');
