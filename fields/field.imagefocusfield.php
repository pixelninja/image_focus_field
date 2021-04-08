<?php

	require_once(TOOLKIT . '/class.xsltprocess.php');

	Class fieldImageFocusField extends Field {

		/**
		 * The constructor
		 * @param  $parent		The parent, provided by Symphony
		 */
		public function __construct() {
			parent::__construct();
			$this->_name = __('Image Focus');
			$this->_required = true;

			$this->set('required', 'no');
			$this->set('show_column', 'no');
			$this->set('location', 'main');
			$this->set('field_id', null);
		}

		public function allowDatasourceParamOutput() {
			return !is_null($this->get('field_id')) ? true : false;
		}

		/**
		 * The settings-panel on the blueprints-screen
		 * @param  $wrapper		The wrapper, provided by Symphony
		 * @param null $errors	Errors
		 * @return void
		 */
		public function displaySettingsPanel(&$wrapper, $errors = null) {
			parent::displaySettingsPanel($wrapper, $errors);
			$field = FieldManager::fetch((int)$this->get('id'));

			if (is_null($field)) return;

			$section_id = $field->get('parent_section');
			$fields = FieldManager::fetch(null, $section_id);
			$options = array();
			$options[] = array('0', false, ' ');

			foreach($fields as $field) {
				if ($field->get('type') !== 'medialibraryfield') continue;

				$name = $field->get('label');
				$id	  = $field->get('id');
				$selected  = $id == $this->get('media_field');
				$options[] = array($id, $selected, $name);
			}

			$label = Widget::Label(__('Select a section to dynamically select an image from:'));
			$selectBox = Widget::Select('fields['.$this->get('sortorder').'][field_id]', $options);
			$label->appendChild($selectBox);
			$wrapper->appendChild($label);
		}

		/**
		 * Save the settings-panel in the blueprints-section
		 * @return bool		True on success, false on failure
		 */
		public function commit() {
			if(!parent::commit()) return false;

			$id = $this->get('id');
			$handle = $this->handle();

			if($id === false) return false;

			$fields = array(
				'field_id' => $id,
				'media_field' => $this->get('field_id')
			);

			return Symphony::Database()->insert($fields, "tbl_fields_{$handle}", true);
		}

		/**
		 * The publish-panel on the entry editor:
		 * @param  $wrapper					The wrapper, provided by Symphony
		 * @param null $data				The data
		 * @param null $flagWithError		Should the error box be shown?
		 * @param null $fieldnamePrefix
		 * @param null $fieldnamePostfix
		 * @return void
		 */
		public function displayPublishPanel(XMLElement &$wrapper, $data = NULL, $flagWithError = NULL, $fieldnamePrefix = NULL, $fieldnamePostfix = NULL, $entry_id = NULL) {
			extension_image_focus_field::appendAssets();

			$label = Widget::Label($this->get('label'));
			if($this->get('required') != 'yes') $label->appendChild(new XMLElement('i', __('Optional')));

			$image_focus_wrapper = new XMLElement('div', null, array('class' => 'image_focus_wrapper', 'data-field-id' => $this->get('media_field')));
			$label->appendChild($image_focus_wrapper);
			
			$position = $data == null ? null : $data['xpos'].','.$data['ypos'];
			$label->appendChild(Widget::Input('fields['.$this->get('element_name').'][position]', (strlen($position) != 0 ? $position : NULL), 'hidden'));

			if($flagWithError != NULL) {
				$wrapper->appendChild(Widget::wrapFormElementWithError($label, $flagWithError));
			}
			else {
				$wrapper->appendChild($label);
			}
		}

		/**
		 * Store the information when an entry is created or edited:
		 * @param  $data			The data
		 * @param  $status			The status
		 * @param bool $simulate	Simulate or not?
		 * @param null $entry_id	The ID of the entry
		 * @return array			The result
		 */
		public function processRawFieldData($data, &$status, &$message = NULL, $simulate = false, $entry_id = NULL) {
			$status = self::__OK__;

			$coords = explode(',', $data['position']);

			if(count($coords) != 2) {
				$coords = array(0, 0);
			}

			$result = array(
				'xpos' => $coords[0],
				'ypos' => $coords[1]
			);

			return $result;
		}

		/**
		 * Add the XML element to the datasource output:
		 * @param  $wrapper			The wrapper, provided by Symphony
		 * @param  $data			The data
		 * @param bool $encode		Should encoding be used?
		 * @return void
		 */
		public function appendFormattedElement(XMLElement &$wrapper, $data, $encode = false, $mode = NULL, $entry_id = NULL) {
			if(empty($data)) return;

			$wrapper->appendChild(
				new XMLElement(
					$this->get('element_name'), null, array('xpos' => $data['xpos'], 'ypos' => $data['ypos'], 'unit' => $unit)
				)
			);
		}

		/**
		 * The data to show in the table
		 * @param  $data					The data
		 * @param null|XMLElement $link		The link
		 * @return							The value to show in the table
		 */
		function prepareTableValue($data, ?XMLElement $link = NULL, $entry_id = NULL) {
			if(empty($data)) return;
			return $data['xpos'].$unit.', '.$data['ypos'].$unit;
		}

		/**
		 * Create the table for each field
		 * @return bool
		 */
		public function createTable() {
			return Symphony::Database()->query(
				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_" . $this->get('id') . "` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`entry_id` int(11) unsigned NOT NULL,
					`xpos` float default NULL,
					`ypos` float default NULL,
					PRIMARY KEY	 (`id`),
					KEY `entry_id` (`entry_id`)
				);"
			);
		}
	}
