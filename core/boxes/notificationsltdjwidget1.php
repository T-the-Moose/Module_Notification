<?php
/* Copyright (C) 2004-2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2021  Frédéric France     <frederic.france@netlogic.fr>
 * Copyright (C) 2023 Tony Prioux
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
 * \file    notificationsltdj/core/boxes/notificationsltdjwidget1.php
 * \ingroup notificationsltdj
 * \brief   Widget provided by NotificationsLTDJ
 *
 * Put detailed description here.
 */

include_once DOL_DOCUMENT_ROOT."/core/boxes/modules_boxes.php";
require_once DOL_DOCUMENT_ROOT."/custom/notificationsltdj/class/notifs.class.php";
require_once DOL_DOCUMENT_ROOT."/custom/notificationsltdj/class/affichage.class.php";

/**
 * Class to manage the box
 *
 * Warning: for the box to be detected correctly by dolibarr,
 * the filename should be the lowercase classname
 */
class notificationsltdjwidget1 extends ModeleBoxes
{
	/**
	 * @var string Alphanumeric ID. Populated by the constructor.
	 */
	public $boxcode = "notificationsltdjbox";

	/**
	 * @var string Box icon (in configuration page)
	 * Automatically calls the icon named with the corresponding "object_" prefix
	 */
	public $boximg = "notificationsltdj@notificationsltdj";

	/**
	 * @var string Box label (in configuration page)
	 */
	public $boxlabel;

	/**
	 * @var string[] Module dependencies
	 */
	public $depends = array('notificationsltdj');

	/**
	 * @var DoliDb Database handler
	 */
	public $db;

	/**
	 * @var mixed More parameters
	 */
	public $param;

	/**
	 * @var array Header informations. Usually created at runtime by loadBox().
	 */
	public $info_box_head = array();

	/**
	 * @var array Contents informations. Usually created at runtime by loadBox().
	 */
	public $info_box_contents = array();

	/**
	 * @var string    Widget type ('graph' means the widget is a graph widget)
	 */
	public $widgettype = 'graph';


	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 * @param string $param More parameters
	 */
	public function __construct(DoliDB $db, $param = '')
	{
		global $user, $conf, $langs;
		// Translations
		$langs->loadLangs(array("boxes", "notificationsltdj@notificationsltdj"));

		parent::__construct($db, $param);

		$this->boxlabel = $langs->transnoentitiesnoconv("Boîte de notification(s)");

		$this->param = $param;

		//$this->enabled = $conf->global->FEATURES_LEVEL > 0;         // Condition when module is enabled or not
		//$this->hidden = ! ($user->rights->notificationsltdj->myobject->read);   // Condition when module is visible by user (test on permission)
	}

	/**
	 * Load data into info_box_contents array to show array later. Called by Dolibarr before displaying the box.
	 *
	 * @param int $max Maximum number of records to load
	 * @return void
	 */
	public function loadBox($max = 5)
	{
		global $langs, $user, $db;

		// Use configuration value for max lines count
		$this->max = $max;

		// Populate the head at runtime
		$text = $langs->trans("Boîte de notification(s)", $max);
		$this->info_box_head = array(
			// Title text
			'text' => '<p class="bold" style=color:red;"> ' .$text .'</p>',
			// Add a link
//			'sublink' => 'http://example.com',
			// Sublink icon placed after the text
			'subpicto' => 'object_notificationsproduit@notificationsproduit',
			// Sublink icon HTML alt text
			'subtext' => '',
			// Sublink HTML target
			'target' => '',
			// HTML class attached to the picto and link
			'subclass' => 'center',
			// Limit and truncate with "…" the displayed text lenght, 0 = disabled
			'limit' => 0,
			// Adds translated " (Graph)" to a hidden form value's input (?)
			'graph' => false
		);

		$affichage = new Affichage($db);
		$affichageNotifications = $affichage->fetchAll();


		if (!empty($affichageNotifications)) {

			// Créer un tableau pour stocker les lignes de la boîte
			$info_box_contents = array();

			$product_info = array(
				0 => array(
					'tr' => 'class="center bold" style="color:mediumblue;"',
					'text' => 'Référence',
				),
				1 => array(
					'tr' => 'class="center bold"',
					'text' => 'Nom du produit',
				),
				2 => array(
					'tr' => 'class="center bold"',
					'text' => 'Modification',
				),
				3 => array(
					'tr' => 'class="center bold" ',
					'text' => 'Type de notification',
				),
				4 => array(
					'tr' => 'class="center bold"',
					'text' => 'Utilisateur modificateur',
				),
			);

			$info_box_contents[] = $product_info;

			foreach ($affichageNotifications as $affichageNotifs) {
				$idUtilisateurAffichage = $affichageNotifs->id_user;
				$idNotificationAffichage = $affichageNotifs->id_notif;

				$importanceNotification = $affichageNotifs->is_important;

				// Vérification si l'utilisateur connecté correspond à un id_user dans la table affichage
				if ($idUtilisateurAffichage === $user->id) {

					$notifs = new Notifs($db);
					$notificationDetails = $notifs->fetchAll($idNotificationAffichage);

					$produit = new Product($db);

					foreach ($notificationDetails as $notifs) {

						$ref_produit = $notifs->ref;
						$id_notification = $notifs->id;
						$nomDuProduit = $notifs->label;
						$utilisateurModificateur = $notifs->fk_user_modif;
						$texteModification = $notifs->text;

						if ($notifs->type === 'PRODUCT_CREATE') {
							$notif_action = "Produit créé";
						} else if ($notifs->type === 'PRODUCT_MODIFY') {
							$notif_action = "Produit modifié";
						} else if ($notifs->type === 'PRODUCT_DELETE') {
							$notif_action = "Produit supprimé";
						}

						// Afficher le lien de la ref produit avec getNomUrl()
						if ($produit->fetch('', $ref_produit)) {
							$ref_produit = $produit->getNomUrl(1);
						}

						$importanceNotif = ($importanceNotification == 1) ? 'important-notification' : '';

						$info_box_contents[] = array(
							0 => array(
								'td' => 'class="center ' . $importanceNotif . '" data-notification-id="' . $id_notification . '"',
								'text' => $ref_produit,
								'asis' => 1,
							),
							1 => array(
								'td' => 'class="center ' . $importanceNotif . '"',
								'text' => $nomDuProduit,
							),
							2 => array(
								'td' => 'class="center ' . $importanceNotif . '"',
								'text' => $texteModification,
							),
							3 => array(
								'td' => 'class="center ' . $importanceNotif . '"',
								'text' => $notif_action,
							),
							4 => array(
								'td' => 'class="center ' . $importanceNotif . '"',
								'text' => $utilisateurModificateur,
							),
						);
					}
				}
			}


			// Affecte le tableau d'informations à la boxe
			$this->info_box_contents = $info_box_contents;
		} else {
			// Aucune donnée trouvée dans la table.
			echo "Aucune donnée trouvée dans la table.";
		}
	}



	/**
	 * Method to show box. Called by Dolibarr eatch time it wants to display the box.
	 *
	 * @param array $head       Array with properties of box title
	 * @param array $contents   Array with properties of box lines
	 * @param int   $nooutput   No print, only return string
	 * @return string
	 */
	public function showBox($head = null, $contents = null, $nooutput = 0)
	{
		// You may make your own code here…
		// … or use the parent's class function using the provided head and contents templates
		return parent::showBox($this->info_box_head, $this->info_box_contents, $nooutput);
	}
}
