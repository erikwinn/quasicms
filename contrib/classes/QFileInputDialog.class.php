<?php

//TODO: Autoload me ..
require(__QUASI_CONTRIB_CLASSES__ . '/QFileInput.class.php');

/**
    * This class provides a dialog pop-up for uploading files.
    * 
    *    
    *@author Erik Winn <erikwinnmail@yahoo.com>
    *
    *
    * $Id: QFileInputDialog.class.php 1 2008-07-29 06:33:41Z erikwinn $
    *@version 0.1
    *
    *@copyright (C) 2008 by Erik Winn
    *@license GPL v.2

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111 USA

    *
    * @package QuasiContrib
    * @subpackage Classes
     */
	class QFileInputDialog extends QDialogBox
    {
		public $ctlFileInput;
		public $lblErrorMessage;
		public $btnUpload;
		public $btnCancel;
		public $objSpinner;
        
        protected $objParentObject;

        protected $strFileUploadCallback;

		public function __construct($objParentObject, $strFileUploadCallback, $strControlId = null)
        {
            $this->objParentObject = $objParentObject;
            try {
                parent::__construct($this->objParentObject, $strControlId);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
			
            $this->strFileUploadCallback = $strFileUploadCallback;

			
			$this->strTemplate = __QUASI_CONTRIB_TEMPLATES__ . '/QFileInputDialog.tpl.php';

            $this->blnDisplay = false;
			$this->blnMatteClickable = false;

			$this->lblErrorMessage = new QLabel($this);
			$this->lblErrorMessage->HtmlEntities = false;

			$this->ctlFileInput = new QFileInput($this);

            $this->btnUpload = new QButton($this);
            $this->btnUpload->Text = QApplication::Translate('Upload');
            $this->btnUpload->CausesValidation = QCausesValidation::SiblingsOnly;
			$this->btnCancel = new QButton($this);
            $this->btnCancel->Text = QApplication::Translate('Cancel');
                     
			$this->objSpinner = new QWaitIcon($this);

			// Events on the Dialog Box Controls
			$this->ctlFileInput->AddAction(new QEnterKeyEvent(), new QTerminateAction());

			$this->btnUpload->AddAction(new QClickEvent(), new QToggleEnableAction($this->btnUpload));
			$this->btnUpload->AddAction(new QClickEvent(), new QToggleEnableAction($this->btnCancel));
			$this->btnUpload->AddAction(new QClickEvent(), new QToggleDisplayAction($this->objSpinner));
			$this->btnUpload->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnUpload_Click'));

			$this->btnCancel->AddAction(new QClickEvent(), new QHideDialogBox($this));
		}
        
/*  fixme - figure out how to call this ..       
        public function Validate()
        {
            $this->strValidationError = "";
            if ($this->ErrorCode != 0)
            {
                $this->strValidationError = $this->ErrorMessage;
                return false;
            }
            if (! $this->blnRequired )
                return true;
            if (strlen($this->strFileName) > 0)
                return true;
            if(strlen( $this->Name ))
                $this->strValidationError = _t($this->strName) . ' ' . _t('is required');
            else
                $this->strValidationError = "Filename is required";
            
            return false;
        }*/
        
		
        public function btnUpload_Click($strFormId, $strControlId, $strParameter)
        {
			$this->btnUpload->Enabled = true;
			$this->btnCancel->Enabled = true;
			$this->objSpinner->Display = false;

			$strFileControlCallback = $this->strFileUploadCallback;
			$this->objParentControl->$strFileControlCallback($strFormId, $strControlId, $strParameter);
		}
		
		public function ShowErrorMessage($strErrorMessage=null)
        {
            if( null !== $strErrorMessage)
                $this->lblErrorMessage->Text = $strErrorMessage;
            else
                $this->lblErrorMessage->Text = $this->ErrorMessage;
			$this->ctlFileInput->Focus();
			$this->Blink();
		}
        
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'TempUri':
                    return $this->ctlFileInput->TempUri;
                case 'FileName':
                    return $this->ctlFileInput->FileName;
                case 'Extension':
                    return $this->ctlFileInput->Extension;
                case 'Size':
                    return $this->ctlFileInput->Size;
                case 'MimeType':
                    return $this->ctlFileInput->MimeType;
                case 'ErrorCode':
                    return $this->ctlFileInput->ErrorCode;
                case 'ErrorMessage':
                    return $this->ctlFileInput->ErrorMessage;
                default:
                    try {
                        return parent::__get($strName);
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }
	}
?>