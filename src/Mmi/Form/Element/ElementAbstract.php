<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2015 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace Mmi\Form\Element;

/**
 * Abstrakcyjna klasa elementu formularza
 */
abstract class ElementAbstract extends \Mmi\OptionObject {

	/**
	 * Błędy pola
	 * @var array
	 */
	protected $_errors = [];

	/**
	 * Formularz macierzysty
	 * @var \Mmi\Form\Form
	 */
	protected $_form = null;

	/**
	 * Konstruktor, ustawia nazwę pola i opcje
	 * @param string $name nazwa
	 */
	public function __construct($name) {
		$this->setName($name)
			->setRequired(false)
			->setRequiredAsterisk('*')
			->setLabelPostfix(':')
			->setIgnore(false)
			->addClass('field');
	}

	/**
	 * Dodaje klasę do elementu
	 * @param string $className nazwa klasy
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function addClass($className) {
		return $this->setOption('class', trim($this->getOption('class') . ' ' . $className));
	}

	/**
	 * Ustawia ID
	 * @param integer $id
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function setId($id) {
		return $this->setOption('id', $id);
	}

	/**
	 * Pobiera ID
	 * @return type
	 */
	public final function getId() {
		return $this->getOption('id');
	}

	/**
	 * Ustawia nazwę pola formularza
	 * @param mixed $name wartość
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function setName($name) {
		return $this->setOption('name', $name);
	}

	/**
	 * Pobiera nazwę pola formularza
	 * @return string
	 */
	public final function getName() {
		return $this->getOption('name');
	}

	/**
	 * Ustawia wartość pola formularza
	 * @param mixed $value wartość
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function setValue($value) {
		//ustawia filtrowaną wartość
		return $this->setOption('value', $this->_applyFilters($value));
	}

	/**
	 * Pobiera wartość pola formularza
	 * @return mixed
	 */
	public final function getValue() {
		return $this->getOption('value');
	}

	/**
	 * Ustawia opis
	 * @param string $description
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function setDescription($description) {
		return $this->setOption('data-description', $description);
	}

	/**
	 * Pobiera opis
	 * @return string
	 */
	public final function getDescription() {
		return $this->getOption('data-description');
	}

	/**
	 * Ustawia placeholder (HTML5)
	 * @param string $placeholder
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function setPlaceholder($placeholder) {
		return $this->setOption('placeholder', $placeholder);
	}

	/**
	 * Ustawia ignorowanie pola
	 * @param bool $ignore
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function setIgnore($ignore = true) {
		return $this->setOption('data-ignore', (bool) $ignore);
	}

	/**
	 * Zwraca czy pole jest ignorowane
	 * @return boolean
	 */
	public final function getIgnore() {
		return (bool) $this->getOption('data-ignore');
	}

	/**
	 * Ustawia wyłączenie pola
	 * @param bool $disabled
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function setDisabled($disabled = true) {
		return $disabled ? $this->setOption('disabled', '') : $this;
	}

	/**
	 * Zwraca czy pole jest wyłączone
	 * @return boolean
	 */
	public final function getDisabled() {
		return null !== $this->getOption('disabled');
	}

	/**
	 * Ustawia pole do odczytu
	 * @param boolean $readOnly
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function setReadOnly($readOnly = true) {
		return $readOnly ? $this->setOption('readonly', '') : $this;
	}

	/**
	 * Ustawia label pola
	 * @param string $label
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function setLabel($label) {
		return $this->setOption('data-label', $label);
	}

	/**
	 * Pobiera label
	 * @return string
	 */
	public final function getLabel() {
		return $this->getOption('data-label');
	}

	/**
	 * Ustawia postfix labela
	 * @param string $labelPostfix postfix labelki
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function setLabelPostfix($labelPostfix) {
		return $this->setOption('data-labelPostfix', $labelPostfix);
	}

	/**
	 * Pobiera postfix labelki
	 * @return string
	 */
	public final function getLabelPostfix() {
		return $this->getOption('data-labelPostfix');
	}

	/**
	 * Ustawia czy pole jest wymagane
	 * @param bool $required wymagane
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function setRequired($required = true) {
		return $this->setOption('data-required', (bool) $required);
	}

	/**
	 * Zwraca czy pole jest wymagane
	 * @return boolean
	 */
	public final function getRequired() {
		return (bool) $this->getOption('data-required');
	}

	/**
	 * Ustawia symbol gwiazdki pól wymaganych
	 * @param string $asterisk
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function setRequiredAsterisk($asterisk = '*') {
		return $this->setOption('data-requiredAsterisk', $asterisk);
	}

	/**
	 * Ustawia wszystkie opcje wyboru na podstawie tabeli
	 * @param array $multiOptions opcje
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function setMultiOptions(array $multiOptions = []) {
		return $this->setOption('multiOptions', $multiOptions);
	}

	/**
	 * Zwraca multi opcje pola
	 * @return array
	 */
	public final function getMultiOptions() {
		return is_array($this->getOption('multiOptions')) ? $this->getOption('multiOptions') : [];
	}

	/**
	 * Dodaje opcję wyboru
	 * @param string $value wartość
	 * @param string $caption nazwa
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function addMultiOption($value, $caption) {
		$multiOptions = $this->getMultiOptions();
		$multiOptions[$value] = $caption;
		return $this->setOption('multiOptions', $multiOptions);
	}

	/**
	 * Dodaje walidator
	 * @param string $name nazwa
	 * @param string $options opcje
	 * @param string $message wiadomość
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function addValidator($name, array $options = [], $message = null) {
		$validators = $this->getValidators();
		$validator = ['validator' => $name, 'options' => $options];
		if ($message !== null) {
			$validator['message'] = $message;
		}
		$validators[] = $validator;
		return $this->setOption('validators', $validators);
	}

	/**
	 * Pobiera walidatory
	 * @return array
	 */
	public final function getValidators() {
		return is_array($this->getOption('validators')) ? $this->getOption('validators') : [];
	}

	/**
	 * Dodaje filtr
	 * @param string $name nazwa
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function addFilter($name, array $options = []) {
		$filters = $this->getFilters();
		$filters[] = ['filter' => $name, 'options' => $options];
		return $this->setOption('data-filters', $filters);
	}

	/**
	 * Pobiera walidatory
	 * @return array
	 */
	public final function getFilters() {
		return is_array($this->getOption('data-filters')) ? $this->getOption('data-filters') : [];
	}

	/**
	 * Ustawia form macierzysty
	 * @param \Mmi\Form\Form $form
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function setForm(\Mmi\Form\Form $form) {
		$this->_form = $form;
		//ustawianie ID
		$this->setId($form->getBaseName() . '-' . $this->getName());
		return $this;
	}

	/**
	 * Waliduje pole
	 * @return boolean
	 */
	public final function isValid() {
		$result = true;
		//waliduje poprawnie jeśli niewymagane, ale tylko gdy niepuste
		if (!($this->getRequired() || $this->getValue() != '')) {
			return true;
		}
		foreach ($this->getValidators() as $validator) {
			$options = [];
			$message = null;
			if (is_array($validator)) {
				$options = isset($validator['options']) ? $validator['options'] : [];
				$message = isset($validator['message']) ? $validator['message'] : null;
				$validator = $validator['validator'];
			}
			$v = $this->_getValidator($validator);
			$v->setOptions($options);
			if (!$v->isValid($this->getValue())) {
				$result = false;
				$this->addError(($message !== null) ? $message : $v->getError());
			}
		}
		return $result;
	}

	/**
	 * Zwraca czy pole ma błędy
	 * @return boolean
	 */
	public final function hasErrors() {
		return !empty($this->_errors);
	}

	/**
	 * Pobiera błędy pola
	 * @return array
	 */
	public final function getErrors() {
		return $this->_errors;
	}

	/**
	 * Dodaje błąd
	 * @param string $error
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function addError($error) {
		$this->_errors[] = $error;
		return $this;
	}

	/**
	 * Pobiera obiekt filtra
	 * @param string $name nazwa filtra
	 * @return \Mmi\Filter\FilterAbstract
	 */
	protected final function _getFilter($name) {
		return \Mmi\App\FrontController::getInstance()->getView()->getFilter($name);
	}

	/**
	 * Pobiera nazwę walidatora
	 * @param string $name nazwa walidatora
	 * @return \Mmi\Validator\ValidatorAbstract
	 */
	protected final function _getValidator($name) {
		$structure = \Mmi\App\FrontController::getInstance()->getStructure('validator');
		foreach ($structure as $namespace => $validators) {
			if (!isset($validators[$name])) {
				continue;
			}
			$className = '\\' . $namespace . '\\Validator\\' . ucfirst($name);
		}
		if (!isset($className)) {
			throw new \Mmi\Form\FormException('Unknown validator: ' . $name);
		}
		return new $className();
	}

	/**
	 * Filtruje daną wartość za pomocą filtrów pola
	 * @param mixed $value wartość
	 * @return mixed wynik filtracji
	 */
	protected function _applyFilters($value) {
		//iteracja po filtrach
		foreach ($this->getFilters() as $filter) {
			//pobranie filtra, ustawienie opcji i filtracja zmiennej
			$value = $this->_getFilter($filter['filter'])
				->setOptions(isset($filter['options']) ? $filter['options'] : [])
				->filter($value);
		}
		return $value;
	}

	/**
	 * Buduje opcje HTML
	 * @return string
	 */
	protected final function _getHtmlOptions() {
		if (!empty($this->getValidators())) {
			$this->addClass('validate');
		}
		$html = '';
		//iteracja po opcjach do HTML
		foreach ($this->getOptions() as $key => $value) {
			if (is_array($value)) {
				continue;
			}
			$html .= $key . '="' . str_replace('"', '&quot;', $value) . '" ';
		}
		//zwrot html
		return $html;
	}

	/**
	 * Dodaje walidator alfanumeryczny
	 * @param string $message opcjonalny komunikat błędu
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public function addValidatorAlnum($message = null) {
		return $this->addValidator('alnum', [], $message);
	}

	/**
	 * Dodaje walidator dat
	 * @param string $message opcjonalny komunikat błędu
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public function addValidatorDate($message = null) {
		return $this->addValidator('date', [], $message);
	}

	/**
	 * Dodaje walidator e-maili
	 * @param string $message opcjonalny komunikat błędu
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public function addValidatorEmailAddress($message = null) {
		return $this->addValidator('emailAddress', [], $message);
	}

	/**
	 * Dodaje walidator listy e-maili
	 * @param string $separator separator adresów e-mail, domyślnie ";"
	 * @param string $message opcjonalny komunikat błędu
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public function addValidatorEmailAddressList($separator = ';', $message = null) {
		return $this->addValidator('emailAddressList', [$separator], $message);
	}

	/**
	 * Dodaje walidator równości z wartością
	 * @param mixed $value wartość porównania
	 * @param bool $isCheckbox czy checkbox
	 * @param string $message opcjonalny komunikat błędu
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public function addValidatorEqual($value, $isCheckbox = false, $message = null) {
		return $this->addValidator('equal', ['value' => $value, 'checkbox' => (bool) $isCheckbox], $message);
	}

	/**
	 * Dodaje walidator numerów IBAN
	 * @param string $countryPrefix kod kraju np. GB, PL
	 * @param array $allowedCountries lista dozwolonych prefixów
	 * @param $message opcjonalny komunikat błędu
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public function addValidatorIban($countryPrefix = 'PL', array $allowedCountries = [], $message = null) {
		return $this->addValidator('iban', [$countryPrefix, $allowedCountries], $message);
	}

	/**
	 * Walidacja całkowitych
	 * @param bool $positive czy tylko naturalne
	 * @param string $message opcjonalny komunikat błędu
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public function addValidatorInteger($positive = false, $message = null) {
		return $this->addValidator('integer', ['positive' => $positive], $message);
	}

	/**
	 * Walidacja udziału wielkich liter
	 * @param int $percent maksymalny udział
	 * @param string $message opcjonalny komunikat błędu
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public function addValidatorLargeSmall($percent = 40, $message = null) {
		return $this->addValidator('largeSmall', [$percent], $message);
	}

	/**
	 * Walidacja wypełnienia pola
	 * @param string $message opcjonalny komunikat błędu
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public function addValidatorNotEmpty($message = null) {
		return $this->addValidator('notEmpty', [], $message);
	}

	/**
	 * Walidacja od/do
	 * @param mixed $from większa od
	 * @param mixed $to mniejsza od
	 * @param string $message opcjonalny komunikat błędu
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public function addValidatorValueBetween($from = null, $to = null, $message = null) {
		return $this->addValidator('numberBetween', [$from, $to], $message);
	}

	/**
	 * Walidacja numeryczna
	 * @param string $message opcjonalny komunikat błędu
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public function addValidatorNumeric($message = null) {
		return $this->addValidator('numeric', [], $message);
	}

	/**
	 * Walidacja numeryczna
	 * @param string $message opcjonalny komunikat błędu
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public function addValidatorPostal($message = null) {
		return $this->addValidator('postal', [], $message);
	}

	/**
	 * Walidacja unikalności rekordu
	 * @param (\Mmi\Orm\Query $query obiekt zapytania
	 * @param string $fieldName nazwa pola
	 * @param int $id identyfikator istniejącego pola (domyślnie null)
	 * @param string $message opcjonalny komunikat błędu
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public function addValidatorRecordUnique(\Mmi\Orm\Query $query, $fieldName, $id = null, $message = null) {
		return $this->addValidator('recordUnique', [$query, $fieldName, $id], $message);
	}

	/**
	 * Walidacja regex
	 * @param string $pattern wzorzec
	 * @param string $message opcjonalny komunikat błędu
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public function addValidatorRegex($pattern, $message = null) {
		return $this->addValidator('regex', [$pattern], $message);
	}

	/**
	 * Walidacja długości ciągu znaków
	 * @param int $from długość od
	 * @param int $to długość do
	 * @param string $message opcjonalny komunikat błędu
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public function addValidatorStringLength($from, $to, $message = null) {
		return $this->addValidator('stringLength', [\intval($from), \intval($to)], $message);
	}

	/**
	 * Kolejność renderowania pola
	 * @var array
	 */
	protected $_renderingOrder = ['fetchBegin', 'fetchLabel', 'fetchField', 'fetchDescription', 'fetchErrors', 'fetchEnd'];

	/**
	 * Renderer pola
	 * @return string
	 */
	public function __toString() {
		try {
			$html = '';
			//ustawienie nazwy po nazwie forma
			if ($this->_form) {
				$this->setName($this->_form->getBaseName() . '[' . rtrim($this->getName(), '[]') . ']' . (substr($this->getName(), -2) == '[]' ? '[]' : ''));
			}
			foreach ($this->_renderingOrder as $method) {
				if (!method_exists($this, $method)) {
					continue;
				}
				$html .= $this->{$method}();
			}
			return $html;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * Ustaw kolejność realizacji
	 * @param array $renderingOrder
	 * @return \Mmi\Form\Element\ElementAbstract
	 */
	public final function setRenderingOrder(array $renderingOrder = []) {
		foreach ($renderingOrder as $method) {
			if (!method_exists($this, $method)) {
				throw new \Mmi\Form\FormException('Unknown rendering method');
			}
		}
		$this->_renderingOrder = $renderingOrder;
		return $this;
	}

	/**
	 * Buduje kontener pola (początek)
	 * @return string
	 */
	public final function fetchBegin() {
		$class = get_class($this);
		$this->addClass(strtolower(substr($class, strrpos($class, '\\') + 1)));
		if ($this->hasErrors()) {
			$this->addClass('error');
		}
		return '<div id ="' . $this->getId() . '-container" class="' . $this->getOption('class') . '">';
	}

	/**
	 * Buduje kontener pola (koniec)
	 * @return string
	 */
	public final function fetchEnd() {
		return '<div class="clear"></div></div>';
	}

	/**
	 * Buduje etykietę pola
	 * @return string
	 */
	public function fetchLabel() {
		if (!$this->getLabel()) {
			return;
		}
		$forHtml = $this->getId() ? ' for="' . $this->getId() . '" id="' . $this->getId() . '-label"' : '';
		$requiredClass = '';
		$required = '';
		if ($this->getRequired()) {
			$requiredClass = ' class="required"';
			$required = '<span class="required">' . $this->getOption('data-requiredAsterisk') . '</span>';
		}
		//zwrot html
		return '<label' . $forHtml . $requiredClass . '>' . $this->getLabel() . $this->getOption('data-labelPostfix') . $required . '</label>';
	}

	/**
	 * Buduje pole
	 * @return string
	 */
	public abstract function fetchField();

	/**
	 * Buduje opis pola
	 * @return string
	 */
	public final function fetchDescription() {
		//brak opisu
		if (!$this->getDescription()) {
			return;
		}
		$id = $this->getId() ? ' id="' . $this->getId() . '_description"' : '';
		$description = $this->getDescription();
		return '<div' . $id . ' class="description">' . $description . '</div>';
	}

	/**
	 * Buduje błędy pola
	 * @return string
	 */
	public final function fetchErrors() {
		$idHtml = $this->getId() ? ' id="' . $this->getId() . '-errors"' : '';
		$html = '<div class="errors"' . $idHtml . '>';
		if ($this->hasErrors()) {
			$html .= '<span class="marker"></span>'
				. '<ul>'
				. '<li class="point first"></li>';
			foreach ($this->_errors as $error) {
				$html .= '<li class="notice error"><i class="icon-remove-sign icon-large"></i>' . $error . '</li>';
			}
			$html .= '<li class="close last"></li>'
				. '</ul>';
		}
		$html .= '<div class="clear"></div></div>';
		return $html;
	}

}
