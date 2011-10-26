<?php
/************************
* This is the Column class used by the QFilteredDataGrid. It contains all column-specific
* information, such as the filter information to use, and the size of this column's textbox
*
* This is released under the MIT license. See the README.txt file for more details.
*
* @author Ryan Peters rpeters@icomproductions.ca
* @copyright ICOM Productions, Inc. 2006-2008
* @name QFilteredDataGridColumn
* @package QFilteredDataGrid
* @subpackage QFilteredDataGridColumn
* @version 4.1.0
*/
//newly added custom filters allowing us to specify custom conditions
//Gagandeep Grewal
//Cleaned up by gibran (reset not a filter type, QQCondition filter type support, misc changes)

abstract class QFilterType {
	const None = '';
	const TextFilter = 'Text';
	const ListFilter = 'Advanced List';
}

class QFilteredDataGridColumn extends QDataGridColumn {
	// BEHAVIOR
	protected $arrFilterByCommand = null; //depreciated
	protected $FilterBoxSize = '10';
	protected $strFilterType = QFilterType::None;
		protected $intFilterColId = null;
    protected $arrFilterList = array();
	
	//The filter this column has applied
	protected $objFilter = null;
	//a Filter that gets applied in addition to $Filter when the user filters on this column
	protected $objFilterCustom = null;
	
	protected $strFilterPrefix = '';
	protected $strFilterPostfix = '';
	
	/////////////////////////
	// Public Properties: GET
	/////////////////////////
	public function __get($strName) {
		switch ($strName) {
			// BEHAVIOR
			case "FilterByCommand": return $this->arrFilterByCommand;
			case "FilterBoxSize": return $this->FilterBoxSize;
			case "FilterType": return $this->strFilterType;
			case "FilterList": return $this->arrFilterList;
			case "FilterColId": return $this->intFilterColId;
			
			case "FilterPrefix": return $this->strFilterPrefix;
			case "FilterPostfix": return $this->strFilterPostfix;
			
			case "FilterCustom": return $this->objFilterCustom;
			case "Filter": return $this->objFilter;
			
			default:
			try {
				return parent::__get($strName);
			} catch (QCallerException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
		}
	}
	
	/////////////////////////
	// Public Properties: SET
	/////////////////////////
	public function __set($strName, $mixValue) {
		switch ($strName) {
			// BEHAVIOR
			case "FilterCustom":
			try {
				$this->objFilterCustom = $mixValue;
				break;
			} catch(QInvalidCastException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
			
			
			case "FilterPrefix":
			try {
				$this->strFilterPrefix = QType::Cast($mixValue, QType::String);
				break;
			} catch (QInvalidCastException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
			case "FilterPostfix":
			try {
				$this->strFilterPostfix = QType::Cast($mixValue, QType::String);
				break;
			} catch (QInvalidCastException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
			
			case "FilterType":
			try {
				$this->strFilterType= QType::Cast($mixValue, QType::String);
				break;
			} catch (QInvalidCastException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
			
			case "FilterColId":
			try {
				$this->intFilterColId = QType::Cast($mixValue, QType::Integer);
				break;
			} catch (QInvalidCastException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
					
			case "FilterByCommand": //depreciated
			try {
				$arr = QType::Cast($mixValue, QType::ArrayType);
				//ensure pre and postfix exist
				if(!isset($arr['prefix']))
					$arr['prefix'] = '';
				if(!isset($arr['postfix']))
					$arr['postfix'] = '';
				$this->arrFilterByCommand = $arr;
				break;
			} catch (QInvalidCastException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
			
			case "Filter":
			try {
				//if($mixValue instanceof Filter)
				//	$this->objFilter = $mixValue;
				//else
					$this->arrFilterList = array($mixValue);
				break;
			} catch(QInvalidCastException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
			
			

			case "FilterBoxSize":
			try {
				$this->FilterBoxSize = QType::Cast($mixValue, QType::Integer);
				$this->FilterType = 'Text';
				break;
			} catch (QInvalidCastException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
			
			case "FilterList":
			try {
				$this->arrFilterList = QType::Cast($mixValue, QType::ArrayType);
				$this->strFilterType = 'List';
				break;
			} catch (QInvalidCastException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
			
			default:
			try {
				parent::__set($strName, $mixValue);
				break;
			} catch (QCallerException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
		}
	}
	
	public function AddListItem($arg1=null, $arg2=null) {
		return $this->FilterAddListItem($arg1, $arg2);
	}
	
	//creates and advanced list or a simple list for a column
	//2 ways of calling the fuction: specify only one paramter and it should be an advanced list item
	//the other way is to call it using 2 parameters with first one being a name and other a value
	public function FilterAddListItem($arg1=null, $arg2=null) {
		if($this->arrFilterList === null)
			$this->arrFilterList = array();
		if($arg1 instanceof AdvancedListItem) {
			$this->arrFilterList[$arg1->Name] = $arg1->Filter;
			//since we are adding an advanced list item make sure to set the 
			//filter type for the column appropriately too
			$this->strFilterType = QFilterType::ListFilter;
		}
		elseif($arg1 !== null && $arg2 instanceof QQCondition)
		{
			//they passed in a name, condition pair
			$this->arrFilterList[$arg1] = $arg2;
			$this->strFilterType = QFilterType::ListFilter;
		}
		//else we are trying to make a simple list but make sure the name is supplied
		elseif ($arg1 !== null){
			$this->arrFilterList[$arg1] = $arg2;
			$this->strFilterType = "List";
		}
		//else fail the function and let the user know about correct use of parameters
		else {
			throw new Exception("Please specify a single AdvancedListItem OR a name and value pair OR a name and QQCondition pair as parameters.");
		}		
	}

	public function FilterActivate($strIndex = 0) {
		if ($this->strFilterType == QFilterType::TextFilter && count($this->arrFilterList) > 1) {
			throw new Exception('Trying to activate a Filter when multiple filters are stored (potential ListFilter).');
			return;
		}
		
		//really, this shouldn't happen
		if(null === $strIndex)
			return $this->FilterClear();
		
		$this->objFilter = $this->arrFilterList[$strIndex];
		return true;
	}
	
	public function FilterSetOperand($mixOperand) {
		try {
			if(null === $this->objFilter)
				return;
			elseif($this->objFilter instanceof QQConditionComparison)
			{
				if ($mixOperand instanceof QQNamedValue)
					$this->objFilter->mixOperand = $mixOperand;
				else if ($mixOperand instanceof QQAssociationNode)
					throw new QInvalidCastException('Comparison operand cannot be an Association-based QQNode', 3);
				else if ($mixOperand instanceof QQCondition)
					throw new QInvalidCastException('Comparison operand cannot be a QQCondition', 3);
				else if ($mixOperand instanceof QQClause)
					throw new QInvalidCastException('Comparison operand cannot be a QQClause', 3);
				else if (!($mixOperand instanceof QQNode)) {
					$mixOperand = $this->strFilterPrefix . $mixOperand . $this->strFilterPostfix;
						$this->objFilter->mixOperand = $mixOperand;
				} else {
					if (!$mixOperand->_ParentNode)
						throw new QInvalidCastException('Unable to cast "' . $mixOperand->_Name . '" table to Column-based QQNode', 3);
					$this->objFilter->mixOperand = $mixOperand;
				}
			}
			elseif($this->objFilter instanceof Filter)
			{
				$this->objFilter->Value = $mixOperand;
			}
			else
				throw new Exception('Trying to set Operand on a filter that does not take operands');
		} catch (QInvalidCastException $objExc) {
			$objExc->IncrementOffset();
			throw $objExc;
		}
	}
	
	//depreciated
	public function btnReset_Click()
	{
		$this->FilterClear();
	}
	public function FilterClear() 
	{
		$this->objFilter = null;
		if($this->arrFilterByCommand !== null)
			$this->arrFilterByCommand['value'] = null;
	}
}
?>
