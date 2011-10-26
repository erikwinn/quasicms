<?php

/************************
* This is the Advanced List Item class allowing us to specify custom conditions in a drop down 
* list to filter a column on in a data grid.
*
* This is released under the MIT license. See the README.txt file for more details.
*
* @author Gagandeep Grewal, ggrewal@icomproductions.ca
* @copyright ICOM Productions, Inc. 2007 - 2008
* @name AdvancedListItem
* @version 1.0.0
*/

class AdvancedListItem {
	//member variables
	
	//name of the list item
	protected $name;
	//Filter to be applied for this item
	protected $filter;
	
	//Default constructor
	public function __construct($name, $filter=null) {
		$this->name = $name;
		$this->filter = $filter;
	}
	
	//Set function for public properties
	public function __set($strName, $mixValue) {
		switch ($strName) {
			case "Name":
				$this->name = QType::Cast($mixValue, QType::String);
				break;
			case "Filter":
				$this->filter = QType::Cast($mixValue, QType::Object);
				break;
		}
	}
	
	//Get function for public properties
	public function __get($strName) {
		switch ($strName) {
			case "Name":
				return $this->name;
			
			case "Filter":
				return $this->filter;
		}
	}
}

?>