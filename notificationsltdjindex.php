<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */
global $langs, $db, $conf, $user;

/**
 *	\file       notificationsltdj/notificationsltdjindex.php
 *	\ingroup    notificationsltdj
 *	\brief      Home page of notificationsltdj top menu
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/notificationsltdj/class/notifs.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/notificationsltdj/class/config.class.php';



// Load translation files required by the page
$langs->loadLangs(array("notificationsltdj@notificationsltdj"));

$action = GETPOST('action', 'aZ09');


// Security check
// if (! $user->rights->notificationsltdj->myobject->read) {
// 	accessforbidden();
// }
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

$max = 5;
$now = dol_now();


/*
 * Actions
 */

$notificationProduit = new Notifs($db);
$notifs = $notificationProduit->fetchAll();

// UserGroup
$sql = 'SELECT rowid, nom FROM '.MAIN_DB_PREFIX.'usergroup';
$sql .= ' ORDER by rowid';
$result = $db->query($sql);

// User
$sql2 = 'SELECT rowid, lastname, firstname FROM '.MAIN_DB_PREFIX.'user';
$sql2 .= ' WHERE lastname NOT LIKE "[MAIL]%"
			AND lastname NOT LIKE "Sup%"
			AND lastname NOT LIKE "ltdj%"';
$sql2 .= ' ORDER by lastname';
$result2 = $db->query($sql2);

/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("", $langs->trans("NotificationsLTDJArea"));

print load_fiche_titre($langs->trans("NotificationsLTDJArea"), '', 'notificationsltdj.png@notificationsltdj');

echo '<div class="divForm">
<h2 class="titreForm">Envoyez la notification importante a un groupe et/ou à votre collègue</h2>

<form id="formulaireNotification" method="POST">
	<fieldset>
		<legend>Formulaire de notification(s)</legend> <br>
		<div class="label&select">
			<div class="div-notif-action">
				<label class="label" for="notif-action">Selectionnez l\'action</label>
				<select class="select" name="notif-action" id="notif-action" required>
					<option>--- Nom de l\'action ---</option>
					<option value="PRODUCT_CREATE">Produit créé</option>
					<option value="PRODUCT_MODIFY">Produit modifié</option>
					<option value="PRODUCT_DELETE">Produit supprimé</option>
				</select>
			</div>

			<div class="switch-group">
				<div class="div-group-select">
					<p class="label-group">Sélectionnez le nom d\'un groupe</p>
					<div class="list-group">';
						while ($row = $db->fetch_object($result)) {
							$idGroup = $row->rowid;
							$groupName = $row->nom;
							echo '<label for="group' . $idGroup . '">';
							echo '<input type="checkbox" name="group" id="group' . $idGroup . '" value="' . $idGroup . '"> ' . $groupName;
							echo '<input type="hidden" name="id_group_json" value="">';
							echo '</label><br>';
						}
						echo '</select> <br> <br>
					</div>
				</div>
				<div class="div-switch">
					<p>Est ce une notification importante ?</p>
					<span style="color:#cccccc;">Pas important <i class="fas fa-arrow-right"></i> </span>
					<label class="switch">
					  	<input type="checkbox" name="is-important-group" value="1" checked>
					  	<span class="slider round"></span>
					</label>
					<span style="color:#568fff;"> <i class="fas fa-arrow-left"></i> Important</span> <br>
				</div>
			</div>

			<div class="switch-user">
			<hr>
				<div class="div-group-select2">
					<p class="label">Envoyez la notification à un ou plusieurs collègues :</p> <br> <br>
					<div class="liste-collegue">';
						while ($row2 = $db->fetch_object($result2)) {
							$idCollegue = $row2->rowid;
							$collegueLastName = $row2->lastname;
							$collegueFirstName = $row2->firstname;
							echo '<label for="collegue' . $idCollegue . '">';
							echo '<input type="checkbox" name="collegue" id="collegue' . $idCollegue . '" value="' . $idCollegue . '"> ' . $collegueLastName . ' ' . $collegueFirstName;
							echo '<input type="hidden" name="id_user_json" value="">';
							echo '</label><br>';
						}
					echo '</div>
				</div>

				<div class="div-switch">
					<p>Est-ce une notification importante ?</p>
					<span style="color:#cccccc;">Pas important <i class="fas fa-arrow-right"></i> </span>
					<label class="switch">
					  	<input type="checkbox" name="is-important-user" value="1" checked>
					  	<span class="slider round"></span>
					</label>
					<span style="color:#568fff;"> <i class="fas fa-arrow-left"></i> Important</span> <br>
				</div>
			</div>

			<div class="divbutton">
				<button type="submit" id="boutonSoumettre" class="button-form" role="button">Envoyez !</button>
			</div>
		</div>
</form>';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	//Récupération en format JSON des id_group
	$colleguesSelectionnes = $_POST['id_user_json'];
	$groupesSelectionnes = $_POST['id_group_json'];

	$actionSelectionnee = $_POST['notif-action'];

	//Date du jour
	$now = dol_now();
	// Récupere les données du formulaire
	$config = new Config($db);

	$config->entity = $user->entity;
	$config->date_creation = $now;
	$config->tms = $now;
	$config->fk_user_modif = $user->id;
	$config->type = $actionSelectionnee;
	$config->user_id_json = $colleguesSelectionnes;
	$config->group_id_json = $groupesSelectionnes;
	$config->is_important_group = $_POST["is-important-group"] ? 1 : 0;
	$config->is_important_user = $_POST["is-important-user"] ? 1 : 0;

	$config->create($user);

	if (!$result) {
		echo "Erreur SQL : " . $db->lasterror();
	}
}

$NBMAX = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;
$max = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;

// End of page
llxFooter();
$db->close();
