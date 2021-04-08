<?php

	Class extension_image_focus_field extends Extension{

	/*-------------------------------------------------------------------------
		Installation:
	-------------------------------------------------------------------------*/

		public function install(){
			try {
				Symphony::Database()->query("
					CREATE TABLE IF NOT EXISTS `tbl_fields_imagefocusfield` (
						`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
						`field_id` INT(11) UNSIGNED NOT NULL,
						`media_field` INT(11) UNSIGNED DEFAULT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `field_id` (`field_id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
				");
			}
			catch (Exception $ex) {
				$extension = $this->about();
				Administration::instance()->Page->pageAlert(__('An error occurred while installing %s. %s', array($extension['name'], $ex->getMessage())), Alert::ERROR);
				return false;
			}

			return true;
		}

		public function uninstall(){
			if(parent::uninstall() == true){
				try {
					Symphony::Database()->query("DROP TABLE `tbl_fields_imagefocusfield`");

					return true;
				}
				catch (Exception $ex) {
					$extension = $this->about();
					Administration::instance()->Page->pageAlert(__('An error occurred while uninstalling %s. %s', array($extension['name'], $ex->getMessage())), Alert::ERROR);
					return false;
				}
			}

			return false;
		}

	/*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/
		public static function appendAssets() {
			if(class_exists('Administration')
				&& Administration::instance() instanceof Administration
				&& Administration::instance()->Page instanceof HTMLPage
			) {
				Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/image_focus_field/assets/focus.css', 'screen', 100, false);
				Administration::instance()->Page->addScriptToHead(URL . '/extensions/image_focus_field/assets/focus.js', 100, false);
			}
		}
	}
