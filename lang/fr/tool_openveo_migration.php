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

// Plugin name in administration.
$string['pluginname'] = 'OpenVeo Migration Tool';

// Privacy (GDPR).
$string['privacy:metadata'] = 'Le plugin OpenVeo Migration Tool n\'enregistre pas de données personnelles.';

// Settings page.
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
$string['settingsdestinationplatformchoose'] = 'Choisir...';
$string['settingsdestinationplatformlocal'] = 'OpenVeo';
$string['settingsdestinationplatformyoutube'] = 'Youtube';
$string['settingsdestinationplatformvimeo'] = 'Vimeo';
$string['settingsdestinationplatformtls'] = 'TLS';
$string['settingsdestinationplatformwowza'] = 'Wowza';
$string['settingsdestinationgrouplabel'] = 'Groupe de contenus';
$string['settingsdestinationgroup'] = 'Groupe de contenus';
$string['settingsdestinationgroup_help'] = 'Choisissez un groupe de contenus OpenVeo à attribuer aux vidéos migrées.';
$string['settingsdestinationgroupchoose'] = 'Choisir...';
$string['settingsmigratedcoursevideonameformatlabel'] = 'Format du nom des vidéos OpenVeo pour les vidéos dans un contexte de cours';
$string['settingsmigratedcoursevideonameformat'] = 'Format du nom des vidéos OpenVeo pour les vidéos dans un contexte de cours';
$string['settingsmigratedcoursevideonameformat_help'] = 'Le format à utiliser pour le nom des vidéos OpenVeo issues d\'un contexte de cours. Les jetons disponibles sont : <ul><li><strong>%filename%</strong>: Le nom du fichier vidéo</li><li><strong>%courseid%</strong> : L\'identifiant du cours</li><li><strong>%courseidnumber%</strong> : Le numéro d\'identification du cours</li><li><strong>%coursecategoryid%</strong> : L\'identifiant de la categorie à laquelle appartient le cours</li><li><strong>%coursefullname%</strong> : Le nom complet du cours</li><li><strong>%courseshortname%</strong> : Le nom abrégé du cours</li></ul> ("%courseid% - %filename%" par défaut)';
$string['settingsmigratedmodulevideonameformatlabel'] = 'Format du nom des vidéos OpenVeo pour les vidéos dans un contexte activités / ressources';
$string['settingsmigratedmodulevideonameformat'] = 'Format du nom des vidéos OpenVeo pour les vidéos dans un contexte activités / ressources';
$string['settingsmigratedmodulevideonameformat_help'] = 'Le format à utiliser pour le nom des vidéos OpenVeo issues d\'un contexte d\'activités ou de ressources. Les jetons disponibles sont : <ul><li><strong>%filename%</strong> : Le nom du fichier vidéo</li><li><strong>%moduleid%</strong> : L\'identifiant de l\'activité / ressource</li><li><strong>%modulename%</strong> : Le nom de l\'activité / ressource</li><li><strong>%courseid%</strong> : L\'identifiant du cours</li><li><strong>%courseidnumber%</strong> : Le numéro d\'identification du cours</li><li><strong>%coursecategoryid%</strong> : L\'identifiant de la categorie à laquelle appartient le cours</li><li><strong>%coursefullname%</strong> : Le nom complet du cours</li><li><strong>%courseshortname%</strong> : Le nom abrégé du cours</li></ul> ("%courseid% - %filename%" par défaut)';
$string['settingsmigratedcategoryvideonameformatlabel'] = 'Format du nom des vidéos OpenVeo pour les vidéos dans un contexte de catégories';
$string['settingsmigratedcategoryvideonameformat'] = 'Format du nom des vidéos OpenVeo pour les vidéos dans un contexte de catégories';
$string['settingsmigratedcategoryvideonameformat_help'] = 'Le format à utiliser pour le nom des vidéos OpenVeo issues d\'un contexte de catégories. Les jetons disponibles sont : <ul><li><strong>%filename%</strong> : Le nom du fichier vidéo</li><li><strong>%categoryid%</strong> : L\'identifiant de la catégorie</li><li><strong>%categoryidnumber%</strong> : Le numéro d\'identifiant de la catégorie</li><li><strong>%categoryname%</strong> : Le nom de la catégorie</li></ul> ("%categoryid% - %filename%" par défaut)';
$string['settingsmigratedblockvideonameformatlabel'] = 'Format du nom des vidéos OpenVeo pour les videos dans un contexte de blocs';
$string['settingsmigratedblockvideonameformat'] = 'Format du nom des vidéos OpenVeo pour les videos dans un contexte de blocs';
$string['settingsmigratedblockvideonameformat_help'] = 'Le format à utiliser pour le nom des vidéos OpenVeo issues d\'un contexte de blocs. Les jetons disponibles sont : <ul><li><strong>%filename%</strong>: Le nom du fichier vidéo</li><li><strong>%blockid%</strong> : L\'identifiant du bloc</li><li><strong>%blockname%</strong> : Le nom du bloc</li></ul>Les jetons suivants sont disponibles uniquement pour les blocs associés à un cours : <ul><li><strong>%courseid%</strong> : L\'identifiant du cours</li><li><strong>%courseidnumber%</strong> : Le numéro d\'identification du cours</li><li><strong>%coursecategoryid%</strong> : L\'identifiant de la categorie à laquelle appartient le cours</li><li><strong>%coursefullname%</strong> : Le nom complet du cours</li><li><strong>%courseshortname%</strong> : Le nom abrégé du cours</li></ul> ("%blockid% - %filename%" par défaut)';
$string['settingsmigrateduservideonameformatlabel'] = 'Format du nom des vidéos OpenVeo pour les videos dans un contexte utilisateurs';
$string['settingsmigrateduservideonameformat'] = 'Format du nom des vidéos OpenVeo pour les videos dans un contexte utilisateurs';
$string['settingsmigrateduservideonameformat_help'] = 'Le format à utiliser pour le nom des vidéos OpenVeo issues d\'un contexte utilisateurs. Les jetons disponibles sont : <ul><li><strong>%filename%</strong>: Le nom du fichier vidéo</li><li><strong>%userid%</strong> : L\'identifiant de l\'utilisateur</li><li><strong>%username%</strong> : Le nom de l\'utilisateur</li><li><strong>%userfirstname%</strong> : Le prénom de l\'utilisateur</li><li><strong>%userlastname%</strong> : Le nom de famille de l\'utilisateur</li><li><strong>%useremail%</strong> : L\'email de l\'utilisateur</li></ul> ("%userid% - %filename%" par défaut)';
$string['settingsstatuspollingfrequencylabel'] = 'Fréquence de récupération du statut (en secondes)';
$string['settingsstatuspollingfrequency'] = 'Fréquence de récupération du statut (en secondes)';
$string['settingsstatuspollingfrequency_help'] = 'Lors de la migration d\'une vidéo Moodle vers OpenVeo, OpenVeo Migration Tool s\'informe régulièrement de l\'état de la vidéo auprès d\'OpenVeo jusqu\'à ce qu\'elle soit complétement traitée. La fréquence par défaut est de 10.';
$string['settingsstatuspollingfrequencyformaterror'] = 'Fréquence invalide (ex : 10)';
$string['settingsplanningpagevideosnumberlabel'] = 'Planification : Nombre maximum de vidéos par page';
$string['settingsplanningpagevideosnumber'] = 'Planification : Nombre maximum de vidéos par page';
$string['settingsplanningpagevideosnumber_help'] = 'Le nombre de vidéos à afficher par page de résultats sur la page de planification (10 par défaut).';
$string['settingsplanningpagevideosnumberformaterror'] = 'Nombre de vidéos invalide (ex : 10)';
$string['settingsuploadcurltimeoutlabel'] = 'Limite de chargement (en secondes)';
$string['settingsuploadcurltimeout'] = 'Limite de chargement (en secondes)';
$string['settingsuploadcurltimeout_help'] = 'Le nombre de secondes avant d\'arrêter le chargement d\'une vidéo sur OpenVeo (3600 par défaut).';
$string['settingsuploadcurltimeoutformaterror'] = 'Limite invalide (ex : 500)';
$string['settingsfilefieldslabel'] = 'Champs d\'ajout de fichiers';
$string['settingsfilefields'] = 'Champs d\'ajout de fichiers';
$string['settingsfilefields_help'] = 'La liste des champs de formulaire de type "editor" et "filemanager" permettant d\'ajouter des fichiers. Si une réfèrence vers une vidéo OpenVeo est ajoutée à partir d\'un champ de formulaire sans que le champ ne soit défini ici, OpenVeo Migration Tool ne migrera pas la vidéo. Chaque ligne représente un champ avec trois colonnes : le composant propriétaire du champ (component), la zone du fichier (filearea) et les méthodes supportées (supportedmethods). Les colonnes sont séparées par des barres verticales (pipe). Plus d\'informations disponibles sur <a href="https://github.com/veo-labs/openveo-moodle-migration-tool" target="_blank">la page du plugin</a>. L\'ordre des lignes est également important puisqu\'il détermine l\'ordre de la migration automatique. Les vidéos correspondant au premier champ (première ligne) seront migrées avant les vidéos correspondant au deuxième champ (seconde ligne) et ainsi de suite.';
$string['settingssubmitlabel'] = 'Enregistrer les modifications';

// Planning page.
$string['planningtitle'] = 'OpenVeo Migration Tool planification';

// Planning page: search form.
$string['planningsearchgroup'] = 'Rechercher';
$string['planningsearchfrom'] = 'Du';
$string['planningsearchto'] = 'Au';
$string['planningsearchtypeslabel'] = 'Type';
$string['planningsearchtypesall'] = 'Tous';
$string['planningsearchstatuslabel'] = 'Statut';
$string['planningsearchstatusall'] = 'Tous';
$string['planningsearchstatus0'] = 'Erreur';
$string['planningsearchstatus1'] = 'Programmée';
$string['planningsearchstatus2'] = 'En cours de migration';
$string['planningsearchstatus3'] = 'Migrée';
$string['planningsearchstatus4'] = 'Non programmée';
$string['planningsearchstatus5'] = 'Bloquée';
$string['planningsearchsubmitlabel'] = 'Rechercher';

// Planning page: action form.
$string['planningactionssubmitlabel'] = 'Appliquer';
$string['planningactionslabel'] = 'Pour les vidéos sélectionnées...';
$string['planningactionschooseaction'] = 'Choisir...';
$string['planningactionsregisteraction'] = 'Programmer';
$string['planningactionsderegisteraction'] = 'Déprogrammer';
$string['planningactionsremoveaction'] = 'Supprimer';

// Planning page: table of results.
$string['planningtablecaption'] = 'Résultats de recherche ({$a})';
$string['planningtablefilename'] = 'Nom du fichier';
$string['planningtablecontexts'] = 'Contextes';
$string['planningtablecontexts_help'] = 'La liste des contextes où apparaît la vidéo. Plusieurs contextes signifie que la vidéo possède des références.';
$string['planningtabledate'] = 'Date';
$string['planningtabledate_help'] = 'La date d\'ajout de la vidéo originale (pas les références) dans Moodle.';
$string['planningtabletype'] = 'Type';
$string['planningtablestatus'] = 'Statut';
$string['planningtablestatus0'] = 'Erreur';
$string['planningtablestatus1'] = 'Programmée';
$string['planningtablestatus2'] = 'En cours de migration';
$string['planningtablestatus3'] = 'Migrée';
$string['planningtablestatus4'] = 'Non programmée';
$string['planningtablestatus5'] = 'Bloquée';
$string['planningtablestatus6'] = 'Non supportée';

// Errors.
$string['errorlocalpluginnotconfigured'] = 'Le plugin local "OpenVeo API" n\'est pas configuré.';
$string['errornovideoplatform'] = 'Aucune plateforme vidéos n\'est configurée sur OpenVeo Publish.';
$string['errormigrationwrongconfiguration'] = 'La migration nécessite au moins un type de vidéos et une plateforme de destination.';
$string['errornorepositoryopenveo'] = 'Aucun dépôt OpenVeo trouvé.';
$string['errorgettingvideos'] = 'La recherche de vidéos a échoué (cf. logs pour plus d\'information).';
$string['errorpreparingvideos'] = 'La recherche de vidéos a échoué (cf. logs pour plus d\'information).';
$string['errorplanningvideos'] = 'L\'ajout de vidéos à migrer a échoué (cf. logs pour plus d\'information).';
$string['errorderegisteringvideos'] = 'La déprogrammation des vidéos a échoué (cf. logs pour plus d\'information).';

// Events.
$string['eventgettingplatformsfailed'] = 'Récupération des plateformes vidéos echouée';
$string['eventgettinggroupsfailed'] = 'Récupération des groupes echouée';
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
$string['eventrestoringoriginalfailed'] = 'Récupération vidéo originale échouée';
$string['eventrestoringoriginalaliasesfailed'] = 'Récupération alias vidéo originale échouée';
$string['eventupdatingregisteredvideoidfailed'] = 'Modification id vidéo planifiée échouée';
$string['eventgettingvideosfailed'] = 'Récupération fichiers vidéos Moodle échouée';
$string['eventgettingvideocontextfailed'] = 'Récupération contexte vidéo échouée';
$string['eventplanningvideosfailed'] = 'Programmation vidéos échouée';
$string['eventderegisteringvideosfailed'] = 'Déprogrammation vidéos échouée';

// Tasks.
$string['taskmigratename'] = 'Migrer les vidéos Moodle vers OpenVeo';

// OpenVeo.
$string['openveooriginallabel'] = 'Original';
$string['openveoaliaslabel'] = 'Alias';
