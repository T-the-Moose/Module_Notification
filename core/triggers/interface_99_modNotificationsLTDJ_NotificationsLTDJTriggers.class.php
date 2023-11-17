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
 * \file    core/triggers/interface_99_modNotificationsLTDJ_NotificationsLTDJTriggers.class.php
 * \ingroup notificationsltdj
 * \brief   Example trigger.
 *
 * Put detailed description here.
 *
 * \remarks You can create other triggers by copying this one.
 * - File name should be either:
 *      - interface_99_modNotificationsLTDJ_MyTrigger.class.php
 *      - interface_99_all_MyTrigger.class.php
 * - The file must stay in core/triggers
 * - The class name must be InterfaceMytrigger
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/notificationsltdj/core/boxes/notificationsltdjwidget1.php';
require_once DOL_DOCUMENT_ROOT.'/custom/notificationsltdj/class/affichage.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/notificationsltdj/class/config.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';

/**
 *  Class of triggers for NotificationsLTDJ module
 */

class InterfaceNotificationsLTDJTriggers extends DolibarrTriggers
{
	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "demo";
		$this->description = "NotificationsLTDJ triggers.";
		// 'development', 'experimental', 'dolibarr' or version
		$this->version = 'development';
		$this->picto = 'notificationsltdj@notificationsltdj';
	}

	/**
	 * Factory method for Notifs
	 *
	 **/
	private function creationNotification(User $user, $object, $type, $text = ''): Notifs
	{
		global $db;
		$dateDuJour = dol_now('tzserver');
		$notif = new Notifs($db);

		$notif->entity = $user->entity;
		$notif->ref = $db->escape($object->ref);
		$notif->type = $type;
		$notif->label = $db->escape(addslashes($object->label));
		$notif->date_creation = $dateDuJour;
		$notif->tms = $dateDuJour;
		$notif->fk_user_modif = $user->id;
		$notif->text = $db->escape($text);

		$notif->create($user);

		return $notif;
	}

	/**
	 * Managing notifications
	 *
	 */
	private function manageNotification($action, $object, $user, $dernierIdNotif) {

		global $db;
		$dateDuJour = dol_now('tzserver');

		$config = new Config($db);
		$configuration = $config->fetchAll();

		foreach ($configuration as $item) {
			$configType = $item->type;
			$configUtilisateurModif = $item->fk_user_modif;
			$configGroupeIdJson = $item->group_id_json;
			$configUtilisateurIdJson = $item->user_id_json;
			$configImportantGroupe = $item->is_important_group;
			$configImportantUtilisateur = $item->is_important_user;

			if ($configType === $action) {
				$idUtilisateurDecode = json_decode($configUtilisateurIdJson, true);
				$idGroupeDecode = json_decode($configGroupeIdJson, true);

				$idUniqueUtilisateur = [];

				foreach ($idGroupeDecode as $groupeId) {
					$userGroup = new UserGroup($db);
					$userGroup->fetch($groupeId);
					$utilisateursDansGroupes = $userGroup->listUsersForGroup();

					foreach ($utilisateursDansGroupes as $utilisateurDansUnGroupe) {
						$idUniqueUtilisateur[] = $utilisateurDansUnGroupe->id;
					}
				}

				// Fusion des deux tableaux d'Ids
				$idUniqueUtilisateur = array_merge($idUniqueUtilisateur, $idUtilisateurDecode);

				// Élimine les doublons
				$idUniqueUtilisateur = array_unique($idUniqueUtilisateur);

				foreach ($idUniqueUtilisateur as $userId) {
					$affichage = new Affichage($db);
					$affichage->date_creation = $dateDuJour;
					$affichage->tms = $dateDuJour;
					$affichage->fk_user_modif = $configUtilisateurModif;

					// Vérifie si l'id de l'utilisateur fait partie des utilisateurs individuels
					$isUserImportant = in_array($userId, $idUtilisateurDecode) && $configImportantUtilisateur == 1;

					// Vérifie si l'id de l'utilisateur fait partie des utilisateurs d'un groupe
					$isGroupImportant = in_array($userId, $idGroupeDecode) && $configImportantGroupe == 1;

					$affichage->is_important = $isUserImportant || $isGroupImportant ? 1 : 0;

					// Fetch l'id de la dernière notification en signature de fonction
					$affichage->id_notif = $dernierIdNotif;

					$affichage->id_user = $userId;
					$affichage->create($user);
				}
			}
		}
	}


	/**
	 * Trigger name
	 *
	 * @return string Name of trigger file
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Trigger description
	 *
	 * @return string Description of trigger file
	 */
	public function getDesc()
	{
		return $this->description;
	}

	/**
	 * Function called when a Dolibarrr business event is done.
	 * All functions "runTrigger" are triggered if file
	 * is inside directory core/triggers
	 *
	 * @param string 		$action 	Event action code
	 * @param CommonObject 	$object 	Object
	 * @param User 			$user 		Object user
	 * @param Translate 	$langs 		Object langs
	 * @param Conf 			$conf 		Object conf
	 * @return int              		<0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
	{
		if (empty($conf->notificationsltdj) || empty($conf->notificationsltdj->enabled)) {
			return 0; // If module is not enabled, we do nothing
		}

		// Put here code you want to execute when a Dolibarr business events occurs.
		// Data and type of action are stored into $object and $action

		// You can isolate code for each action in a separate method: this method should be named like the trigger in camelCase.
		// For example : COMPANY_CREATE => public function companyCreate($action, $object, User $user, Translate $langs, Conf $conf)
		$methodName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($action)))));
		$callback = array($this, $methodName);
		if (is_callable($callback)) {
			dol_syslog(
				"Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id
			);

			return call_user_func($callback, $action, $object, $user, $langs, $conf);
		};

		// Or you can execute some code here
		switch ($action) {

			// Products
			case 'PRODUCT_CREATE':
				$this->produitCree($action, $object, $user);
				break;
			case 'PRODUCT_MODIFY':
				$this->produitModifie($action, $object, $user);
				break;
			case 'PRODUCT_DELETE':
				$this->produitSupprime($action, $object, $user);
				break;

			default:
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
				break;
		}
		return 0;
	}

	private function produitCree(string $action, Product $object, User $user): void
	{
		$res = $this->creationNotification($user, $object, "PRODUCT_CREATE", $user->login . " a créé un produit");
		$this->manageNotification('PRODUCT_CREATE', $object, $user, $res->id);
	}

	private function produitModifie(string $action, Product $object, User $user): void
	{
		$text = '';

		// Vérification des anciennes ref et label
		if ($object->ref != $object->oldcopy->ref) {
			$text .= "Ancienne réf : " . $object->oldcopy->ref . ". ";
		}
		if ($object->label != $object->oldcopy->label) {
			$text .= "Ancien label : " . $object->oldcopy->label . ". ";
		}
		if ($object->label == $object->oldcopy->label || $object->ref == $object->oldcopy->ref) {
			$text .= "Modification d'un prix";
		}
		$res = $this->creationNotification($user, $object, "PRODUCT_MODIFY", $text);
		$this->manageNotification('PRODUCT_MODIFY', $object, $user, $res->id);
	}

	private function produitSupprime(string $action, Product $object, User $user): void
	{
		$res = $this->creationNotification($user, $object, "PRODUCT_DELETE", $user->login . " a supprimé un produit");
		$this->manageNotification('PRODUCT_DELETE', $object, $user, $res->id);
	}

}
