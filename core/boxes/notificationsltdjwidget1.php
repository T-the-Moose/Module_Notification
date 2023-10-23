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
require_once DOL_DOCUMENT_ROOT."/custom/notifications/class/produit.class.php";
require_once DOL_DOCUMENT_ROOT."/custom/notifications/class/config.class.php";


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
	 * @var string 	Widget type ('graph' means the widget is a graph widget)
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
		global $langs, $db;

		// Use configuration value for max lines count
		$this->max = $max;

		//dol_include_once("/notifications/class/notifications.class.php");

		// Populate the head at runtime
		$text = $langs->trans("Notification(s) importante(s)", $max);
		$this->info_box_head = array(
			// Title text
			'text' => '<p class="bold" style=color:red;">!! ' .$text .' !!</p>',
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

		$produit = new Product($db);

		$notificationProduit = new Notifs($db);
		$notifs = $notificationProduit->fetchAll();

		// Vérifier s'il y a des résultats
		if ($notifs > 0) {

			// Créer un tableau pour stocker les lignes de la boîte
			$info_box_contents = array();

			$product_info2 = array(
				0 => array(
					'tr' => 'class="center bold" style="color:mediumblue;"',
					'text' => 'Action',
				),
				1 => array(
					'tr' => 'class="center bold"',
					'text' => 'Réference',
				),
				2 => array(
					'tr' => 'class="center bold" ',
					'text' => 'Modification',
				),
				3 => array(
					'tr' => 'class="center bold"',
					'text' => 'Action',
				),
				4 => array(
					'tr' => 'class="center bold"',
					'text' => 'Validation',
				),
			);

			$info_box_contents[] = $product_info2;

			// Parcourir les résultats
			foreach ($notifs as $notif) {
				// Accéder aux colonnes de chaque ligne
				$ref_produit = $notif->ref;
				$id_notification = $notif->id;
				$utilisateur_modificateur = $notif->user_modif;
				$text_modification = $notif->text;

				if ($notif->action === 'PRODUCT_CREATE') {
					$notif_action = "Produit créé";
				} else if ($notif->action === 'PRODUCT_MODIFY') {
					$notif_action = "Produit modifié";
				} else if ($notif->action === 'PRODUCT_DELETE') {
					$notif_action = "Produit supprimé";
				}

				// Afficher le lien de la ref produit avec getNomUrl()
				if ($produit->fetch('', $ref_produit)) {
					$ref_produit = $produit->getNomUrl(1);
				}
				// Créer un tableau pour stocker les informations de chaque produit
				$product_info = array(
					0 => array(
						'td' => 'class="center"',
						'text' => $notif_action,
					),
					1 => array(
						'td' => 'class="center"',
						'text' => $ref_produit,
						'asis' => 1,
					),
					2 => array(
						'td' => 'class="center"',
						'text' => $utilisateur_modificateur,
					),
					3 => array(
						'td' => 'class="center"',
						'text' => $text_modification,
					),
					4 => array(
						'td' => 'class="monBouton center"' . "id=" . $id_notification,
						'text' => '<span><i class="fa fa-check"></i></span>',
					),
				);
				// Ajoute les informations du produit au tableau de la boxe
				$info_box_contents[] = $product_info;

			}
			// Affecte le tableau d'informations à la boxe
			$this->info_box_contents = $info_box_contents;
		} else {
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
