<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines french translations.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Plugin name in administration
$string['pluginname'] = 'OpenVeo Migration Tool';

// Privacy (GDPR)
$string['privacy:metadata'] = 'Le plugin OpenVeo Migration Tool n\'enregistre pas de données personnelles.';

// Settings page
$string['settingstitle'] = 'OpenVeo Migration Tool configuration';
$string['settingsdescription'] = '<p>Configurez la manière dont les vidéos Moodle seront migrées sur OpenVeo. Seules les vidéos de type défini ici seront migrées, assurez-vous auparavant qu\'OpenVeo est capable de recevoir les types de vidéos spécifiés. OpenVeo Migration Tool permet de sélectionner précisément les vidéos à migrer à l\'aide de la page de migration mais il est également possible de migrer les vidéos automatiquement sans devoir les sélectionner (si toutes les vidéos présentes et futures doivent être migrées) à l\'aide de l\'option "Migration automatique". Vous pouvez stopper la migration des vidéos à tout moment en stoppant la tâche planifiée correspondante.</p>';
$string['settingsvideotypestomigratelabel'] = 'Types de vidéos à migrer';
$string['settingsvideotypestomigrate'] = 'Types de vidéos à migrer';
$string['settingsvideotypestomigrate_help'] = 'La liste des types de vidéos à migrer sur OpenVeo. Assurez-vous qu\'OpenVeo Publish accepte les types de vidéos définis ici.';
$string['settingsautomaticmigrationactivatedlabel'] = 'Migration automatique';
$string['settingsautomaticmigrationactivatedcheckboxlabel'] = 'Migrer les vidéos automatiquement';
$string['settingsautomaticmigrationactivated'] = 'Migration automatique';
$string['settingsautomaticmigrationactivated_help'] = 'Activer la migration automatique migrera automatiquement toutes les vidéos Moodle (présentes et futures) au lieu de les sélectionner manuellement.';
$string['settingsdestinationplatformlabel'] = 'Plateforme vidéos';
$string['settingsdestinationplatform'] = 'Plateforme vidéos';
$string['settingsdestinationplatform_help'] = 'Choisissez une plateforme vidéos pour les vidéos à migrer. OpenVeo peut stocker les vidéos sur différentes plateformes.';
$string['settingsdestinationplatformlocal'] = 'OpenVeo';
$string['settingsdestinationplatformyoutube'] = 'Youtube';
$string['settingsdestinationplatformvimeo'] = 'Vimeo';
$string['settingsdestinationplatformtls'] = 'TLS';
$string['settingsdestinationplatformwowza'] = 'Wowza';
$string['settingsstatuspollingfrequencylabel'] = 'Fréquence de récupération du statut (en secondes)';
$string['settingsstatuspollingfrequency'] = 'Fréquence de récupération du statut (en secondes)';
$string['settingsstatuspollingfrequency_help'] = 'Lors de la migration d\'une vidéo Moodle vers OpenVeo, OpenVeo Migration Tool s\'informe régulièrement de l\'état de la vidéo auprès d\'OpenVeo jusqu\'à ce qu\'elle soit complétement traitée. La fréquence par défaut est de 10.';
$string['settingsstatuspollingfrequencyformaterror'] = 'Fréquence invalide (ex : 10).';
$string['settingsfilefieldslabel'] = 'Champs d\'ajout de fichiers';
$string['settingsfilefields'] = 'Champs d\'ajout de fichiers';
$string['settingsfilefields_help'] = 'La liste des champs de formulaire de type "editor" et "filemanager" permettant d\'ajouter des fichiers. Si une réfèrence vers une vidéo OpenVeo est ajoutée à partir d\'un champ de formulaire sans que le champ ne soit défini ici, OpenVeo Migration Tool ne migrera pas la vidéo. Chaque ligne représente un champ avec trois colonnes : le composant propriétaire du champ (component), la zone du fichier (filearea) et les méthodes supportées (supportedmethods). Les colonnes sont séparées par des barres verticales (pipe). Plus d\'informations disponibles sur <a href="https://github.com/veo-labs/openveo-moodle-migration-tool" target="_blank">la page du plugin</a>. L\'ordre des lignes est également important puisqu\'il détermine l\'ordre de la migration automatique. Les vidéos correspondant au premier champ (première ligne) seront migrées avant les vidéos correspondant au deuxième champ (seconde ligne) et ainsi de suite.';
$string['settingssubmitlabel'] = 'Enregistrer les modifications';

// Errors
$string['errorlocalpluginnotconfigured'] = 'Le plugin local "OpenVeo API" n\'est pas configuré.';
$string['errornovideoplatform'] = 'Aucune platforme vidéos de configurée sur OpenVeo Publish.';
$string['errormigrationwrongconfiguration'] = 'La migration nécessite au moins un type de vidéos et une platforme de destination.';
$string['errornorepositoryopenveo'] = 'Aucun dépôt OpenVeo trouvé.';

// Events
$string['eventgettingplatformsfailed'] = 'Récupération des plateformes vidéos echouée';
$string['eventvideomigrationstarted'] = 'Migration vidéo démarrée';
$string['eventvideomigrationended'] = 'Migration vidéo terminée';
$string['eventvideomigrationfailed'] = 'Migration vidéo échouée';
$string['eventvideotransitionstarted'] = 'Transition vidéo démarrée';
$string['eventvideotransitionended'] = 'Transition vidéo terminée';
$string['eventvideotransitionfailed'] = 'Transition vidéo échouée';
$string['eventgettingregisteredvideofailed'] = 'Récupération vidéo planifiée échouée';
$string['eventgettingvideofailed'] = 'Récupération vidéo échouée';
$string['eventplanningvideofailed'] = 'Planification vidéo échouée';
$string['eventupdatingvideomigrationstatusfailed'] = 'Modification migration statut vidéo échouée';
$string['eventsendingvideofailed'] = 'Envoi vidéo échoué';
$string['eventwaitingforopenveovideofailed'] = 'Attente vidéo OpenVeo échouée';
$string['eventremovingopenveovideofailed'] = 'Suppression vidéo OpenVeo échouée';
$string['eventgettingopenveovideofailed'] = 'Récupération vidéo OpenVeo échouée';
$string['eventpublishingopenveovideofailed'] = 'Publication vidéo OpenVeo échouée';
$string['eventcreatingreferencefailed'] = 'Création référence vidéo échouée';
$string['eventverifyingvideofailed'] = 'Vérification vidéo échouée';
$string['eventremovingreferencesfailed'] = 'Suppression références video échouée';
$string['eventremovingoriginalfailed'] = 'Suppression vidéo originale échouée';
$string['eventremovingoriginalaliasesfailed'] = 'Suppression alias vidéo originale échouée';
$string['eventremovingdraftfilesfailed'] = 'Suppression fichiers brouillons échouée';
$string['eventconnectingtoopenveofailed'] = 'Connexion à OpenVeo échouée';
$string['eventrestoringoriginalfailed'] = 'Récupération vidéo originale échouée';
$string['eventrestoringoriginalaliasesfailed'] = 'Récupération alias vidéo originale échouée';
$string['eventupdatingregisteredvideoidfailed'] = 'Modification id vidéo planifiée échouée';

// Tasks
$string['taskmigratename'] = 'Migrer les vidéos Moodle vers OpenVeo';
