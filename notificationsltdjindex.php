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

<form method="POST" action="#">
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
				</select> <br> <br>
			</div>

			<div class="div-notif-select">
				<label class="label" for="notif-select">Selectionnez une notification</label>
				<select class="select" name="notif-select" id="notif-select" required>
				<option>--- Nom de la notification ---</option>';
				foreach($notifs as $notif){
					$id = $notif->id;
					$titre = $notif->label;
					$action = $notif->action;
					echo '<option value="' . $id . '" data-action="' . $action . '">' . $titre . '</option>';
				}
				echo '</select> <br> <br>
			</div>
			<div class="div-checkbox">
				<div class="checkbox-1">
					<label for="group-checkbox">Envoyer la notification à un groupe ?</label>
					<input type="checkbox" id="group-checkbox" name="group-checkbox"/>
				</div>
				<br>
				<div class="checkbox-2">
					<label for="user-checkbox">Envoyer la notification à un collègue ?</label>
					<input type="checkbox" id="user-checkbox" name="user-checkbox" />
				</div>
			</div>
			<div class="div-group-select">
				<label class="label" for="group-select">Selectionnez le nom d\'un groupe</label>
				<select class="select" name="group-select" id="group-select" required>
				<option>--- Nom du groupe ---</option>';
				while ($row = $db->fetch_object($result)) {
					$idGroup = $row->rowid;
					$groupName = $row->nom;
					echo '<option value="' . $idGroup . '">' . $groupName . '</option>';
				}
				echo '</select> <br> <br>
			</div>

			<div class="div-user-select">
				<label class="label" for="user-select">Selectionnez le nom de votre collègue</label>
				<select class="select" name="user-select" id="user-select">
				<option>--- Nom de votre collègue ---</option>';
				while ($row = $db->fetch_object($result2)) {
					$idGroup = $row->rowid;
					$userLastName = $row->lastname;
					$userFirstName = $row->firstname;
					echo '<option value="' . $idGroup . '">' . $userLastName . ' ' . $userFirstName . '</option>';
				}
				echo '</select><br>
			</div>

			<div class="divswitch">
				<span style="color:#cccccc;">Pas important <i class="fas fa-arrow-right"></i> </span>
				<label class="switch">
				  <input type="checkbox" name="is-important" value="1">
				  <span class="slider round"></span>
				</label>
				<span style="color:#568fff;"> <i class="fas fa-arrow-left"></i> Important</span> <br>
			</div>

			<div class="divbutton">
				<button class="button-form" role="button">Envoyez !</button>
			</div>
		</div>
</form>';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Récupere les données du formulaire
	$config = new Config($db);

	$config->fk_id_notif = $_POST["notif-select"];
	$config->fk_id_group = $_POST["group-select"];
	$config->fk_user_form = $_POST["user-select"];
	$config->fk_user_connected = $user->id;
	$config->notif_important = isset($_POST["is-important"]) ? 1 : 0;

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
