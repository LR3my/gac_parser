-- --------------------------------------------------------

--
-- Structure de la table `logs`
--

CREATE TABLE `logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscriber_number` int(11) NOT NULL,
  `type` enum('SMS','data','phone_call') COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_call_actual_duration` time DEFAULT NULL,
  `phone_call_billed_duration` time DEFAULT NULL,
  `data_real_volume` double(8,2) DEFAULT NULL,
  `data_billed_volume` double(8,2) DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;