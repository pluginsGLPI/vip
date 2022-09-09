-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_vip_profiles'
-- --------------------------------------------------------
CREATE TABLE `glpi_plugin_vip_profiles` (
   `id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   `show_vip_tab` tinyint(1) collate utf8_unicode_ci default NULL,
   PRIMARY KEY  (`id`)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_vip_groups'
-- --------------------------------------------------------
CREATE TABLE `glpi_plugin_vip_groups` (
  `id` int(11) NOT NULL default 0 COMMENT 'RELATION to glpi_groups(id)',
  `isvip` tinyint(1) default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `glpi_plugin_vip_groups` (`id`, `isvip`) VALUES ('0', '0');

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_vip_tickets'
-- --------------------------------------------------------
CREATE TABLE glpi_plugin_vip_tickets (
  `id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_tickets (id)',
  `isvip` tinyint(1) default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;