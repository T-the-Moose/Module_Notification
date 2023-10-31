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
global $langs, $db, $conf, $user, $object;

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
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';


require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';


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

// UserGroup
$sql = 'SELECT rowid, nom FROM '.MAIN_DB_PREFIX.'usergroup';
$sql .= ' ORDER by rowid';
$result = $db->query($sql);

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
						echo '<td class="tdoverflowmax200">';
						$groupList = array();
						while ($row = $db->fetch_object($result)) {
							$groupList[$row->rowid] = $row->nom;
						}

						$selected = array();
						echo $form->multiselectarray('Groupe', $groupList, $selected, 0, null, null, null, "300%");
						echo '</td>';
					echo '</div>
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
				<div class="div-group-select2">
					<p class="label">Envoyez la notification à un ou plusieurs collègues :</p>
					<div class="liste-collegue">';
						echo '<td class="tdoverflowmax200">';
						$userList = $form->select_dolusers('', 'userList', 0, null, 0, '', '', 0, 0, 0, '', 0, '', '', 0, 1);
						$selected = array();
						echo $form->multiselectarray('Utilisateur', $userList, $selected, 0, null, null, null, "300%");
						echo '</td>';
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

	//Récupération et encode au format JSON des ids group et user
	$arrayUsers = GETPOST('Utilisateur', 'array:restricthtml');
	$colleguesSelectionnes = json_encode($arrayUsers);

	$arrayGroups= GETPOST('Groupe', 'array:restricthtml');
	$groupesSelectionnes = json_encode($arrayGroups);

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

