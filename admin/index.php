<?php
    // Include to load  Quasi and Qcodo
    require('../core/Quasi.class.php');

	// Security check for ALLOW_REMOTE_ADMIN
	// To allow access REGARDLESS of ALLOW_REMOTE_ADMIN, simply remove the line below
	QApplication::CheckRemoteAdmin();



	// Let's "magically" determine the list of genereated Class Panel Drafts by
	// just traversing through this directory, looking for "*ListPanel.class.php" and "*EditPanel.class.php"

	// Obviously, if you are wanting to make your own dashbaord, you should change this and use more
	// hard-coded means to determine which classes' paneldrafts you want to include/use in your dashboard.
	$objDirectory = opendir(dirname(__FILE__));
	$strClassNameArray = array();
	while ($strFile = readdir($objDirectory))
    {
        if(false !== strpos($strFile,'~') )
            continue;
		if ($intPosition = strpos($strFile, 'ListPanel.class.php'))
        {
			$strClassName = substr($strFile, 0, $intPosition);
			$strClassNameArray[$strClassName] = $strClassName . 'ListPanel';
			require($strClassName . 'ListPanel.class.php');
            if(file_exists('./' . $strClassName . 'EditPanel.class.php') )         
			require($strClassName . 'EditPanel.class.php');
		}
	}

    natsort($strClassNameArray);

	class Dashboard extends QForm {
		protected $pnlClassNames;

		protected $pnlTitle;
		protected $pnlList;
		protected $pnlEdit;

		protected function Form_Create() {
			$this->pnlTitle = new QPanel($this);
			$this->pnlTitle->Text = 'AJAX Dashboard';

			$this->pnlList = new QPanel($this, 'pnlList');
			$this->pnlList->AutoRenderChildren = true;

			$this->pnlEdit = new QPanel($this, 'pnlEdit');
			$this->pnlEdit->AutoRenderChildren = true;
			$this->pnlEdit->Visible = false;

			$this->pnlClassNames = new QPanel($this, "classNames");
            $this->pnlClassNames->AutoRenderChildren = true;
            $this->pnlClassNames->Visible = true;

			// Use the strClassNameArray as magically determined above to aggregate the listbox of classes
			// Obviously, this should be modified if you want to make a custom dashboard
			global $strClassNameArray;
			foreach ($strClassNameArray as $strClassName)
            {
                $strMenuLabel =  substr( $strClassName, 0 , strpos( $strClassName, "ListPanel" ) );
                $pnlClassName = new QPanel($this->pnlClassNames);
                $pnlClassName->Text = $strMenuLabel;
                $pnlClassName->CssClass = 'className';
                $pnlClassName->ActionParameter = $strClassName;
                $pnlClassName->AddAction(New QClickEvent(), new QAjaxAction('pnlClassNames_Change'));

            }         
			
			$this->objDefaultWaitIcon = new QWaitIcon($this);
		}

		/**
		 * This Form_Validate event handler allows you to specify any custom Form Validation rules.
		 * It will also Blink() on all invalid controls, as well as Focus() on the top-most invalid control.
		 */
		protected function Form_Validate() {
			// By default, we report that Custom Validations passed
			$blnToReturn = true;

			// Custom Validation Rules
			// TODO: Be sure to set $blnToReturn to false if any custom validation fails!

			$blnFocused = false;
			foreach ($this->GetErrorControls() as $objControl) {
				// Set Focus to the top-most invalid control
				if (!$blnFocused) {
					$objControl->Focus();
					$blnFocused = true;
				}

				// Blink on ALL invalid controls
				$objControl->Blink();
			}

			return $blnToReturn;
		}

		protected function pnlClassNames_Change($strFormId, $strControlId, $strParameter)
        {
			// Get rid of all child controls for list and edit panels
			$this->pnlList->RemoveChildControls(true);
			$this->pnlEdit->RemoveChildControls(true);
            $this->pnlEdit->Visible = false;
            $this->pnlList->Visible = true;

			if ($strClassName = $strParameter)
            {
				// We've selected a Class Name
				$objNewPanel = new $strClassName($this->pnlList, 'SetEditPane', 'CloseEditPane');
				$this->pnlTitle->Text = $strMenuLabel =  substr( $strParameter, 0 , strpos( $strParameter, "ListPanel" ) );
                $this->pnlTitle->Text .= ' List';            
			} else {
				$this->pnlTitle->Text = 'AJAX Dashboard';
			}
		}

		public function SetListPane(QPanel $objPanel)
        {
            $this->pnlEdit->RemoveChildControls(true);
            $this->pnlEdit->Visible = false;
            
			$this->pnlList->RemoveChildControls(true);
			$objPanel->SetParentControl($this->pnlList);
            $this->pnlList->Visible = true;         
		}

		public function CloseEditPane($blnUpdatesMade) {
			// Close the Edit Pane
			$this->pnlEdit->RemoveChildControls(true);
			$this->pnlEdit->Visible = false;

			// If updates were made, let's "brute force" the updates to the screen by just refreshing the list pane altogether
			if ($blnUpdatesMade)
				$this->pnlList->Refresh();
            $this->pnlList->Visible = true;
		}

		public function SetEditPane(QPanel $objPanel = null)
        {
            $this->pnlList->Visible = false;
			$this->pnlEdit->RemoveChildControls(true);
			if ($objPanel) {
				$objPanel->SetParentControl($this->pnlEdit);
				$this->pnlEdit->Visible = true;
			} else {
				$this->pnlEdit->Visible = false;
			}
		}
	}

	Dashboard::Run('Dashboard');
?>