START TRANSACTION; 

CREATE TABLE `adkq_site_visits` (
  `id` int(11) NOT NULL,
  `did` int(11) NOT NULL,
  `date` varchar(10) NOT NULL,
  `ip` varchar(19) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `adkq_site_counter` (
  `id` int(11) NOT NULL,
  `did` int(11) NOT NULL,
  `date` varchar(10) NOT NULL,
  `visits` int(11) NOT NULL DEFAULT '0',
  `bots` int(11) NOT NULL DEFAULT '0',
  `views` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `adkq_site_counter`  ADD PRIMARY KEY (`id`),  ADD UNIQUE KEY `id` (`did`,`date`);

ALTER TABLE `adkq_site_counter`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;  

ALTER TABLE `adkq_site_visits`   ADD PRIMARY KEY (`id`),  ADD UNIQUE KEY `id` (`did`,`ip`,`date`);

ALTER TABLE `adkq_site_visits` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

COMMIT;
