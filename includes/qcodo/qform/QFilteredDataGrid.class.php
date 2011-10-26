<?php
/************************
* This is the Filtered Data Grid class that extends the basic QDataGrid with an additional filter
* row and also generates the FilterInfo array needed to pass into the LoadArrayByArray function.
*
* This is released under the MIT license. See the README.txt file for more details.
*
* @author Ryan Peters rpeters@icomproductions.ca
* @copyright ICOM Productions, Inc. 2006-2008
* @name QFilteredDataGrid
* @package QFilteredDataGrid
* @subpackage QFilteredDataGrid
* @version 4.1.0
*/

//TODO: provide <,>,<=,>= and = comparisons for numbers in filter
//Cleaned up by gibran (reset not a filter type, QQCondition filter type support, misc changes)

class QFilteredDataGrid extends QDataGrid {
	///////////////////////////
	// DataGrid Preferences
	///////////////////////////
	public $objFilterWaitIcon = null;
	protected $objFilterRowStyle = null;
	protected $blnFilterShow = true;
	protected $blnFilterButtonShow = true;
	protected $blnFilterResetButtonShow = true;
	protected $intCurrentColumnId = 1;
	
	protected $btnFilter;
	protected $btnFilterReset;
	
	// Feel free to specify global display preferences/defaults for all QDataGrid controls
	public function __construct($objParentObject, $strControlId = null) {
		try {
			parent::__construct($objParentObject, $strControlId);
		} catch (QCallerException  $objExc) {
			$objExc->IncrementOffset();
			throw $objExc;
		}
		$this->objWaitIcon_Create();
		$this->objFilterRowStyle = new QDataGridRowStyle();
		$this->btnFilter_Create();
		$this->btnFilterReset_Create();
	}
	
	/******
	* Create the row used for datagrid filtering
	* @return string $strToReturn of html table row
	*/
	protected function GetFilterRowHtml() {
		$objFilterStyle = $this->objRowStyle->ApplyOverride($this->objFilterRowStyle);
		$strToReturn = sprintf('  <tr %s>'."\r\n", $objFilterStyle->GetAttributes());
		$intColumnIndex = 0;
		if($this->objColumnArray !== null)
		{
			for ($intIndex = 0; $intIndex < count($this->objColumnArray); $intIndex++)
			{
				$objColumn = $this->objColumnArray[$intIndex];
				
				$colContent = '&nbsp;';
				
				if ($objColumn instanceof QFilteredDataGridColumn && ($objColumn->Filter !== null || $objColumn->FilterByCommand !== null || $objColumn->FilterType != QFilterType::None)) {
					// This Column is Filterable
					$ctlFilter = $this->GetFilterControl($objColumn);
					
					if(null !== $ctlFilter)
						//display the control
						$colContent = $ctlFilter->Render(false);
				}
				
				if($intIndex == count($this->objColumnArray) -1)
				{
					if ($this->FilterResetButtonShow)
						$colContent .= $this->btnFilterReset->Render(false);
					if ($this->FilterResetButtonShow && $this->FilterButtonShow)
						$colContent .= '&nbsp;';
					if ($this->FilterButtonShow)
						$colContent .= $this->btnFilter->Render(false);
					$colContent .= $this->objFilterWaitIcon->Render(false);
				}
				
				$strToReturn .= sprintf('    <th %s>%s</th>'."\r\n",
						$this->objFilterRowStyle->GetAttributes(),
						$colContent);
			}
		}
		$strToReturn .= '  </tr>'."\r\n";
		return $strToReturn;
	}
	
	protected function GetColumnFilterControlId($objColumn) {
		if ($objColumn->FilterColId === null)
			$objColumn->FilterColId = $this->intCurrentColumnId++;
		return 'ctl'.$this->ControlId.'flt'.$objColumn->FilterColId;
	}
	
	protected function GetFilterControl($objColumn)
	{
		$intControlId = $this->GetColumnFilterControlId($objColumn);
		//find/build the control
		if(($ctlFilter = $this->GetChildControl($intControlId)) === null)
			//create the control this first time
			$ctlFilter = $this->CreateFilterControl($intControlId, $objColumn);
		return $ctlFilter;
	}
	
	/******
	* CreateControls used in the filter row and set their fiter values if available. 
	* @param string $intControlId id based on the column that the control is contained
	* @param QFilteredDataGridColumn $objColumn the QFilteredDataGridColumn that contains the filter data. 
	* @return QControl $control the input control used for filtering
	*/
	//this, btnReset_Click and GetControlValue are the functions to override/change if you want to add new types
	
	protected function CreateFilterControl($intControlId, $objColumn)
	{
		//show the current filter in the control
		$value = null;
		if (isset($objColumn->FilterByCommand['value']))
			$value = $objColumn->FilterByCommand['value'];
		if (null !==$objColumn->Filter && null !== $objColumn->Filter->Value)
			$value = $objColumn->Filter->Value;
		if (null !==$objColumn->Filter && $objColumn->FilterType == QFilterType::ListFilter)
			$value = array_search($objColumn->Filter,$objColumn->FilterList);
		
		//create the appropriate kind of control
		$actionName = 'btnFilter_Click';
		$ctlFilter = null;
		switch($objColumn->FilterType)
		{
			default:
			case QFilterType::TextFilter:
				$ctlFilter = $this->filterTextBox_Create($intControlId, $objColumn->Name, $objColumn->FilterBoxSize, $value);
				break;
			case "List":
				$ctlFilter = $this->listBox_Create($intControlId, $objColumn->Name, $objColumn->FilterList, $value);
				break;			
			case QFilterType::ListFilter:
				$ctlFilter = $this->filterListBox_Create($intControlId, $objColumn->Name, $objColumn->FilterList, $value);
				break;
		}
		
		if(null !== $ctlFilter)
		{
			//make sure hitting enter applies the filter
			if ($this->blnUseAjax)
				$ctlFilter->AddAction(new QEnterKeyEvent(), new QAjaxControlAction($this, $actionName, $this->objFilterWaitIcon));
			else
				$ctlFilter->AddAction(new QEnterKeyEvent(), new QServerControlAction($this, $actionName));
			
			$ctlFilter->AddAction(new QEnterKeyEvent(), new QTerminateAction());
		}
		
		return $ctlFilter;
	}
	
	/******
	* Get the control's filter input for filtering
	* @param string $type id based on the column that the control is contained
	* @param obj $control the filter control to get the filter input 
	* @return string $value the input used for filtering
	*/
	//this, btnReset_Click and CreateControl are the functions to override/change if you want to add new types
	protected function GetFilterControlValue($strFilterType, $ctlControl) {
		//depending on the control, the members used to store the value are different
		$strValue = null;
		switch($strFilterType) {
			default:
			case QFilterType::TextFilter:
				$strValue = $ctlControl->Text;
				if($strValue == '')
					$strValue = null;
				break;
			case "List":
			case QFilterType::ListFilter:
				$strValue = $ctlControl->SelectedValue;
				break;
		}
		return $strValue;
	}
	
	protected function filterTextBox_Create($intControlId, $strControlName, $columns, $strValue) {
		$ctlFilterTextBox = new QTextBox($this, $intControlId);
		$ctlFilterTextBox->Name = $strControlName;
		$ctlFilterTextBox->Text = QType::Cast($strValue, QType::String);
		$ctlFilterTextBox->FontSize = $this->RowStyle->FontSize;
		$ctlFilterTextBox->Columns = $columns;
		
		return $ctlFilterTextBox;
	}
	
	protected function listBox_Create($controlId, $controlName, $list, $value)
	{
		$listbox = new QListBox($this, $controlId);
		$listbox->Name = $controlName;
		$listbox->AddItem('- '.QApplication::Translate('Any').' -', null);
		
		//fill it with the supplied name=>value pairs, ensuring any current choices are selected
		foreach ($list as $itemName=>$itemValue)
		{
			$objListItem = new QListItem($itemName, $itemValue);
			if ($value === $itemValue)
				$objListItem->Selected = true;
			$listbox->AddItem($objListItem);
		}
		
		return $listbox;
	}
	
	protected function filterListBox_Create($intControlId, $strControlName, $arrListValues, $strSelectedValue)
	{
		$ctlFilterListbox = new QListBox($this, $intControlId);
		$ctlFilterListbox->Name = $strControlName;
		$ctlFilterListbox->AddItem('-'.QApplication::Translate('Any').'-');
		$ctlFilterListbox->FontSize = $this->RowStyle->FontSize;
		$ctlFilterListbox->Width = 'auto';
		
		//Now fill up the advanced list
		foreach (array_keys($arrListValues) as $strFilterName) {
			$ctlFilterListbox->AddItem($strFilterName,$strFilterName);			
		}
		$ctlFilterListbox->SelectedName = $strSelectedValue;
		return $ctlFilterListbox;
	}	
	
	protected function btnFilterReset_Create() {
		$this->btnFilterReset = new QButton($this);
		$this->btnFilterReset->Text = QApplication::Translate('Reset');;
		
		if ($this->blnUseAjax)
			$this->btnFilterReset->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnFilterReset_Click', $this->objFilterWaitIcon));
		else
			$this->btnFilterReset->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnFilterReset_Click'));
		$this->btnFilterReset->AddAction(new QClickEvent(), new QTerminateAction());
	}
	
	//create the filter button
	protected function btnFilter_Create()
	{
		$this->btnFilter = new QButton($this);
		$this->btnFilter->Name = QApplication::Translate('Filter');
		$this->btnFilter->Text = QApplication::Translate('Filter');
		
		if ($this->blnUseAjax)
			$this->btnFilter->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnFilter_Click', $this->objFilterWaitIcon));
		else
		$this->btnFilter->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnFilter_Click'));
		
		$this->btnFilter->AddAction(new QClickEvent(), new QTerminateAction());
		
		$this->btnFilter->CausesValidation = false;
	}
	
	protected function objWaitIcon_Create()
	{
		$this->objFilterWaitIcon = new QWaitIcon($this);
	}
	
	/******
	* For each column, get its input filter value and set the columns filter with it.
	* @param $strFormId, $strControlId, $strParameter
	*/
	public function btnFilter_Click($strFormId, $strControlId, $strParameter) 
	{
		//set the filter commands
		foreach($this->objColumnArray as $objColumn)
		{
			if ($objColumn instanceof QFilteredDataGridColumn && ($objColumn->FilterByCommand !== null || $objColumn->FilterType != QFilterType::None))
			{
				$ctlFilter = $this->GetChildControl($this->GetColumnFilterControlId($objColumn));				
				if($ctlFilter !== null)
				{
					$strValue = $this->GetFilterControlValue($objColumn->FilterType, $ctlFilter);

					if ($objColumn->FilterByCommand !== null)
					{
						//update the column's filterByCommand with the user-entered value
						$filter = $objColumn->FilterByCommand;
						
						if($strValue !== null && $objColumn->FilterType !== "Reset")
							$filter['value'] = $strValue;
						else if(isset($filter['value']))
							unset($filter['value']);
						
						$objColumn->FilterByCommand = $filter;
					}
					//Handle the other methods differently
					elseif($strValue !== null)
					{
						switch($objColumn->FilterType) {
							case QFilterType::ListFilter:
								$objColumn->FilterActivate($strValue);
								break;
							default:
							case QFilterType::TextFilter;
								$objColumn->FilterActivate();
								$objColumn->FilterSetOperand($strValue);
								break;
						}
					}
					else
						$objColumn->FilterClear();
				}
			}
		}
		//reset to page 1
		if ($this->objPaginator)
			$this->PageNumber = 1;

		$this->DataBind();

		$this->MarkAsModified();
	}
	
	//depreciated
	public function btnReset_Click()
	{
		$this->btnFilterReset_Click(null,null,null);
	}
	
	/******
	* Clear all  filter column control input values.
	* @param $strFormId = null, $strControlId = null, $strParameter = null
	*/
	//this, GetControlValue and CreateControl are the functions to override/change if you want to add new types
	public function btnFilterReset_Click($strFormId, $strControlId, $strParameter) 
	{
		//set the filter commands
		foreach($this->objColumnArray as $objColumn)
		{
			if ($objColumn instanceof QFilteredDataGridColumn)
			{
				if($objColumn->FilterByCommand !== null)
				{
					//legacy mode
					$filter = $this->GetChildControl($this->GetColumnFilterControlId($objColumn));
					if($filter) switch($objColumn->FilterType)
					{
						default:
						case QFilterType::TextFilter:
							$filter->Text = '';
							break;
						case "List":
							$filter->SelectedIndex = 0;
							break;
						case "Reset":
							break;
					}
					$objColumn->FilterClear();					
				}
				elseif($objColumn->FilterType != QFilterType::None)
				{
					//normal mode
					$ctlFilter = $this->GetChildControl($this->GetColumnFilterControlId($objColumn));
					if ($ctlFilter !== null)
					{
						switch($objColumn->FilterType)
						{
							default:
							case QFilterType::TextFilter:
								$ctlFilter->Text = '';
								break;
							case 'List':
							case QFilterType::ListFilter:
								$ctlFilter->SelectedIndex = 0;
								break;
						}
						$objColumn->FilterClear();
					}
				}
			}
		}
		
		//reset to page 1
		if ($this->objPaginator)
			$this->PageNumber = 1;
		$this->MarkAsModified();
	}
	
	protected function GetHeaderRowHtml() {
		$strToReturn = parent::GetHeaderRowHtml();		
		// Filter Row (if applicable)
		if ($this->blnFilterShow)
			$strToReturn .= $this->GetFilterRowHtml();
		
		return $strToReturn;
	}
	
	/******
	* Set Filter values as passed through session upon preRender
	* @param array $filters array of filters indexed by column name
	* contain either a string or a filter object
	*/
	public function SetFilters($filters)
	{
		foreach($this->objColumnArray as $col)
		{	
			if(isset($filters[$col->Name]))
			{
				if($col instanceof QFilteredDataGridColumn && (isset($col->FilterByCommand['operator'])))
				{
					//if filterbycommand is used
					$filterCommand = $col->FilterByCommand;
					$filterCommand['value'] = $filters[$col->Name];
					$col->FilterByCommand = $filterCommand;
				}
				//AddListItem with filters dont enter this check until filter button clicked
				elseif ($col instanceof QFilteredDataGridColumn && $col->Filter !== null) {
					if($col->Filter instanceof QQConditionComparison)
					{
						$col->Filter = $filters[$col->Name];
					}
					elseif($col->Filter instanceof Filter)
					{
						if($col->Filter->Node === null){
							$col->Filter = $filters[$col->Name];
						}else{
							$col->Filter->Value = $filters[$col->Name];
						}
					}
				}
				elseif ($col instanceof QFilteredDataGridColumn && $col->FilterType == "Advanced List"){
					$col->Filter = $filters[$col->Name];
				}						
			}	
		}
	}
	/******
	* Get Filter values from each column to be passed to session
	* @return array $filters array of filters indexed by column name
	*/
	public function GetFilters()
	{
		$filters = array();
		foreach($this->objColumnArray as $col)
		{	
			
			if($col instanceof QFilteredDataGridColumn && (isset($col->FilterByCommand['value'])))
			{
				$filterCommand = $col->FilterByCommand;
				$filters[$col->Name] = $filterCommand['value'];		
			}
			elseif ($col instanceof QFilteredDataGridColumn && $col->Filter !== null) 
			{
				if($col->Filter instanceof QQConditionComparison)
				{
					$filters[$col->Name] = $col->Filter;
				}
				elseif($col->Filter instanceof Filter)
				{
					if($col->Filter->Node === null ){
						$filters[$col->Name] = $col->Filter;
					}elseif($col->Filter->Value !== null){
						$filters[$col->Name] = $col->Filter->Value;	
					}	
				}
				else
					throw new exception(QApplication::Translate("Unknown Filter type"));
			}
		}
		return $filters;
	}
	
	/////////////////////////
	// Public Properties: GET
	/////////////////////////
	public function __get($strName) {
		switch ($strName) {
			// APPEARANCE
			case "FilterRowStyle": return $this->objFilterRowStyle;
			// LAYOUT
			case "FilterShow": 
			case "ShowFilter": 
				return $this->blnFilterShow;
			case "ShowFilterButton": 
			case "FilterButtonShow": 
				return $this->blnFilterButtonShow;
			case 'FilterResetButtonShow': return $this->blnFilterResetButtonShow;
			// MISC
			case "FilterInfo":
				$filterArray = array();
				foreach($this->objColumnArray as $col)
				{
					if(isset($col->FilterByCommand['value']))
					{
						$filterCommand = $col->FilterByCommand;
						$filterCommand['clause_operator'] = 'AND';
						//apply the pre and postfix
						$filterCommand['value'] = $filterCommand['prefix'] . $filterCommand['value'] . $filterCommand['postfix'];
						$filterArray[] = $filterCommand;
					}
				}
				return $filterArray;
			case 'FilterConditions':
			case "Conditions":
				//Calculate the conditions to apply to the entire grid based on the column's filters
				$dtgConditions = QQ::All();
				foreach($this->objColumnArray as $objColumn)
				{	
					if($objColumn instanceof QFilteredDataGridColumn)
					{
						if(isset($objColumn->FilterByCommand['value']))
						{
							//old style filter
							$filterCommand = $objColumn->FilterByCommand;
							//apply the pre and postfix
							$filterCommand['value'] = $filterCommand['prefix'] . $filterCommand['value'] . $filterCommand['postfix'];
							
							$colCondition = QQ::_($filterCommand['node'], $filterCommand['operator'], $filterCommand['value']);						
						}
						elseif ($objColumn->FilterType != QFilterType::None) {
							//new condition based filter
							
							//if we are using node, operator, value and value returned by control is null we
							//do not want to apply the filter
							if($objColumn->Filter instanceof Filter)
							{
								if($objColumn->Filter->Node !== null && $objColumn->Filter->Operator !==null && $objColumn->Filter->Value === null)
									$colCondition = null;
								else
									//A filter was set. Use it
									$colCondition = $objColumn->Filter->Condition;
							}
							else
							{
								$colCondition = null;
								if ($objColumn->Filter !== null)
									$colCondition = $objColumn->Filter;
							}
						}
						else {
							//neither form of filter appears to have been used
							$colCondition = null;
						}
					}
					/*CustomFilter allows us to specify a custom QQuery that applies in addition to any 
					user-specified filters. EG: If the user enters a Cost to filter on, also filter on 
					object actually being sold*/
					if(null !== $colCondition && null !== $objColumn->FilterCustom)
						$colCondition = QQ::AndCondition($colCondition, $objColumn->FilterCustom);
					
					//now after all the above checks if the column has a condition to be specified
					//we add it to overall conditions. but if the column conditions are null we leave
					//overall conditions as is
					if($colCondition !== null) {
						//if there are no overall conditions yet change them to reflect the column condition
						if($dtgConditions == QQ::All())
							$dtgConditions = $colCondition;
						else
							//combine the overall conditions with the column conditions
							$dtgConditions = QQ::AndCondition($dtgConditions, $colCondition);
					}
				}

				return $dtgConditions;
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
			// APPEARANCE
			case "FilterRowStyle":
			try {
				$this->objFilterRowStyle = QType::Cast($mixValue, "QDataGridRowStyle");
				break;
			} catch (QInvalidCastException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
			// LAYOUT
			case 'FilterShow':
			case "ShowFilter":
			try {
				$this->blnFilterShow = QType::Cast($mixValue, QType::Boolean);
				break;
			} catch (QInvalidCastException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
			case 'FilterButtonShow':
			case "ShowFilterButton":
			try {
				$this->blnFilterButtonShow = QType::Cast($mixValue, QType::Boolean);
				break;
			} catch (QInvalidCastException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
			case 'FilterResetButtonShow':
			try {
				$this->blnFilterResetButtonShow = QType::Cast($mixValue, QType::Boolean);
				break;
			} catch (QInvalidCastException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
			// BEHAVIOR
			case "Paginator":
				//do whatever needs done
				try {
					$blnToReturn = parent::__set($strName, $mixValue);
				} catch (QInvalidCastException $objExc) {
					$objExc->IncrementOffset();
					throw $objExc;
				}
				
				//Now make sure it knows about our spinner
//				$this->objPaginator->WaitIcon = $this->objFilterWaitIcon;
				return $blnToReturn;
				break;
			
			case "PaginatorAlternate":
				//do whatever needs done
				try {
					$blnToReturn = parent::__set($strName, $mixValue);
				} catch (QInvalidCastException $objExc) {
					$objExc->IncrementOffset();
					throw $objExc;
				}
				
				//Now make sure it knows about our spinner
				$this->objPaginatorAlternate->WaitIcon = $this->objFilterWaitIcon;
				return $blnToReturn;
				break;
			
			case "UseAjax":
				try {
					$blnToReturn = parent::__set($strName, $mixValue);
				} catch (QInvalidCastException $objExc) {
					$objExc->IncrementOffset();
					throw $objExc;
				}

				// Because we are switching to/from Ajax, we need to reset the events
				$actionName = 'btnFilter_Click';
				foreach($this->objColumnArray as $objColumn) 
				{
					if ($objColumn instanceof QFilteredDataGridColumn && ($objColumn->FilterByCommand !== null || $objColumn->FilterType != QFilterType::None))
					{
						$ctlFilter = $this->GetChildControl($this->GetColumnFilterControlId($objColumn));
						if ($ctlFilter !== null) 
						{
							$ctlFilter->RemoveAllActions('onkeydown');
							if ($this->blnUseAjax)
								$ctlFilter->AddAction(new QEnterKeyEvent(), new QAjaxControlAction($this, $actionName, $this->objFilterWaitIcon));
							else
								$ctlFilter->AddAction(new QEnterKeyEvent(), new QServerControlAction($this, $actionName));
							
							$ctlFilter->AddAction(new QEnterKeyEvent(), new QTerminateAction());
						}
					}
				}
				
				
				$this->btnFilter->RemoveAllActions('onclick');
				if ($this->blnUseAjax)
					$this->btnFilter->AddAction(new QClickEvent(), new QAjaxControlAction($this, $actionName, $this->objFilterWaitIcon));
				else
					$this->btnFilter->AddAction(new QClickEvent(), new QServerControlAction($this, $actionName));
				$this->btnFilter->AddAction(new QClickEvent(), new QTerminateAction());
				
				$this->btnFilterReset->RemoveAllActions('onclick');
				if ($this->blnUseAjax)
					$this->btnFilterReset->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnFilterReset_Click', $this->objFilterWaitIcon));
				else
					$this->btnFilterReset->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnFilterReset_Click'));
				$this->btnFilterReset->AddAction(new QClickEvent(), new QTerminateAction());
				
				return $blnToReturn;
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
}
?>