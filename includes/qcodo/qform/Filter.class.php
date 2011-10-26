<?php

/************************
* This is the filter class allowing us to specify custom conditions to filter a column on in
* a data grid.
*
* This is released under the MIT license. See the README.txt file for more details.
*
* @author Gagandeep Grewal, ggrewal@icomproductions.ca
* @copyright ICOM Productions, Inc. 2007
* @name Filter
* @version 1.0.0
*/

class Filter {
	//members for a custom condition
	
	//The node to apply the condition on
	protected $filterNode;
	//The operator
	protected $filterOperator;
	//The value
	protected $filterValue;
	//The prefix
	protected $filterPrefix;
	//The suffix
	protected $filterSuffix;
	
	//or we can specify a QCondition itself
	protected $filterCondition=null;
	
	//Default constructor
	public function __construct($node, $operator=null, $value=null, $prefix = "", $suffix = "") {
		//this check basically determines that we are passing only one parameter to the
		//contructor and in that case it must be the condition itself. Otherwise we take three
		//parameters and make the condition from node, operator and value
		if($operator === null && $node instanceof QQCondition) {
			$this->filterCondition = $node;
		}
		else {
			$this->filterNode = $node;
			$this->filterOperator = $operator;
			$this->filterPrefix = $prefix;
			$this->filterValue = $value;
			$this->filterSuffix = $suffix;
		}
	}

	//function to reset the condition if any of the three parameters might have changed
	public function resetCondition() {
		$this->filterCondition = QQ::_($this->filterNode, $this->filterOperator, $this->filterPrefix.$this->filterValue.$this->filterSuffix);		
	}
	
	//Set function for public properties
	public function __set($strName, $mixValue) {
		switch ($strName) {
			case "Node":
				$this->filterNode = QType::Cast($mixValue, QType::Object);
				$this->resetCondition();
				break;
			case "Operator":
				$this->filterOperator = QType::Cast($mixValue, QType::String);
				$this->resetCondition();
				break;
			case "Prefix":
				$this->filterPrefix = QType::Cast($mixValue, QType::String);
				$this->resetCondition();
				break;
			case "Value":
				$this->filterValue = $mixValue;
				$this->resetCondition();
				break;
			case "Suffix":
				$this->filterSuffix = QType::Cast($mixValue, QType::String);
				$this->resetCondition();
				break;
			case "Condition":
				$this->filterCondition = $mixValue;
				break;
		}
	}
	
	//Get function for public properties
	public function __get($strName) {
		switch ($strName) {
			case "Node":
				return $this->filterNode;
				
			case "Operator":
				return $this->filterOperator;
			
			case "Prefix":
				return $this->filterPrefix;
					
			case "Value":
				return $this->filterValue;
			
			case "Suffix":
				return $this->filterSuffix;
				
			case "Condition":
				if($this->filterCondition === null) {
					if($this->filterNode !== null && $this->filterOperator !== null && $this->filterValue !== null)
						$this->resetCondition();
				}
				return $this->filterCondition;
		}
	}
}

?>