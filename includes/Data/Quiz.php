<?php

namespace CreatorLms\Data;

use CreatorLms\Abstracts\Data;
use CreatorLms\DataStores\DataStores;

defined( 'ABSPATH' ) || exit;

/**
 * Class Quiz
 * @package CreatorLms\Data
 * @since 1.0.0
 */
class Quiz extends Data {

	/**
	 * Name of the store
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected string $data_store_name = 'quiz';

	/**
	 * Object type
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public string $object_type = 'quiz';

	/**
	 * Quiz data
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected array $data = [
		'title' => '',
		'description' => '',
		'questions' => [],
		'settings' => [],
	];

	/**
	 * Quiz constructor.
	 *
	 * @param mixed $quiz
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function __construct($quiz) {
		if (is_numeric($quiz) && $quiz > 0) {
			$this->set_id($quiz);
		} elseif ($quiz instanceof self) {
			$this->set_id(absint($quiz->get_id()));
		} elseif (!empty($quiz->ID)) {
			$this->set_id(absint($quiz->ID));
		}

		$this->data_store = DataStores::load($this->data_store_name);

		if ($this->get_id() > 0) {
			$this->data_store->read($this);
		}
	}

	/**
	 * Save quiz
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function save() {
		if (!$this->data_store) {
			return $this->get_id();
		}

		if ($this->get_id()) {
			return $this->data_store->update($this);
		} else {
			return $this->data_store->create($this);
		}
	}

	/**
	 * Delete quiz
	 *
	 * @param array $args
	 * @since 1.0.0
	 */
	public function delete($args = array()) {
		return $this->data_store->delete($this, $args);
	}

	/**
	 * Get the quiz title
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_title(): string {
		return $this->data['title'];
	}

	/**
	 * Set the quiz title
	 *
	 * @param string $title
	 * @since 1.0.0
	 */
	public function set_title(string $title) {
		$this->data['title'] = $title;
	}

	/**
	 * Get the quiz description
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_description(): string {
		return $this->data['description'];
	}

	/**
	 * Set the quiz description
	 *
	 * @param string $description
	 * @since 1.0.0
	 */
	public function set_description(string $description) {
		$this->data['description'] = $description;
	}

	/**
	 * Get the quiz questions
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_questions(): array {
		return $this->data['questions'];
	}

	/**
	 * Set the quiz questions
	 *
	 * @param array $questions
	 * @since 1.0.0
	 */
	public function set_questions(array $questions) {
		$this->data['questions'] = $questions;
	}

	/**
	 * Get the quiz settings
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_settings(): array {
		return $this->data['settings'];
	}

	/**
	 * Set the quiz settings
	 *
	 * @param array $settings
	 * @since 1.0.0
	 */
	public function set_settings(array $settings) {
		$this->data['settings'] = $settings;
	}

	/**
	 * Get quiz data as an array.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_data(): array {
		return $this->data;
	}

	/**
	 * Set quiz data from an array.
	 *
	 * @param array $data
	 * @since 1.0.0
	 */
	public function set_data(array $data) {
		$this->data = $data;
	}
}
