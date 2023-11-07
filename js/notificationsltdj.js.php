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
document.addEventListener('DOMContentLoaded', function() {
	let myButtons = document.querySelectorAll('.monBouton');

	// Parcourir la liste des boutons et ajout d'un addEventListener à chacun
	myButtons.forEach(function(myButton) {
		myButton.addEventListener('click', function() {

			let box = this.closest('.boite');
			if (box) {
				box.style.transition = 'opacity 1s ease-in-out';
				box.style.opacity = '0';

				setTimeout(function() {
					box.remove();
				}, 1000);
			}
		});
	});
});

// Animation formulaire en jQuery
$(document).ready(function() {

	$('.div-checkbox').hide();
	$('.div-group-select').hide();
	$('.div-switch').hide();
	$('.div-group-select2').hide();

	let button = $('.button-form');

	// Désactive le bouton
	button.prop('disabled', true);

	$('.div-notif-action select').on('change', function() {
		let selectedValue = $(this).val();

		if (selectedValue !== '--- Nom de l\'action ---') {

			$('.div-checkbox').slideDown(900);
			$('.div-group-select').slideDown(900);
			$('.div-switch').slideDown(900);
			$('.div-group-select2').slideDown(900);

			// Activation du bouton
			button.prop('disabled', false);
		} else {
			$('.div-checkbox').slideUp(900);
			$('.div-group-select').slideUp(900);
			$('.div-switch').slideUp(900);
			$('.div-group-select2').slideUp(900);

			// Désactive le bouton lorsque rien n'est sélectionné
			button.prop('disabled', true);
		}
	});
});


// API Intersection Observer pour date d'affichage
$(document).ready(function() {

	let elementsToObserve = document.querySelectorAll('[data-notification-id]');

	// Options de l'observateur d'intersection
	let options = {
		root: null,
		rootMargin: '0px', // Marge autour de la fenêtre de visualisation
		threshold: 1 // Visible si 100 % ou plus est visible
	};

	// Fonction appelée lorsque l'élément devient visible
	let handleIntersection = (notifications, observer) => {
		notifications.forEach(notification => {
			if (notification.isIntersecting) {

				let dateActuelle = new Date().toISOString();
				let formData = new FormData();
				formData.append('date', dateActuelle);

				// Envoi de la date au serveur en utilisant une requête AJAX
				let xhr = new XMLHttpRequest();
				xhr.open('POST', 'core/triggers/interface_99_modNotificationsLTDJ_NotificationsLTDJTriggers.class.php', true);

				xhr.onload = function () {
					if (xhr.status >= 200 && xhr.status < 300) {
						console.log('Requête AJAX réussie');
					} else {
						console.error('Erreur de la requête AJAX');
					}
				};

				xhr.send(formData);

				// Arrêt d'observation de notificationId après enregistrement
				observer.unobserve(notification.target);
			}
		});
	};

	// Instance de IntersectionObserver
	let observer = new IntersectionObserver(handleIntersection, options);

	// Ajoute chaque élément à observer
	elementsToObserve.forEach(element => {
		observer.observe(element);
	});
});




