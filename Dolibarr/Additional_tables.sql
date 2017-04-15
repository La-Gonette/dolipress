--
-- Table structure for table `llx_touched_partners`
--

CREATE TABLE IF NOT EXISTS `llx_touched_partners` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `partner_code` varchar(10) NOT NULL,
  `modification_datetime` datetime NOT NULL,
  PRIMARY KEY (`rowid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- Table structure for table `llx_partners_markets`
--

CREATE TABLE IF NOT EXISTS `llx_partners_markets` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `opening_hours` varchar(255) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `gpscoords` varchar(50) NOT NULL,
  PRIMARY KEY (`rowid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=35 ;

--
-- Dumping data for table `llx_partners_markets`
--

INSERT INTO `llx_partners_markets` (`rowid`, `name`, `opening_hours`, `icon`, `address`, `gpscoords`) VALUES
(1, 'Marché bio de Jassans Riottier', 'Les mercredi de 15 à 19h', 'Picto-22.png', 'Place de Limelette, 01480 Jassans-Riottier', ''),
(2, 'Marché de Tarare', 'Les samedi matin', 'Picto-22.png', 'Place du Marché, 69170 Tarare', ''),
(3, 'Marché de Sainte Foy l''argentière', 'Les samedi de 8h00 à 13h', 'Picto-22.png', 'Rue du Marché, 69610 Sainte-Foy-l''Argentière', ''),
(4, 'Marché bio des clarines', 'Les samedi de 9h à 13h', 'Picto-22.png', 'Thurigneux, 69440 Saint-Maurice-sur-Dargoire', ''),
(5, 'Marché bio du Chapi', 'Les vendredi de 16h30 à 19h', 'Picto-22.png', '69510 Soucieu-en-Jarrest', ''),
(6, 'Marché de Vaugneray', 'Les mardi de 7h30 à 12h30', 'Picto-22.png', 'Place du Marché, 69670 Vaugneray', ''),
(7, 'Marché Bio de Grezieu la Varenne', 'Les vendredi de 14h à 19h', 'Picto-22.png', '69290 Grézieu la Varenne', ''),
(8, 'Marché de lentilly', 'Les mercredi et dimanche, de 8h à 12h (13h le dimanche)', 'Picto-22.png', '69210 Lentilly', ''),
(9, 'Marché bio de Collonge aux monts d''or', 'Les vendredi de 16h30 à 19h30', 'Picto-22.png', 'Rue de la Plage, 69660 Collonges-au-Mont-d''Or', ''),
(10, 'Marché de Caluire', 'Les samedi de 7h30 à 12h30', 'Picto-22.png', 'Allée du Parc de la jeunesse, 69300 Caluire-et-Cuire', ''),
(11, 'Marché bio de la Croix Rousse', 'Les samedi de 6h à 13h30', 'Picto-22.png', '73 rue de Belfort, 69004 Lyon', ''),
(12, 'Marché bio de Vaise', 'Les mardi de 6 à 13h', 'Picto-22.png', '2 Rue du Sergent Michel Berthet, 69009 Lyon', ''),
(13, 'Marché de Tassin la demi lune', 'Les vendredi de 7h30 à 12h30', 'Picto-22.png', 'Promenade des Tuileries, 69160 Tassin-la-Demi-Lune', ''),
(14, 'Marché bio Monplaisir', 'Les mercredi de 15h à 20h', 'Picto-22.png', 'Place Ambroise Courtois, 69008 Lyon', ''),
(15, 'Marché Saint Louis', 'Les dimanche de 6h30 à 13h30', 'Picto-22.png', 'Place Saint-Louis, 69007 Lyon', ''),
(16, 'Marché Jean Macé', 'Les mercredi et samedi de 6h à 13h30', 'Picto-22.png', 'Place Jean Macé, 69007 Lyon', ''),
(18, 'Marché bio de Villefranche', 'Les samedi, de 7h30 à 13h', 'Picto-22.png', 'Place du 11 novembre 1918, 69400 Villefranche-sur-Saone', '');

-- --------------------------------------------------------

--
-- Table structure for table `llx_partner_categories`
--

CREATE TABLE IF NOT EXISTS `llx_partner_categories` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `partner_category` varchar(50) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `icon` varchar(50) NOT NULL,
  `display_order` int(11) NOT NULL,
  `hidden` tinyint(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rowid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

--
-- Dumping data for table `llx_partner_categories`
--

INSERT INTO `llx_partner_categories` (`rowid`, `partner_category`, `active`, `icon`, `display_order`, `hidden`) VALUES
(1, 'Artisanat, métiers d''art', 1, 'Picto-3.png', 20, 0),
(2, 'Autres commerces', 1, 'Picto-8.png', 70, 0),
(3, 'Commerces alimentaires', 1, 'Picto-2.png', 10, 0),
(4, 'Décoration, ameublement, bricolage, jardin', 1, 'Picto-7.png', 60, 0),
(5, 'Divers', 1, 'Picto-21.png', 210, 0),
(6, 'Education, formation', 1, 'Picto-16.png', 150, 0),
(7, 'Habillement, mode, accessoires', 1, 'Picto-4.png', 30, 0),
(8, 'Hébergement', 1, 'Picto-18.png', 170, 0),
(9, 'Hygiène, beauté', 1, 'Picto-5.png', 40, 0),
(10, 'Immobilier', 1, 'Picto-19.png', 180, 0),
(11, 'Informatique, électronique', 1, 'Picto-10.png', 90, 0),
(12, 'Mécanique, réparation', 1, 'Picto-12.png', 110, 0),
(13, 'Papeterie, librairie, presse, édition', 1, 'Picto-6.png', 50, 0),
(14, 'Produits de l''agriculture et de l''élevage', 1, 'Picto-17.png', 160, 0),
(15, 'Restaurants, Bars, Traiteurs', 1, 'Picto-1.png', 0, 0),
(16, 'Santé, Bien-être', 1, 'Picto-11.png', 100, 0),
(17, 'Services à la personne', 1, 'Picto-20.png', 190, 0),
(18, 'Services aux entreprises', 1, '', 200, 0),
(19, 'Sorties culturelles', 1, 'Picto-9.png', 80, 0),
(20, 'Sport et loisirs', 1, 'Picto-15.png', 140, 0),
(21, 'Transport, livraison', 1, 'Picto-13.png', 120, 0),
(22, 'Web, multimédia, communication, imprimerie', 1, 'Picto-14.png', 130, 0);

-- --------------------------------------------------------

--
-- Table structure for table `llx_societe_extrafields`
--

CREATE TABLE IF NOT EXISTS `llx_societe_extrafields` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `tms` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fk_object` int(11) NOT NULL,
  `import_key` varchar(14) DEFAULT NULL,
  `description` text,
  `openinghours` text,
  `exchangeoffice` int(1) DEFAULT NULL,
  `publishedpartner` int(1) DEFAULT NULL,
  `shortdescription` varchar(255) DEFAULT NULL,
  `bypasscoordinatescalc` int(1) DEFAULT NULL,
  `gpscoords` varchar(50) DEFAULT NULL,
  `maincategory` text NOT NULL,
  `sidecategories` text,
  `marketpresence` text,
  PRIMARY KEY (`rowid`),
  UNIQUE KEY `uk_societe_extrafields` (`fk_object`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=176 ;
