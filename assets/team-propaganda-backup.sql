-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Počítač: localhost
-- Vytvořeno: Čtv 26. bře 2026, 09:43
-- Verze serveru: 12.2.2-MariaDB
-- Verze PHP: 8.5.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `team-propaganda`
--
CREATE DATABASE IF NOT EXISTS `team-propaganda` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `team-propaganda`;

-- --------------------------------------------------------

--
-- Struktura tabulky `email_send`
--

CREATE TABLE IF NOT EXISTS `email_send` (
  `id_email_send` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `send` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id_email_send`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `email_send_user`
--

CREATE TABLE IF NOT EXISTS `email_send_user` (
  `id_users` int(10) UNSIGNED NOT NULL,
  `id_email_send` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_users`,`id_email_send`),
  KEY `id_email_send` (`id_email_send`),
  KEY `id_users` (`id_users`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `schools`
--

CREATE TABLE IF NOT EXISTS `schools` (
  `id_schools` int(16) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id_schools`)
) ENGINE=InnoDB AUTO_INCREMENT=278 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `schools`
--

INSERT INTO `schools` (`id_schools`, `name`) VALUES
(1, 'Adamov Komenského 324/4 679 04 Adamov'),
(2, 'Antonínská 550/3 602 00 Brno – Veveří'),
(3, 'Archlebov 357 696 33 Archlebov'),
(4, 'Arménská 573/21 625 00 Brno – Bohunice'),
(5, 'Bakalovo nábřeží 8/8 639 00 Brno - Štýřice'),
(6, 'Bedihošť Komenského 86 798 21 Bedihošť'),
(7, 'Bednářova 496/28 619 00 Brno - Horní Heršpice'),
(8, 'Benešov 155 679 53 Benešov'),
(9, 'Bílovice nad Svitavou Komenského 151 664 01'),
(10, 'Blansko Erbenova 1237/13 678 01 Blansko'),
(11, 'Blansko Dvorská 1415/26 678 01'),
(12, 'Blansko Rodkovského 822/2 678 01 Blansko'),
(13, 'Blansko Salmova 1940/17 678 01 Blansko'),
(14, 'Blatnice pod Svatým Antonínkem 650 696 71 Blatnice pod Svatým Antonínkem'),
(15, 'Blažkova 9 638 00 Brno'),
(16, 'Bohdalice-Pavlovice 1 683 41 Bohdalice-Pavlovice'),
(17, 'Bosonožská 381/9 625 00 Brno - Starý Lískovec'),
(18, 'Božice 393 671 64 Božice'),
(19, 'Brankovice Tasova 272 683 33 Brankovice'),
(20, 'Brodek u Konice 265 798 46 Brodek u Konice'),
(21, 'Brodek u Prostějova Císařská 65 798 07 Brodek u Prostějova'),
(22, 'Brumov 51 679 23 Brumov'),
(23, 'Břeclav Herbenova 2969/4 690 03 Břeclav'),
(24, 'Břeclav Komenského 60/2 691 41 Břeclav'),
(25, 'Břeclav Kpt. Nálepky 186/7 690 06 Břeclav'),
(26, 'Břeclav Kupkova 1020/1 690 02 Břeclav'),
(27, 'Břeclav Na Valtické 641/31a 691 41 Břeclav'),
(28, 'Břeclav Slovácká 2853/40 690 02 Břeclav'),
(29, 'Březí Školní 194 691 81 Březí'),
(30, 'Březová nad Svitavou Moravské náměstí 15 569 02 Březová nad Svitavou'),
(31, 'Bučovice Školní 710 685 01 Bučovice'),
(32, 'Bučovice Školní 711 685 01 Bučovice'),
(33, 'Bystré Školní 24 569 92 Bystré'),
(34, 'Bystřice nad Pernštejnem Nádražní 615 596 01 Bystřice nad Pernštejnem'),
(35, 'Bystřice nad Pernštejnem Tyršova 409 593 01 Bystřice nad Pernštejnem'),
(36, 'Bzenec Olšovská 1428 696 81 Bzenec'),
(37, 'Čejkovice Školní 800 696 15 Čejkovice'),
(38, 'Čejkovická 4339/10 628 00 Brno – Židenice'),
(39, 'Černá Hora Strmá 308 679 21 Černá Hora'),
(40, 'Černovice 97 679 75 Černovice'),
(41, 'Dambořice Farní 466 696 35 Dambořice'),
(42, 'Deblín 277 664 75 Deblín'),
(43, 'Deštná 60 679 61 Deštná'),
(44, 'Dolní Bojanovice Školní 195 696 17 Dolní Bojanovice'),
(45, 'Dolní Dunajovice Hlavní 82 691 85 Dolní Dunajovice'),
(46, 'Dolní Kounice Smetanova 547/2 664 64 Dolní Kounice'),
(47, 'Dolní Loučky 207 594 55 Dolní Loučky'),
(48, 'Dolní Věstonice 84 691 29 Dolní Věstonice'),
(49, 'Drásov 167 664 24 Drásov'),
(50, 'Drnholec Svatoplukova 277/2 691 83 Drnholec'),
(51, 'Drnovice 109 683 04 Drnovice'),
(52, 'Dubňany Hodonínská 925 696 03 Dubňany'),
(53, 'Elišky Přemyslovny 497/10 625 00 Brno - Starý Lískovec'),
(54, 'Gajdošova 1282/3 615 00 Brno - Židenice'),
(55, 'Hamry 576/12 614 00 Brno - Maloměřice'),
(56, 'Herčíkova 2499/19 612 00 Brno - Královo Pole'),
(57, 'Hevlín 225 671 69 Hevlín'),
(58, 'Heyrovského 611/32 635 00 Brno - Bystrc'),
(59, 'Heyrovského 828/13 635 00 Brno – Bystrc Pramínek'),
(60, 'Hodějice 230, 684 01 Hodějice'),
(61, 'Hodonín Mírové nám. 2244/19 695 01 Hodonín'),
(62, 'Hodonín Očovská 3835/1 695 01 Hodonín'),
(63, 'Hodonín U Červených domků 3206/40 695 01 Hodonín'),
(64, 'Hodonín Vančurova 3423/2 695 01 Hodonín'),
(65, 'Holzova 1461/1 628 00 Brno – Líšeň'),
(66, 'Horácké náměstí 1493/13 621 00 Brno – Řečkovice'),
(67, 'Horní 742/16 639 00 Brno – Štýřice'),
(68, 'Horníkova 1 Horníkova 2170/1 628 00 Brno - Líšeň'),
(69, 'Hovorany 594 696 12 Hovorany'),
(70, 'Hroznová 65/1 603 00 Brno - Pisárky'),
(71, 'Hroznová Lhota 318 696 63 Hroznová Lhota'),
(72, 'Hrušovany nad Jevišovkou Nádražní 461 671 67 Hrušovany nad Jevišovkou'),
(73, 'Hudcova 81/35 621 00 Brno – Medlánky'),
(74, 'Husova 219/17 602 00 Brno - Brno-město'),
(75, 'Hustopeče Nádražní 175/4 693 01 Hustopeče'),
(76, 'Chalabalova 575/2 623 00 Brno - Kohoutovice'),
(77, 'Ivančice Růžová 149/7 664 91'),
(78, 'Ivančice Na Brněnce 545/1 664 91 Ivančice'),
(79, 'Ivanovice na Hané Tyršova 218/4 683 23 Ivanovice na Hané'),
(80, 'Jana Babáka 1960/1 616 00 Brno – Žabovřesky'),
(81, 'Jana Broskvy 388/3 643 00 Brno - Chrlice'),
(82, 'Janouškova 577/2 613 00 Brno - Černá Pole'),
(83, 'Jaroměřice 310 569 44 Jaroměřice'),
(84, 'Jaroslavice Školní 83 671 28 Jaroslavice'),
(85, 'Jasanová 647/2 637 00 Brno - Jundrov'),
(86, 'Jedovnice Nad Rybníkem 401 679 06 Jedovnice'),
(87, 'Jevíčko U Zámečku 784 569 43 Jevíčko'),
(88, 'Jihomoravské náměstí 1089/2 627 00 Brno - Slatina'),
(89, 'Kamenačky 3591/4 636 00 Brno – Židenice'),
(90, 'Kamínky 368/5 634 00 Brno - Nový Lískovec'),
(91, 'Kanice 135 664 01 Kanice'),
(92, 'Klobouky u Brna Vinařská 719/29 691 72 Klobouky u Brna'),
(93, 'Kneslova 697/28 618 00 Brno - Černovice'),
(94, 'Knínice 210 679 34 Knínice'),
(95, 'Kobylí 661 691 10 Kobylí'),
(96, 'Kostelec na Hané Sportovní 850 798 41 Kostelec na Hané'),
(97, 'Košinova 661/22 612 00 Brno - Královo Pole'),
(98, 'Kotlářská 4 Kotlářská 655/4 602 00 Brno - Veveří'),
(99, 'Krásného 3191/24 636 00 Brno – Židenice'),
(100, 'Kroměříž Komenského náměstí 440 767 01 Kroměříž'),
(101, 'Kroměříž Zeyerova 3354 767 01 Kroměříž'),
(102, 'Křenová 99/21 602 00 Brno - Trnitá'),
(103, 'Křenovice Školní 140 683 52 Křenovice'),
(104, 'Křídlovická 513/30b 603 00 Brno - Staré Brno'),
(105, 'Křtiny 240 679 05 Křtiny'),
(106, 'Kuldova 734/38 615 00 Brno - Zábrdovice'),
(107, 'Kunštát Brněnská 32 679 72'),
(108, 'Kuřim Jungmannova 813/5 664 34'),
(109, 'Kuřim Tyršova 1255/56 664 34 Kuřim'),
(110, 'Kuželov 1 696 73 Kuželov'),
(111, 'Kyjov - Bohuslavice 4177 696 55 Kyjov – Bohuslavice'),
(112, 'Kyjov Sídliště U Vodojemu 1261/18 697 01 Kyjov'),
(113, 'Kyjov Újezd 990/2 697 01 Kyjov'),
(114, 'Labská 269/27 625 00 Brno - Starý Lískovec'),
(115, 'Lanžhot Masarykova 730/22 691 51 Lanžhot'),
(116, 'Laštůvkova 920/77 635 00 Brno - Bystrc'),
(117, 'Lednice Břeclavská 510 691 44 Lednice'),
(118, 'Letovice Komenského 902/5 679 61 Letovice'),
(119, 'Lipov 199 696 72 Lipov'),
(120, 'Lipovec 167 679 15 Lipovec'),
(121, 'Lipůvka 283 679 22 Lipůvka'),
(122, 'Loděnice 134 671 75 Loděnice'),
(123, 'Lomnice Tišnovská 362 679 23'),
(124, 'Lužice Velkomoravská 220/264 696 18 Lužice'),
(125, 'Lysice Zákostelí 360 679 71 Lysice'),
(126, 'Masarova 2360/11 628 00 Brno – Líšeň'),
(127, 'Masarykova 178 691 25 Vranovice'),
(128, 'Medlov 12 664 66 Medlov'),
(129, 'Měnín 32 664 57 Měnín'),
(130, 'Merhautova 37 Merhautova 932/37 613 00 Brno - Černá Pole'),
(131, 'Měšťanská 459/21 620 00 Brno - Brněnské Ivanovice'),
(132, 'Mikulčice 555 696 19 Mikulčice'),
(133, 'Mikulov Hraničářů 69/617e 692 01 Mikulov'),
(134, 'Mikulov Valtická 845/3 692 01 Mikulov'),
(135, 'Milénova 808/14 638 00 Brno - Lesná'),
(136, 'Milotice Školní 375 696 05 Milotice'),
(137, 'Miroslav Třináctky 135/19 671 72 Miroslav'),
(138, 'Modřice Benešova 332 664 42'),
(139, 'Mokrá-Horákov Mokrá 352 664 04'),
(140, 'Moravská Chrastová 100 596 04 Brněnec'),
(141, 'Moravská Nová Ves Školní 396 691 55 Moravská Nová Ves'),
(142, 'Moravská Třebová Kostelní náměstí 21/2, Město 571 01 Moravská Třebová'),
(143, 'Moravský Krumlov Ivančická 218 672 01 Moravský Krumlov'),
(144, 'Moravský Krumlov Náměstí Klášterní 134 672 01 Moravský Krumlov'),
(145, 'Moravský Písek Velkomoravská 168 696 85 Moravský Písek'),
(146, 'Moravský Žižkov Bílovská 78 691 01 Moravský Žižkov'),
(147, 'Mutěnice Brněnská 777 696 11 Mutěnice'),
(148, 'Mutěnická 4164/23 628 00 Brno – Židenice'),
(149, 'nám. 28. října 22 náměstí 28. října 1902/22 602 00 Brno - Černá Pole'),
(150, 'náměstí Míru 375/3 602 00 Brno – Stránice'),
(151, 'náměstí Republiky 1536/10 614 00 Brno - Husovice'),
(152, 'náměstí Svornosti 2571/7 616 00 Brno - Žabovřesky'),
(153, 'Náměšť nad Oslavou Komenského 53 675 71 Náměšť nad Oslavou'),
(154, 'Nedvědice 80 592 62 Nedvědice'),
(155, 'Němčice nad Hanou Tyršova 360 798 27 Němčice nad Hanou'),
(156, 'Nenkovice 222 696 37 Nenkovice'),
(157, 'Nezamyslice 1.Máje 234 798 26 Nezamyslice'),
(158, 'Nikolčice 79 691 71 Nikolčice'),
(159, 'Novolíšeňská 2411/10 628 00 Brno - Líšeň'),
(160, 'Novoměstská 1887/21 621 00 Brno – Řečkovice'),
(161, 'Olbramovice 125 671 76 Olbramovice'),
(162, 'Olešnice Hliníky 108 679 74'),
(163, 'Olšany u Prostějova 3 798 14 Olšany u Prostějova'),
(164, 'Ořechov Komenského 703/2 664 44 Ořechov'),
(165, 'Oslavany Hlavní 850/43 664 12'),
(166, 'Osová Bítýška 246 594 53 Osová Bítýška'),
(167, 'Ostrov u Macochy 363 679 14 Ostrov u Macochy'),
(168, 'Otaslavice Sýpky 117 798 06 Otaslavice'),
(169, 'Otevřená 986/20a 641 00 Brno-Žebětín'),
(170, 'Otnice Školní 352 683 54 Otnice'),
(171, 'Pastviny 718/70 624 00 Brno - Komín'),
(172, 'Pavlovská 576/16 623 00 Brno - Kohoutovice'),
(173, 'Plumlov Rudé armády 300 798 03 Plumlov'),
(174, 'Podivín Masarykovo nám. 230/23 691 45 Podivín'),
(175, 'Podomí 155 683 04 Podomí'),
(176, 'Pohořelice Dlouhá 35 691 23'),
(177, 'Polešovice 600, 687 37 Polešovice'),
(178, 'Pozořice U školy 386 664 07'),
(179, 'Prosiměřice 151 671 61 Prosiměřice'),
(180, 'Prostějov Dr. Horáka 24 796 01 Prostějov'),
(181, 'Prostějov Edvarda Valenty 3970/52 796 03 Prostějov'),
(182, 'Prostějov Melantrichova 60 796 01 Prostějov'),
(183, 'Prostějov Palackého tř. 14 796 01 Prostějov'),
(184, 'Prostějov Sídliště Svobody 3578/79 796 01 Prostějov'),
(185, 'Prostějov Vl. Majakovského 131/1 798 11 Prostějov'),
(186, 'Protivanov Školní 292 798 48 Protivanov'),
(187, 'Prušánky 289 696 21 Prušánky'),
(188, 'Přemyslovo náměstí 89/1 627 00 Brno - Slatina'),
(189, 'Pustiměř 207 683 21 Pustiměř'),
(190, 'Rájec-Jestřebí Školní 446 679 02 Rájec-Jestřebí'),
(191, 'Rajhrad Havlíčkova 452 664 61'),
(192, 'Rakvice Horní 566 691 03 Rakvice'),
(193, 'Ratíškovice Vítězná 701 696 02 Ratíškovice'),
(194, 'Rohatec Školní 742/50 696 01 Rohatec'),
(195, 'Rosice Pod Zahrádkami 120 665 01'),
(196, 'Rousínov Habrovanská 312/3 683 01 Rousínov'),
(197, 'Řehořova 1020/3 618 00 Brno – Černovice'),
(198, 'Sadová 530 664 43 Želešice'),
(199, 'Sirotkova 371/36 616 00 Brno – Žabovřesky'),
(200, 'Slavkov u Brna Komenského náměstí 495 684 01 Slavkov u Brna'),
(201, 'Slavkov u Brna Tyršova 977 684 01 Slavkov u Brna'),
(202, 'Sloup 200 679 13 Sloup'),
(203, 'Slovanské náměstí 1218/2 612 00 Brno - Královo Pole'),
(204, 'Sokolnice Masarykova 20 664 52'),
(205, 'Staňkova 327/14 602 00 Brno - Ponava'),
(206, 'Strážnice Příční 1365 696 62 Strážnice'),
(207, 'Strážnice Školní 283 696 62 Strážnice'),
(208, 'Střelice Komenského 2/585 664 47 Střelice'),
(209, 'Svatobořice-Mistřín Hlavní 871/198 696 04 Svatobořice-Mistřín'),
(210, 'Svážná 438/9 634 00 Brno - Nový Lískovec'),
(211, 'Svitávka Komenského 157 679 32 Svitávka'),
(212, 'Šakvice Hlavní 41 691 67 Šakvice'),
(213, 'Šaratice Náves 96 683 52 Šaratice'),
(214, 'Šardice 521 696 13 Šardice'),
(215, 'Šitbořice Nikolčická 531 691 76 Šitbořice'),
(216, 'Šlapanice Masarykovo náměstí 1594/16 664 51 Šlapanice'),
(217, 'Štěpánov nad Svratkou 159, 592 63 Štěpánov nad Svratkou'),
(218, 'Těšany 305 664 54 Těšany'),
(219, 'Tišnov nám. 28. října 1708 666 01 Tišnov'),
(220, 'Tišnov Smíškova 840 666 01 Tišnov'),
(221, 'Tuháčkova 23/25 617 00 Brno - Komárov'),
(222, 'Tvrdonice Kostická 600/98 691 53 Tvrdonice'),
(223, 'Uherské Hradiště, Komenského náměstí 350, 686 62 UNESCO'),
(224, 'Uherské Hradiště, Za Alejí 1072'),
(225, 'Uherské Hradiště, Větrná 1063'),
(226, 'Újezd u Brna Školní 284 664 53 Újezd u Brna'),
(227, 'Úvoz 423/55 602 00 Brno - Veveří'),
(228, 'Valtice nám. Svobody 38 691 42 Valtice'),
(229, 'Vedlejší 655/10 625 00 Brno - Bohunice'),
(230, 'Vejrostova 1066/1 635 00 Brno – Bystrc'),
(231, 'Velká Bíteš Sadová 579 595 01 Velká Bíteš'),
(232, 'Velká nad Veličkou 461 696 74 Velká nad Veličkou'),
(233, 'Velké Bílovice Fabian 1215 691 02 Velké Bílovice'),
(234, 'Velké Němčice Školní 105 691 63 Velké Němčice'),
(235, 'Velké Opatovice Pod Strážnicí 499 679 61 Velké Opatovice'),
(236, 'Velké Pavlovice Náměstí 9. května 46/2 691 06 Velké Pavlovice'),
(237, 'Veselí nad Moravou Hutník 1456 698 01 Veselí nad Moravou'),
(238, 'Veverská Bítýška náměstí Na Městečku 51 664 71'),
(239, 'Vlasatice 3 691 30 Vlasatice'),
(240, 'Vnorovy Hlavní 17 696 61 Vnorovy'),
(241, 'Voděrady 76 679 01 Voděrady'),
(242, 'Vracov Komenského 950 696 42 Vracov'),
(243, 'Vyškov Purkyňova 39 682 01 Vyškov'),
(244, 'Vyškov Morávkova 492/40 682 01 Vyškov'),
(245, 'Vyškov Nádražní 5/5 682 01 Vyškov-Město'),
(246, 'Vyškov Sídliště Osvobození 682/56 682 01 Vyškov'),
(247, 'Vyškov Tyršova 664/4 682 01 Vyškov'),
(248, 'Zaječí Školní 402 691 05 Zaječí'),
(249, 'Zbraslav Komenského 280 664 84 Zbraslav'),
(250, 'Zbýšov J. A. Komenského 473 664 11'),
(251, 'Zemědělská 173/29 613 00 Brno - Černá Pole'),
(252, 'Znojmo Mládeže 3 669 02 Znojmo'),
(253, 'Znojmo Ke Škole 569/15 669 04 Znojmo – Přímětice'),
(254, 'Znojmo Klášterní 3301/2 669 02 Znojmo'),
(255, 'Znojmo náměstí Republiky 902/9669 02 Znojmo'),
(256, 'Znojmo Pražská 2808/98 669 02 Znojmo'),
(257, 'Znojmo Václavské náměstí 133/8 669 02 Znojmo'),
(258, 'Žarošice 321 696 34 Žarošice'),
(259, 'Ždánice Městečko 18 696 32 Ždánice'),
(260, 'Žďárná 217 679 52 Žďárná'),
(261, 'Želešice 24. dubna 270 664 43 Želešice'),
(262, 'Židlochovice Tyršova 611 667 01'),
(263, 'Scio Škola Heršpice Sokolova 145/4, Brno'),
(264, 'Basic, Šujanovo náměstí 356/1 602 00 Brno'),
(265, 'Scio škola Trnitá Šujanovo nám. 356/1 Brno'),
(266, 'Boskovice Sušilova 2007/28, 680 01 Boskovice'),
(267, 'Boskovice Slovákova 2006/8, 680 01 Boskovice'),
(268, 'Boskovice náměstí 9. května 953/8 680 01 Boskovice'),
(269, 'Horní Štěpánov 36'),
(270, 'Cyrilometodějská Lerchova 344/65 602 00 Brno – Stránice'),
(271, 'Waldorfská, Plovdivská 2572/8 616 00 Brno – Žabovřesky'),
(272, 'Zastávka U Školy 181 664 84 Zastávka'),
(273, 'Veslařská 339/234, Logopedická, 637 00 Brno Jundrov'),
(274, 'Škola příběhem – církevní ZŠ Filipínského 300/1, Brno Filipka'),
(275, 'Ostrovačice Ríšova 43 664 81 Ostrovačice'),
(276, 'Labyrinth, Žerotínovo náměstí 6, Brno 60200'),
(277, 'Koryčany, Masarykova 161, Koryčany');

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id_users` int(16) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `id_schools` int(16) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_users`),
  KEY `school` (`id_schools`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `email_send_user`
--
ALTER TABLE `email_send_user`
  ADD CONSTRAINT `id_email_send` FOREIGN KEY (`id_email_send`) REFERENCES `email_send` (`id_email_send`),
  ADD CONSTRAINT `id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`);

--
-- Omezení pro tabulku `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `school` FOREIGN KEY (`id_schools`) REFERENCES `schools` (`id_schools`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
