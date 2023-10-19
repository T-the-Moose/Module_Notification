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
 *
 * Library javascript to enable Browser notifications
 */

if (!defined('NOREQUIREUSER')) {
	define('NOREQUIREUSER', '1');
}
if (!defined('NOREQUIREDB')) {
	define('NOREQUIREDB', '1');
}
if (!defined('NOREQUIRESOC')) {
	define('NOREQUIRESOC', '1');
}
if (!defined('NOREQUIRETRAN')) {
	define('NOREQUIRETRAN', '1');
}
if (!defined('NOCSRFCHECK')) {
	define('NOCSRFCHECK', 1);
}
if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', 1);
}
if (!defined('NOLOGIN')) {
	define('NOLOGIN', 1);
}
if (!defined('NOREQUIREMENU')) {
	define('NOREQUIREMENU', 1);
}
if (!defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', 1);
}
if (!defined('NOREQUIREAJAX')) {
	define('NOREQUIREAJAX', '1');
}


/**
 * \file    notificationsltdj/js/notificationsltdj.js.php
 * \ingroup notificationsltdj
 * \brief   JavaScript file for module NotificationsLTDJ.
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

// Define js type
header('Content-Type: application/javascript');
// Important: Following code is to cache this file to avoid page request by browser at each Dolibarr page access.
// You can use CTRL+F5 to refresh your browser cache.
if (empty($dolibarr_nocache)) {
	header('Cache-Control: max-age=3600, public, must-revalidate');
} else {
	header('Cache-Control: no-cache');
}
?>

/* Javascript library of module NotificationsLTDJ */


// Faire disparaitre la boite de notifications importantes
//document.addEventListener('DOMContentLoaded', function() {
//	let myButtons = document.querySelectorAll('.monBouton');
//
//	// Parcourir la liste des boutons et ajout d'un addEventListener à chacun
//	myButtons.forEach(function(myButton) {
//		myButton.addEventListener('click', function() {
//
//			let box = this.closest('.boite');
//			if (box) {
//				box.style.transition = 'opacity 1s ease-in-out';
//				box.style.opacity = '0';
//
//				setTimeout(function() {
//					box.remove();
//				}, 1000);
//			}
//
//			// requête AJAX
//			$.ajax(
//				{
//					url: 'custom/moduletest1/ajax/myobject.php',
//					type: 'GET',
//					data: {
//						mode:'updateData',
//						notification: this.id
//					},
//					dataType: 'JSON', // (json, html, etc.)
//					async: true,
//					success: function(data) {
//						// Requête a réussi
//						console.log('Réponse de la requête :', data);
//
//						let resultat = JSON.parse(data);
//
//						// Afficher le résultat JSON dans la console
//						console.log('Résultat JSON :', resultat);
//
//					},
//					error: function(xhr, status, error) {
//						// En cas d'erreur lors de la requête
//						console.error('Erreur de requête :', status, error);
//					}
//				});
//		});
//	});
//});


// Animation formulaire
document.addEventListener('DOMContentLoaded', function() {
	let selectAction = document.getElementById('notif-action');
	let selectNotification = document.querySelector('.div-notif-select');
	let selectGroup = document.querySelector('.div-group-select');
	let selectUser = document.querySelector('.div-user-select');
	let checkboxUserGroup = document.querySelector('.div-checkbox')

	// Choisir toutes les options notifications du <select>
	let allOptions = selectNotification.querySelectorAll('option');

	selectNotification.style.display = 'none';
	selectGroup.style.display = 'none';
	selectUser.style.display = 'none';
	checkboxUserGroup.style.display ="none"

	// Masque toutes les options de notification au chargement de la page
	allOptions.forEach(function(option) {
		option.style.display = 'none';
	});

	// Écouteur d'événement pour la case à cocher "Envoyer la notification à un groupe ?"
	document.getElementById('group-checkbox').addEventListener('change', function() {
		if (this.checked) {
			selectGroup.style.display = 'block';
		} else {
			selectGroup.style.display = 'none';
		}
	});

	// Écouteur d'événement pour la case à cocher "Envoyer la notification à un collègue ?"
	document.getElementById('user-checkbox').addEventListener('change', function() {
		if (this.checked) {
			selectUser.style.display = 'block';
		} else {
			selectUser.style.display = 'none';
		}
	});

	selectAction.addEventListener('change', function() {
		let selectedValue = selectAction.value;
		let matchingOptions = Array.from(selectNotification.querySelectorAll('option[data-action="' + selectedValue + '"]'));

		matchingOptions.forEach(function(option) {
			option.style.display = 'block';
		});

		allOptions.forEach(function(option) {
			if (!matchingOptions.includes(option)) {
				option.style.display = 'none';
			}
		});

		if (selectedValue !== '') {
			selectNotification.style.display = 'block';
			checkboxUserGroup.style.display = "block";
		}
	});
});


