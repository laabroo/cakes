<?php
/**
 * The ModelTask handles creating and updating models files.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 1.2
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('AppShell', 'Console/Command');
App::uses('BakeTask', 'Console/Command/Task');
App::uses('ConnectionManager', 'Model');
App::uses('Model', 'Model');
App::uses('Validation', 'Utility');

/**
 * Task class for creating and updating model files.
 *
 * @package       Cake.Console.Command.Task
 */
class ModelTask extends BakeTask {

/**
 * path to Model directory
 *
 * @var string
 */
	public $path = null;

/**
 * tasks
 *
 * @var array
 */
	public $tasks = array('DbConfig', 'Fixture', 'Test', 'Template');

/**
 * Tables to skip when running all()
 *
 * @var array
 */
	public $skipTables = array('i18n');

/**
 * Holds tables found on connection.
 *
 * @var array
 */
	protected $_tables = array();

/**
 * Holds the model names
 *
 * @var array
 */
	protected $_modelNames = array();

/**
 * Holds validation method map.
 *
 * @var array
 */
	protected $_validations = array();

/**
 * Override initialize
 *
 * @return void
 */
	public function initialize() {
		$this->path = current(App::path('Model'));
	}

/**
 * Execution method always used for tasks
 *
 * @return void
 */
	public function execute() {
		parent::execute();

		if (empty($this->args)) {
			$this->_interactive();
		}

		if (!empty($this->args[0])) {
			$this->interactive = false;
			if (!isset($this->connection)) {
				$this->connection = 'default';
			}
			if (strtolower($this->args[0]) == 'all') {
				return $this->all();
			}
			$model = $this->_modelName($this->args[0]);
			$this->listAll($this->connection);
			$useTable = $this->getTable($model);
			$object = $this->_getModelObject($model, $useTable);
			if ($this->bake($object, false)) {
				if ($this->_checkUnitTest()) {
					$this->bakeFixture($model, $useTable);
					$this->bakeTest($model);
				}
			}
		}
	}

/**
 * Bake all models at once.
 *
 * @return void
 */
	public function all() {
		$this->listAll($this->connection, false);
		$unitTestExists = $this->_checkUnitTest();
		foreach ($this->_tables as $table) {
			if (in_array($table, $this->skipTables)) {
				continue;
			}
			$modelClass = Inflector::classify($table);
			$this->out(__d('cake_console', 'Baking %s', $modelClass));
			$object = $this->_getModelObject($modelClass, $table);
			if ($this->bake($object, false) && $unitTestExists) {
				$this->bakeFixture($modelClass, $table);
				$this->bakeTest($modelClass);
			}
		}
	}

/**
 * Get a model object for a class name.
 *
 * @param string $className Name of class you want model to be.
 * @param string $table Table name
 * @return Model Model instance
 */
	protected function _getModelObject($className, $table = null) {
		if (!$table) {
			$table = Inflector::tableize($className);
		}
		$object = new Model(array('name' => $className, 'table' => $table, 'ds' => $this->connection));
		$fields = $object->schema(true);
		foreach ($fields as $name => $field) {
			if (isset($field['key']) && $field['key'] == 'primary') {
				$object->primaryKey = $name;
				break;
			}
		}
		return $object;
	}

/**
 * Generate a key value list of options and a prompt.
 *
 * @param array $options Array of options to use for the selections. indexes must start at 0
 * @param string $prompt Prompt to use for options list.
 * @param integer $default The default option for the given prompt.
 * @return integer result of user choice.
 */
	public function inOptions($options, $prompt = null, $default = null) {
		$valid = false;
		$max = count($options);
		while (!$valid) {
			foreach ($options as $i => $option) {
				$this->out($i + 1 .'. ' . $option);
			}
			if (empty($prompt)) {
				$prompt = __d('cake_console', 'Make a selection from the choices above');
			}
			$choice = $this->in($prompt, null, $default);
			if (intval($choice) > 0 && intval($choice) <= $max) {
				$valid = true;
			}
		}
		return $choice - 1;
	}

/**
 * Handles interactive baking
 *
 * @return boolean
 */
	protected function _interactive() {
		$this->hr();
		$this->out(__d('cake_console', "Bake Model\nPath: %s", $this->getPath()));
		$this->hr();
		$this->interactive = true;

		$primaryKey = 'id';
		$validate = $associations = array();

		if (empty($this->connection)) {
			$this->connection = $this->DbConfig->getConfig();
		}
		$currentModelName = $this->getName();
		$useTable = $this->getTable($currentModelName);
		$db = ConnectionManager::getDataSource($this->connection);
		$fullTableName = $db->fullTableName($useTable);

		if (in_array($useTable, $this->_tables)) {
			$tempModel = new Model(array('name' => $currentModelName, 'table' => $useTable, 'ds' => $this->connection));
			$fields = $tempModel->schema(true);
			if (!array_key_exists('id', $fields)) {
				$primaryKey = $this->findPrimaryKey($fields);
			}
		} else {
			$this->err(__d('cake_console', 'Table %s does not exist, cannot bake a model without a table.', $useTable));
			$this->_stop();
			return false;
		}
		$displayField = $tempModel->hasField(array('name', 'title'));
		if (!$displayField) {
			$displayField = $this->findDisplayField($tempModel->schema());
		}

		$prompt = __d('cake_console', "Would you like to supply validation criteria \nfor the fields in your model?");
		$wannaDoValidation = $this->in($prompt, array('y', 'n'), 'y');
		if (array_search($useTable, $this->_tables) !== false && strtolower($wannaDoValidation) == 'y') {
			$validate = $this->doValidation($tempModel);
		}

		$prompt = __d('cake_console', "Would you like to define model associations\n(hasMany, hasOne, belongsTo, etc.)?");
		$wannaDoAssoc = $this->in($prompt, array('y', 'n'), 'y');
		if (strtolower($wannaDoAssoc) == 'y') {
			$associations = $this->doAssociations($tempModel);
		}

		$this->out();
		$this->hr();
		$this->out(__d('cake_console', 'The following Model will be created:'));
		$this->hr();
		$this->out(__d('cake_console', "Name:       %s", $currentModelName));

		if ($this->connection !== 'default') {
			$this->out(__d('cake_console', "DB Config:  %s", $this->connection));
		}
		if ($fullTableName !== Inflector::tableize($currentModelName)) {
			$this->out(__d('cake_console', 'DB Table:   %s', $fullTableName));
		}
		if ($primaryKey != 'id') {
			$this->out(__d('cake_console', 'Primary Key: %s', $primaryKey));
		}
		if (!empty($validate)) {
			$this->out(__d('cake_console', 'Validation: %s', print_r($validate, true)));
		}
		if (!empty($associations)) {
			$this->out(__d('cake_console', 'Associations:'));
			$assocKeys = array('belongsTo', 'hasOne', 'hasMany', 'hasAndBelongsToMany');
			foreach ($assocKeys as $assocKey) {
				$this->_printAssociation($currentModelName, $assocKey, $associations);
			}
		}

		$this->hr();
		$looksGood = $this->in(__d('cake_console', 'Look okay?'), array('y', 'n'), 'y');

		if (strtolower($looksGood) == 'y') {
			$vars = compact('associations', 'validate', 'primaryKey', 'useTable', 'displayField');
			$vars['useDbConfig'] = $this->connection;
			if ($this->bake($currentModelName, $vars)) {
				if ($this->_checkUnitTest()) {
					$this->bakeFixture($currentModelName, $useTable);
					$this->bakeTest($currentModelName, $useTable, $associations);
				}
			}
		} else {
			return false;
		}
	}

/**
 * Print out all the associations of a particular type
 *
 * @param string $modelName Name of the model relations belong to.
 * @param string $type Name of association you want to see. i.e. 'belongsTo'
 * @param string $associations Collection of associations.
 * @return void
 */
	protected function _printAssociation($modelName, $type, $associations) {
		if (!empty($associations[$type])) {
			for ($i = 0; $i < count($associations[$type]); $i++) {
				$out = "\t" . $modelName . ' ' . $type . ' ' . $associations[$type][$i]['alias'];
				$this->out($out);
			}
		}
	}

/**
 * Finds a primary Key in a list of fields.
 *
 * @param array $fields Array of fields that might have a primary key.
 * @return string Name of field that is a primary key.
 */
	public function findPrimaryKey($fields) {
		$name = 'id';
		foreach ($fields as $name => $field) {
			if (isset($field['key']) && $field['key'] == 'primary') {
				break;
			}
		}
		return $this->in(__d('cake_console', 'What is the primaryKey?'), null, $name);
	}

/**
 * interact with the user to find the displayField value for a model.
 *
 * @param array $fields Array of fields to look for and choose as a displayField
 * @return mixed Name of field to use for displayField or false if the user declines to choose
 */
	public function findDisplayField($fields) {
		$fieldNames = array_keys($fields);
		$prompt = __d('cake_console', "A displayField could not be automatically detected\nwould you like to choose one?");
		$continue = $this->in($prompt, array('y', 'n'));
		if (strtolower($continue) == 'n') {
			return false;
		}
		$prompt = __d('cake_console', 'Choose a field from the options above:');
		$choice = $this->inOptions($fieldNames, $prompt);
		return $fieldNames[$choice];
	}

/**
 * Handles Generation and user interaction for creating validation.
 *
 * @param Model $model Model to have validations generated for.
 * @return array $validate Array of user selected validations.
 */
	public function doValidation($model) {
		if (!is_object($model)) {
			return false;
		}
		$fields = $model->schema();

		if (empty($fields)) {
			return false;
		}
		$validate = array();
		$this->initValidations();
		foreach ($fields as $fieldName => $field) {
			$validation = $this->fieldValidation($fieldName, $field, $model->primaryKey);
			if (!empty($validation)) {
				$validate[$fieldName] = $validation;
			}
		}
		return $validate;
	}

/**
 * Populate the _validations array
 *
 * @return void
 */
	public function initValidations() {
		$options = $choices = array();
		if (class_exists('Validation')) {
			$options = get_class_methods('Validation');
		}
		sort($options);
		$default = 1;
		foreach ($options as $key => $option) {
			if ($option{0} != '_') {
				$choices[$default] = strtolower($option);
				$default++;
			}
		}
		$choices[$default] = 'none'; // Needed since index starts at 1
		$this->_validations = $choices;
		return $choices;
	}

/**
 * Does individual field validation handling.
 *
 * @param string $fieldName Name of field to be validated.
 * @param array $metaData metadata for field
 * @param string $primaryKey
 * @return array Array of validation for the field.
 */
	public function fieldValidation($fieldName, $metaData, $primaryKey = 'id') {
		$defaultChoice = count($this->_validations);
		$validate = $alreadyChosen = array();

		$anotherValidator = 'y';
		while ($anotherValidator == 'y') {
			if ($this->interactive) {
				$this->out();
				$this->out(__d('cake_console', 'Field: %s', $fieldName));
				$this->out(__d('cake_console', 'Type: %s', $metaData['type']));
				$this->hr();
				$this->out(__d('cake_console', 'Please select one of the following validation options:'));
				$this->hr();
			}

			$prompt = '';
			for ($i = 1; $i < $defaultChoice; $i++) {
				$prompt .= $i . ' - ' . $this->_validations[$i] . "\n";
			}
			$prompt .=  __d('cake_console', "%s - Do not do any validation on this field.\n", $defaultChoice);
			$prompt .= __d('cake_console', "... or enter in a valid regex validation string.\n");

			$methods = array_flip($this->_validations);
			$guess = $defaultChoice;
			if ($metaData['null'] != 1 && !in_array($fieldName, array($primaryKey, 'created', 'modified', 'updated'))) {
				if ($fieldName == 'email') {
					$guess = $methods['email'];
				} elseif ($metaData['type'] == 'string' && $metaData['length'] == 36) {
					$guess = $methods['uuid'];
				} elseif ($metaData['type'] == 'string') {
					$guess = $methods['notempty'];
				} elseif ($metaData['type'] == 'integer') {
					$guess = $methods['numeric'];
				} elseif ($metaData['type'] == 'boolean') {
					$guess = $methods['boolean'];
				} elseif ($metaData['type'] == 'date') {
					$guess = $methods['date'];
				} elseif ($metaData['type'] == 'time') {
					$guess = $methods['time'];
				} elseif ($metaData['type'] == 'inet') {
					$guess = $methods['ip'];
				}
			}

			if ($this->interactive === true) {
				$choice = $this->in($prompt, null, $guess);
				if (in_array($choice, $alreadyChosen)) {
					$this->out(__d('cake_console', "You have already chosen that validation rule,\nplease choose again"));
					continue;
				}
				if (!isset($this->_validations[$choice]) && is_numeric($choice)) {
					$this->out(__d('cake_console', 'Please make a valid selection.'));
					continue;
				}
				$alreadyChosen[] = $choice;
			} else {
				$choice = $guess;
			}

			if (isset($this->_validations[$choice])) {
				$validatorName = $this->_validations[$choice];
			} else {
				$validatorName = Inflector::slug($choice);
			}

			if ($choice != $defaultChoice) {
				if (is_numeric($choice) && isset($this->_validations[$choice])) {
					$validate[$validatorName] = $this->_validations[$choice];
				} else {
					$validate[$validatorName] = $choice;
				}
			}
			if ($this->interactive == true && $choice != $defaultChoice) {
				$anotherValidator = $this->in(__d('cake_console', 'Would you like to add another validation rule?'), array('y', 'n'), 'n');
			} else {
				$anotherValidator = 'n';
			}
		}
		return $validate;
	}

/**
 * Handles associations
 *
 * @param Model $model
 * @return array $associations
 */
	public function doAssociations($model) {
		if (!is_object($model)) {
			return false;
		}
		if ($this->interactive === true) {
			$this->out(__d('cake_console', 'One moment while the associations are detected.'));
		}

		$fields = $model->schema(true);
		if (empty($fields)) {
			return array();
		}

		if (empty($this->_tables)) {
			$this->_tables = $this->getAllTables();
		}

		$associations = array(
			'belongsTo' => array(), 'hasMany' => array(), 'hasOne' => array(), 'hasAndBelongsToMany' => array()
		);

		$associations = $this->findBelongsTo($model, $associations);
		$associations = $this->findHasOneAndMany($model, $associations);
		$associations = $this->findHasAndBelongsToMany($model, $associations);

		if ($this->interactive !== true) {
			unset($associations['hasOne']);
		}

		if ($this->interactive === true) {
			$this->hr();
			if (empty($associations)) {
				$this->out(__d('cake_console', 'None found.'));
			} else {
				$this->out(__d('cake_console', 'Please confirm the following associations:'));
				$this->hr();
				$associations = $this->confirmAssociations($model, $associations);
			}
			$associations = $this->doMoreAssociations($model, $associations);
		}
		return $associations;
	}

/**
 * Find belongsTo relations and add them to the associations list.
 *
 * @param Model $model Model instance of model being generated.
 * @param array $associations Array of in progress associations
 * @return array $associations with belongsTo added in.
 */
	public function findBelongsTo($model, $associations) {
		$fields = $model->schema(true);
		foreach ($fields as $fieldName => $field) {
			$offset = strpos($fieldName, '_id');
			if ($fieldName != $model->primaryKey && $fieldName != 'parent_id' && $offset !== false) {
				$tmpModelName = $this->_modelNameFromKey($fieldName);
				$associations['belongsTo'][] = array(
					'alias' => $tmpModelName,
					'className' => $tmpModelName,
					'foreignKey' => $fieldName,
				);
			} elseif ($fieldName == 'parent_id') {
				$associations['belongsTo'][] = array(
					'alias' => 'Parent' . $model->name,
					'className' => $model->name,
					'foreignKey' => $fieldName,
				);
			}
		}
		return $associations;
	}

/**
 * Find the hasOne and HasMany relations and add them to associations list
 *
 * @param Model $model Model instance being generated
 * @param array $associations Array of in progress associations
 * @return array $associations with hasOne and hasMany added in.
 */
	public function findHasOneAndMany($model, $associations) {
		$foreignKey = $this->_modelKey($model->name);
		foreach ($this->_tables as $otherTable) {
			$tempOtherModel = $this->_getModelObject($this->_modelName($otherTable), $otherTable);
			$modelFieldsTemp = $tempOtherModel->schema(true);

			$pattern = '/_' . preg_quote($model->table, '/') . '|' . preg_quote($model->table, '/') . '_/';
			$possibleJoinTable = preg_match($pattern , $otherTable);
			if ($possibleJoinTable == true) {
				continue;
			}
			foreach ($modelFieldsTemp as $fieldName => $field) {
				$assoc = false;
				if ($fieldName != $model->primaryKey && $fieldName == $foreignKey) {
					$assoc = array(
						'alias' => $tempOtherModel->name,
						'className' => $tempOtherModel->name,
						'foreignKey' => $fieldName
					);
				} elseif ($otherTable == $model->table && $fieldName == 'parent_id') {
					$assoc = array(
						'alias' => 'Child' . $model->name,
						'className' => $model->name,
						'foreignKey' => $fieldName
					);
				}
				if ($assoc) {
					$associations['hasOne'][] = $assoc;
					$associations['hasMany'][] = $assoc;
				}

			}
		}
		return $associations;
	}

/**
 * Find the hasAndBelongsToMany relations and add them to associations list
 *
 * @param Model $model Model instance being generated
 * @param array $associations Array of in-progress associations
 * @return array $associations with hasAndBelongsToMany added in.
 */
	public function findHasAndBelongsToMany($model, $associations) {
		$foreignKey = $this->_modelKey($model->name);
		foreach ($this->_tables as $otherTable) {
			$tempOtherModel = $this->_getModelObject($this->_modelName($otherTable), $otherTable);
			$modelFieldsTemp = $tempOtherModel->schema(true);

			$offset = strpos($otherTable, $model->table . '_');
			$otherOffset = strpos($otherTable, '_' . $model->table);

			if ($offset !== false) {
				$offset = strlen($model->table . '_');
				$habtmName = $this->_modelName(substr($otherTable, $offset));
				$associations['hasAndBelongsToMany'][] = array(
					'alias' => $habtmName,
					'className' => $habtmName,
					'foreignKey' => $foreignKey,
					'associationForeignKey' => $this->_modelKey($habtmName),
					'joinTable' => $otherTable
				);
			} elseif ($otherOffset !== false) {
				$habtmName = $this->_modelName(substr($otherTable, 0, $otherOffset));
				$associations['hasAndBelongsToMany'][] = array(
					'alias' => $habtmName,
					'className' => $habtmName,
					'foreignKey' => $foreignKey,
					'associationForeignKey' => $this->_modelKey($habtmName),
					'joinTable' => $otherTable
				);
			}
		}
		return $associations;
	}

/**
 * Interact with the user and confirm associations.
 *
 * @param array $model Temporary Model instance.
 * @param array $associations Array of associations to be confirmed.
 * @return array Array of confirmed associations
 */
	public function confirmAssociations($model, $associations) {
		foreach ($associations as $type => $settings) {
			if (!empty($associations[$type])) {
				foreach ($associations[$type] as $i => $assoc) {
					$prompt = "{$model->name} {$type} {$assoc['alias']}?";
					$response = $this->in($prompt, array('y', 'n'), 'y');

					if ('n' == strtolower($response)) {
						unset($associations[$type][$i]);
					} elseif ($type == 'hasMany') {
						unset($associations['hasOne'][$i]);
					}
				}
				$associations[$type] = array_merge($associations[$type]);
			}
		}
		return $associations;
	}

/**
 * Interact with the user and generate additional non-conventional associations
 *
 * @param Model $model Temporary model instance
 * @param array $associations Array of associations.
 * @return array Array of associations.
 */
	public function doMoreAssociations($model, $associations) {
		$prompt = __d('cake_console', 'Would you like to define some additional model associations?');
		$wannaDoMoreAssoc = $this->in($prompt, array('y', 'n'), 'n');
		$possibleKeys = $this->_generatePossibleKeys();
		while (strtolower($wannaDoMoreAssoc) == 'y') {
			$assocs = array('belongsTo', 'hasOne', 'hasMany', 'hasAndBelongsToMany');
			$this->out(__d('cake_console', 'What is the association type?'));
			$assocType = intval($this->inOptions($assocs, __d('cake_console', 'Enter a number')));

			$this->out(__d('cake_console', "For the following options be very careful to match your setup exactly.\nAny spelling mistakes will cause errors."));
			$this->hr();

			$alias = $this->in(__d('cake_console', 'What is the alias for this association?'));
			$className = $this->in(__d('cake_console', 'What className will %s use?', $alias), null, $alias );

			if ($assocType == 0) {
				$showKeys = $possibleKeys[$model->table];
				$suggestedForeignKey = $this->_modelKey($alias);
			} else {
				$otherTable = Inflector::tableize($className);
				if (in_array($otherTable, $this->_tables)) {
					if ($assocType < 3) {
						$showKeys = $possibleKeys[$otherTable];
					} else {
						$showKeys = null;
					}
				} else {
					$otherTable = $this->in(__d('cake_console', 'What is the table for this model?'));
					$showKeys = $possibleKeys[$otherTable];
				}
				$suggestedForeignKey = $this->_modelKey($model->name);
			}
			if (!empty($showKeys)) {
				$this->out(__d('cake_console', 'A helpful List of possible keys'));
				$foreignKey = $this->inOptions($showKeys, __d('cake_console', 'What is the foreignKey?'));
				$foreignKey = $showKeys[intval($foreignKey)];
			}
			if (!isset($foreignKey)) {
				$foreignKey = $this->in(__d('cake_console', 'What is the foreignKey? Specify your own.'), null, $suggestedForeignKey);
			}
			if ($assocType == 3) {
				$associationForeignKey = $this->in(__d('cake_console', 'What is the associationForeignKey?'), null, $this->_modelKey($model->name));
				$joinTable = $this->in(__d('cake_console', 'What is the joinTable?'));
			}
			$associations[$assocs[$assocType]] = array_values((array)$associations[$assocs[$assocType]]);
			$count = count($associations[$assocs[$assocType]]);
			$i = ($count > 0) ? $count : 0;
			$associations[$assocs[$assocType]][$i]['alias'] = $alias;
			$associations[$assocs[$assocType]][$i]['className'] = $className;
			$associations[$assocs[$assocType]][$i]['foreignKey'] = $foreignKey;
			if ($assocType == 3) {
				$associations[$assocs[$assocType]][$i]['associationForeignKey'] = $associationForeignKey;
				$associations[$assocs[$assocType]][$i]['joinTable'] = $joinTable;
			}
			$wannaDoMoreAssoc = $this->in(__d('cake_console', 'Define another association?'), array('y', 'n'), 'y');
		}
		return $associations;
	}

/**
 * Finds all possible keys to use on custom associations.
 *
 * @return array array of tables and possible keys
 */
	protected function _generatePossibleKeys() {
		$possible = array();
		foreach ($this->_tables as $otherTable) {
			$tempOtherModel = new Model(array('table' => $otherTable, 'ds' => $this->connection));
			$modelFieldsTemp = $tempOtherModel->schema(true);
			foreach ($modelFieldsTemp as $fieldName => $field) {
				if ($field['type'] == 'integer' || $field['type'] == 'string') {
					$possible[$otherTable][] = $fieldName;
				}
			}
		}
		return $possible;
	}

/**
 * Assembles and writes a Model file.
 *
 * @param mixed $name Model name or object
 * @param mixed $data if array and $name is not an object assume bake data, otherwise boolean.
 * @return string
 */
	public function bake($name, $data = array()) {
		if (is_object($name)) {
			if ($data == false) {
				$data = array();
				$data['associations'] = $this->doAssociations($name);
				$data['validate'] = $this->doValidation($name);
			}
			$data['primaryKey'] = $name->primaryKey;
			$data['useTable'] = $name->table;
			$data['useDbConfig'] = $name->useDbConfig;
			$data['name'] = $name = $name->name;
		} else {
			$data['name'] = $name;
		}
		$defaults = array('associations' => array(), 'validate' => array(), 'primaryKey' => 'id',
			'useTable' => null, 'useDbConfig' => 'default', 'displayField' => null);
		$data = array_merge($defaults, $data);

		$this->Template->set($data);
		$this->Template->set(array(
			'plugin' => $this->plugin,
			'pluginPath' => empty($this->plugin) ? '' : $this->plugin . '.'
		));
		$out = $this->Template->generate('classes', 'model');

		$path = $this->getPath();
		$filename = $path . $name . '.php';
		$this->out("\n" . __d('cake_console', 'Baking model class for %s...', $name), 1, Shell::QUIET);
		$this->createFile($filename, $out);
		ClassRegistry::flush();
		return $out;
	}

/**
 * Assembles and writes a unit test file
 *
 * @param string $className Model class name
 * @return string
 */
	public function bakeTest($className) {
		$this->Test->interactive = $this->interactive;
		$this->Test->plugin = $this->plugin;
		$this->Test->connection = $this->connection;
		return $this->Test->bake('Model', $className);
	}

/**
 * outputs the a list of possible models or controllers from database
 *
 * @param string $useDbConfig Database configuration name
 * @return array
 */
	public function listAll($useDbConfig = null) {
		$this->_tables = $this->getAllTables($useDbConfig);

		$this->_modelNames = array();
		$count = count($this->_tables);
		for ($i = 0; $i < $count; $i++) {
			$this->_modelNames[] = $this->_modelName($this->_tables[$i]);
		}
		if ($this->interactive === true) {
			$this->out(__d('cake_console', 'Possible Models based on your current database:'));
			for ($i = 0; $i < $count; $i++) {
				$this->out($i + 1 . ". " . $this->_modelNames[$i]);
			}
		}
		return $this->_tables;
	}

/**
 * Interact with the user to determine the table name of a particular model
 *
 * @param string $modelName Name of the model you want a table for.
 * @param string $useDbConfig Name of the database config you want to get tables from.
 * @return string Table name
 */
	public function getTable($modelName, $useDbConfig = null) {
		$useTable = Inflector::tableize($modelName);
		if (in_array($modelName, $this->_modelNames)) {
			$modelNames = array_flip($this->_modelNames);
			$useTable = $this->_tables[$modelNames[$modelName]];
		}

		if ($this->interactive === true) {
			if (!isset($useDbConfig)) {
				$useDbConfig = $this->connection;
			}
			$db = ConnectionManager::getDataSource($useDbConfig);
			$fullTableName = $db->fullTableName($useTable, false);
			$tableIsGood = false;
			if (array_search($useTable, $this->_tables) === false) {
				$this->out();
				$this->out(__d('cake_console', "Given your model named '%s',\nCake would expect a database table named '%s'", $modelName, $fullTableName));
				$tableIsGood = $this->in(__d('cake_console', 'Do you want to use this table?'), array('y', 'n'), 'y');
			}
			if (strtolower($tableIsGood) == 'n') {
				$useTable = $this->in(__d('cake_console', 'What is the name of the table?'));
			}
		}
		return $useTable;
	}

/**
 * Get an Array of all the tables in the supplied connection
 * will halt the script if no tables are found.
 *
 * @param string $useDbConfig Connection name to scan.
 * @return array Array of tables in the database.
 */
	public function getAllTables($useDbConfig = null) {
		if (!isset($useDbConfig)) {
			$useDbConfig = $this->connection;
		}

		$tables = array();
		$db = ConnectionManager::getDataSource($useDbConfig);
		$db->cacheSources = false;
		$usePrefix = empty($db->config['prefix']) ? '' : $db->config['prefix'];
		if ($usePrefix) {
			foreach ($db->listSources() as $table) {
				if (!strncmp($table, $usePrefix, strlen($usePrefix))) {
					$tables[] = substr($table, strlen($usePrefix));
				}
			}
		} else {
			$tables = $db->listSources();
		}
		if (empty($tables)) {
			$this->err(__d('cake_console', 'Your database does not have any tables.'));
			$this->_stop();
		}
		return $tables;
	}

/**
 * Forces the user to specify the model he wants to bake, and returns the selected model name.
 *
 * @param string $useDbConfig Database config name
 * @return string the model name
 */
	public function getName($useDbConfig = null) {
		$this->listAll($useDbConfig);

		$enteredModel = '';

		while ($enteredModel == '') {
			$enteredModel = $this->in(__d('cake_console', "Enter a number from the list above,\ntype in the name of another model, or 'q' to exit"), null, 'q');

			if ($enteredModel === 'q') {
				$this->out(__d('cake_console', 'Exit'));
				$this->_stop();
			}

			if ($enteredModel == '' || intval($enteredModel) > count($this->_modelNames)) {
				$this->err(__d('cake_console', "The model name you supplied was empty,\nor the number you selected was not an option. Please try again."));
				$enteredModel = '';
			}
		}
		if (intval($enteredModel) > 0 && intval($enteredModel) <= count($this->_modelNames)) {
			$currentModelName = $this->_modelNames[intval($enteredModel) - 1];
		} else {
			$currentModelName = $enteredModel;
		}
		return $currentModelName;
	}

/**
 * get the option parser.
 *
 * @return void
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		return $parser->description(
				__d('cake_console', 'Bake models.')
			)->addArgument('name', array(
				'help' => __d('cake_console', 'Name of the model to bake. Can use Plugin.name to bake plugin models.')
			))->addSubcommand('all', array(
				'help' => __d('cake_console', 'Bake all model files with associations and validation.')
			))->addOption('plugin', array(
				'short' => 'p',
				'help' => __d('cake_console', 'Plugin to bake the model into.')
			))->addOption('connection', array(
				'short' => 'c',
				'help' => __d('cake_console', 'The connection the model table is on.')
			))->epilog(__d('cake_console', 'Omitting all arguments and options will enter into an interactive mode.'));
	}

/**
 * Interact with FixtureTask to automatically bake fixtures when baking models.
 *
 * @param string $className Name of class to bake fixture for
 * @param string $useTable Optional table name for fixture to use.
 * @return void
 * @see FixtureTask::bake
 */
	public function bakeFixture($className, $useTable = null) {
		$this->Fixture->interactive = $this->interactive;
		$this->Fixture->connection = $this->connection;
		$this->Fixture->plugin = $this->plugin;
		$this->Fixture->bake($className, $useTable);
	}
}
