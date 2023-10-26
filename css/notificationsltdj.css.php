<?php
/* Copyright (C) 2023 Tony Prioux
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    notificationsltdj/css/notificationsltdj.css.php
 * \ingroup notificationsltdj
 * \brief   CSS file for module NotificationsLTDJ.
 */

//if (! defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');	// Not disabled because need to load personalized language
//if (! defined('NOREQUIREDB'))   define('NOREQUIREDB','1');	// Not disabled. Language code is found on url.
if (!defined('NOREQUIRESOC')) {
	define('NOREQUIRESOC', '1');
}
//if (! defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');	// Not disabled because need to do translations
if (!defined('NOCSRFCHECK')) {
	define('NOCSRFCHECK', 1);
}
if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', 1);
}
if (!defined('NOLOGIN')) {
	define('NOLOGIN', 1); // File must be accessed by logon page so without login
}
//if (! defined('NOREQUIREMENU'))   define('NOREQUIREMENU',1);  // We need top menu content
if (!defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', 1);
}
if (!defined('NOREQUIREAJAX')) {
	define('NOREQUIREAJAX', '1');
}

session_cache_limiter('public');
// false or '' = keep cache instruction added by server
// 'public'  = remove cache instruction added by server
// and if no cache-control added later, a default cache delay (10800) will be added by PHP.

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
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/../main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/../main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

// Load user to have $user->conf loaded (not done by default here because of NOLOGIN constant defined) and load permission if we need to use them in CSS
/*if (empty($user->id) && ! empty($_SESSION['dol_login'])) {
	$user->fetch('',$_SESSION['dol_login']);
	$user->getrights();
}*/


// Define css type
header('Content-type: text/css');
// Important: Following code is to cache this file to avoid page request by browser at each Dolibarr page access.
// You can use CTRL+F5 to refresh your browser cache.
if (empty($dolibarr_nocache)) {
	header('Cache-Control: max-age=10800, public, must-revalidate');
} else {
	header('Cache-Control: no-cache');
}

?>

div.mainmenu.notificationsltdj::before {
	content: "\f249";
}
div.mainmenu.notificationsltdj {
	background-image: none;
}

.myclasscss {
	/* ... */
}


/***************** div Form de notificationsproduitindex *******************/

.divForm {
	display: flex;
	flex-direction: column;
	align-items: center;
}

.titreForm {
	margin: 2em;
	position: relative;
	display: inline-block;
	font-size: 24px; /* Ajustez la taille de la police à votre convenance */
	background: linear-gradient(to bottom, #d4dc3f, #6592be, #ffffff);
	background-clip: text;
	-webkit-background-clip: text; /* Pour la compatibilité avec les anciens navigateurs WebKit */
	color: transparent;
	padding: 5px; /* Espacement pour rendre le dégradé plus visible */
}

/***************** Ajoute du contenu après le texte du Formulaire (pseudo-élément) *******************/
.titreForm::after {
	content: "";
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: inherit;
}

.div-notif-action {
	margin-bottom: 1em;
}

/***************** Divs des Select + switch group et user ***************************/

.div-switch {
	display: flex;
	flex-direction: row;
	align-items: center;
	margin-right: ;
}

.div-switch p {
	margin-right: 2em;
}

.switch-user {
	margin-bottom: 2em;
}

.div-group-select2 {
	margin-top: 2em;
}

.liste-collegue {
	column-count: 4;
}

/* Style optionnel pour les éléments de la liste (étirez-les pour remplir les colonnes) */
.liste-collegue label {
	display: block;
}

.list-group {
	display: flex;
	flex-direction: row;
	justify-content: space-around;
	margin-top: 2em;
}


/***************** Icone de flèche sur toggle notificationsproduit.index.php *******************/
.fa-arrow-right {
	font-size: 20px;
	color: #cccccc;
}

.fa-arrow-left {
	font-size: 20px;
	color:#568fff;
}

/***************** Icone de check box notificationsproduit *******************/
.fa-check {
	font-size: 2em;
	color:forestgreen;
	cursor:pointer;
}

.fa-check:hover {
	text-shadow: blue 1px 0 10px;
}

.switch {
	position: relative;
	display: inline-block;
	width: 60px;
	height: 34px;
	margin: 1em 1em;
}

/***************** Cacher la checkbox par defaut *******************/
.switch input {
	opacity: 0;
	width: 0;
	height: 0;
}


/***************** Sliders *******************/
.slider {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	-webkit-transition: .4s;
	transition: .4s;
}

.slider:before {
	position: absolute;
	content: "";
	height: 26px;
	width: 26px;
	left: 4px;
	bottom: 4px;
	background-color: white;
	-webkit-transition: .4s;
	transition: .4s;
}

input:checked + .slider {
	background-color: #2196F3;
}

input:focus + .slider {
	box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
	-webkit-transform: translateX(26px);
	-ms-transform: translateX(26px);
	transform: translateX(26px);
}

/***************** Cercle des sliders *******************/
.slider.round {
	border-radius: 34px;
}

.slider.round:before {
	border-radius: 50%;
}

.divbutton {
	display: flex;
	text-align: center;
	flex-direction: column;
	justify-content: center;
}

.button-form[disabled] {
	cursor: not-allowed;
}

.button-form {
	background-image: radial-gradient(100% 100% at 100% 0, #5adaff 0, #5468ff 100%);
	border-radius: 6px;
	box-shadow: rgba(45, 35, 66, .4) 0 2px 4px,rgba(45, 35, 66, .3) 0 7px 13px -3px,rgba(58, 65, 111, .5) 0 -3px 0 inset;
	color: #fff;
	cursor: pointer;
	height: 48px;
	justify-content: center;
	margin-bottom: 1em;
	transition: box-shadow .15s,transform .15s;
	will-change: box-shadow,transform;
	font-size: 18px;
}

.button-form:focus {
	box-shadow: #3c4fe0 0 0 0 1.5px inset, rgba(45, 35, 66, .4) 0 2px 4px, rgba(45, 35, 66, .3) 0 7px 13px -3px, #3c4fe0 0 -3px 0 inset;
}

.button-form:hover {
	box-shadow: rgba(45, 35, 66, .4) 0 4px 8px, rgba(45, 35, 66, .3) 0 7px 13px -3px, #3c4fe0 0 -3px 0 inset;
	transform: translateY(-2px);
}

.button-form:active {
	box-shadow: #3c4fe0 0 3px 7px inset;
	transform: translateY(2px);
}

/***************** Tableau de la liste des notifications *******************/

#table-produit-create {
	border-collapse: collapse;
	width: 100%;
	margin: 20px 0;
	box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

#table-produit-create th, #table-produit-create td {
	border: 1px solid #ddd;
	padding: 8px;
	text-align: left;
}

#table-produit-create th {
	background-color: #3C4FE0FF;
	color: #ffffff;
	text-align: center;
}

#table-produit-create tr:nth-child(even) {
	background-color: #f2f2f2;
}

#table-produit-create tr:nth-child(odd) {
	background-color: #fff;
}

/* Marge en haut pour le premier tableau */
#table-produit-create {
	margin-top: 0;
}

