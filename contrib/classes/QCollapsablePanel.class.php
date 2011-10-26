<?php

		class QCollapsablePanel extends QPanel
        {
		protected $pnlHeader;
		protected $pnlBody;
		protected $btnToggle;
		
		protected $blnExpanded;
        /**
        * NOTE: When set to false this uses client side javascript to hide the body NOT a server action.
        * TODO: support server actions (ie, no javascript). Also, use native qcodo javascript lib for the
        * client side action.
        *@var boolean - use ajax call or client javascript
        */
		protected $blnUseAjax = true;
        /**
        *@var string - the base directory in which to find images 
        */
        protected $strImagesPath;
        /**
        *@var string - the filename for the toggle button image with body expanded
        */
        protected $strExpandedImageUri;
        /**
        *@var string - the filename for the toggle button image with body collapsed
        */
		protected $strCollapsedImageUri;
        
		public function __construct($objParentObject,
                                                          $strControlId = null,
                                                          $blnExpanded = false,
                                                          $blnUseAjax = true,
                                                          $strExpandedImageUri = '/treenav_expanded.png',
                                                          $strCollapsedImageUri = '/treenav_collapsed.png',
                                                          $strImagesPath= null
                                                          )
        {
            
            try {
				parent::__construct($objParentObject, $strControlId);
			} catch (QCallerException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
			$this->blnExpanded = $blnExpanded;
			$this->blnUseAjax = $blnUseAjax;
            if(!$strImagesPath)
                $this->strImagesPath = __QUASI_CONTRIB_IMAGES__;
            else
                $this->strImagesPath = $strImagesPath;
            $this->strExpandedImageUri = $this->strImagesPath . $strExpandedImageUri;
            $this->strCollapsedImageUri = $this->strImagesPath . $strCollapsedImageUri;
			
			$this->AutoRenderChildren = true;
			
			$this->pnlHeader = new QPanel($this);
            $this->pnlHeader->CssClass = 'CollapsableHeader';         
			$this->pnlBody = new QPanel($this);
            $this->pnlBody->CssClass = 'CollapsableBody';
            $this->btnToggle = new QImageButton($this->pnlHeader);
            
            $this->pnlHeader->AutoRenderChildren = true;
            $this->pnlBody->AutoRenderChildren = true;
			
			$this->setButtonAction();
            
            //fake a click to set the initial state - interesting breakage happens if you use this
//            $this->btnToggle_Click(null,null,null);         
		}
		
		protected function setButtonAction()
        {
			$this->btnToggle->RemoveAllActions(QClickEvent::EventName);
			if ($this->blnUseAjax)
            {
				$this->btnToggle->AddAction(new QClickEvent(), new QAjaxControlAction($this, "btnToggle_Click"));
                ///fixme - why doesn't this work??            
//                $this->pnlHeader->AddAction(new QClickEvent(), new QAjaxControlAction($this, "btnToggle_Click"));
			}
            else
            {
				$onclick = "el=document.getElementById('".$this->pnlBody->ControlId."'); imgEl=document.getElementById('".$this->btnToggle->ControlId."'); if (el.style.display=='block') {el.style.display='none'; imgEl.src = '".$this->strCollapsedImageUri."';} else {el.style.display='block'; imgEl.src = '".$this->strExpandedImageUri."';}";
				$this->btnToggle->AddAction(new QClickEvent(), new QJavaScriptAction($onclick));
			}
		}

		public function btnToggle_Click($strFormId, $strControlId, $strParameter)
        {
            if ($this->blnExpanded) 
                $this->CollapseBody();
            else 
                $this->ExpandBody();
		}
				
		public function ExpandBody()
        {
			if ($this->blnUseAjax) 
				$this->pnlBody->Visible = true;
			else
				$this->pnlBody->DisplayStyle = QDisplayStyle::Block;
			
			if ($this->btnToggle instanceof QImageButton) 
				$this->btnToggle->ImageUrl = $this->strExpandedImageUri;
			
			$this->blnExpanded = true;
			$this->MarkAsModified();
		}
		
		public function CollapseBody()
        {
			if ($this->blnUseAjax)
				$this->pnlBody->Visible = false;
			else
				$this->pnlBody->DisplayStyle = QDisplayStyle::None;
			
			$this->btnToggle->ImageUrl = $this->strCollapsedImageUri;
			$this->blnExpanded = false;
			$this->MarkAsModified();
		}
		
		/////////////////////////
		// Public Properties: GET
		/////////////////////////
		public function __get($strName)
        {
			switch ($strName)
            {
				case "Header":
                    return $this->pnlHeader;
				case "Body":
                    return $this->pnlBody;
				case "Button":
                    return $this->btnToggle;
				case "Expanded":
                    return $this->blnExpanded;
                case "UseAjax":
                    return $this->blnUseAjax;
                case "ImagesPath":
                    return $this->strImagesPath;
                case "ExpandedImageUri":
                    return $this->strExpandedImageUri;
                case "CollapsedImageUri":
                    return $this->strCollapsedImageUri;

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
		public function __set($strName, $mixValue)
        {
			$this->blnModified = true;

			switch ($strName)
            {
                case "Expanded":
                    try {
                        $this->blnExpanded = QType::Cast($mixValue, QType::Boolean);
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                    if ($this->blnExpanded)
                        $this->ExpandBody();
                    else
                        $this->CollapseBody();
                    break;
				case "UseAjax":
					try {
						$this->blnUseAjax = QType::Cast($mixValue, QType::Boolean);
					} catch (QInvalidCastException $objExc) {
						$objExc->IncrementOffset();
						throw $objExc;
					}
                    $this->setButtonAction();
                    break;
                
                case "ExpandedImageUri":
                    try {
                       $tmp = QType::Cast($mixValue, QType::String);
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                    if( '/' != $tmp[0] )
                        $tmp = '/' . $tmp;
                    $this->strExpandedImageUri = $this->strImagesPath . $tmp;
                    break;

                case "CollapsedImageUri":
                    try {
                       $tmp = QType::Cast($mixValue, QType::String);
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                    if( '/' != $tmp[0] )
                        $tmp = '/' . $tmp;
                    $this->strCollapsedImageUri = $this->strImagesPath . $tmp; 
                    break;

                case "ImagesPath":
                    try {
                       return ($this->strImagesPath = QType::Cast($mixValue, QType::String));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }

				default:
					try {
						parent::__set($strName, $mixValue);
					} catch (QCallerException $objExc) {
						$objExc->IncrementOffset();
						throw $objExc;
					}
			}
		}
	}
?>