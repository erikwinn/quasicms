<?php
/**
    * This class provides an HTML input field for file uploading.
    * 
    *    
    *@author Erik Winn <erikwinnmail@yahoo.com>
    *
    *
    * $Id: QFileInput.class.php 93 2008-08-28 21:23:02Z erikwinn $
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

	class QFileInput extends QControl
    {
        /**
        *@var string - the full path to the temporary uploaded file.
        */
        private $strTempUri = null;
        /**
        *@var string - the original name of the file on the client machine.
        */
		private $strFileName = null;
        /**
        *@var string - the file extension (if any).
        */
        private $strExtension = null;
        /**
        *@var string - the mime type uploaded file (NOTE: according to the browser).
        */
        private $strMimeType = null;
        /**
        *@var integer - the size of the uploaded file in bytes.
        */
        private $intSize = null;
        /**
        *@var string - the error code associated with this file upload.
        */
        private $intErrorCode = null;

        private $aryErrorMessages = array(
            UPLOAD_ERR_OK => 'Upload Successful',
            UPLOAD_ERR_INI_SIZE => 'File exceeds the upload_max_filesize php directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds the MAX_FILE_SIZE HTML directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'The temporary upload directory does not exist',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
        );
        /**
        *@var array - attributes for the form to indicate upload
        */		
		protected $strFormAttributes = array('enctype'=>'multipart/form-data');
        /**
        *@var integer - the maximum size of the uploaded file in bytes.
        */
        private $intMaxFileSize = 1000000;
 		
		public function ParsePostData()
        {
			if ( array_key_exists($this->strControlId, $_FILES) 
                &&  isset($_FILES[$this->strControlId]['tmp_name']) )
            {
                $this->strTempUri = QType::Cast($_FILES[$this->strControlId]['tmp_name'], QType::String);
				$this->strFileName =  QType::Cast($_FILES[$this->strControlId]['name'], QType::String);
				$this->strMimeType = QType::Cast($_FILES[$this->strControlId]['type'], QType::String);
                $this->intSize = QType::Cast($_FILES[$this->strControlId]['size'], QType::Integer);
                $this->intErrorCode = QType::Cast($_FILES[$this->strControlId]['error'], QType::Integer);
                $pos = strrpos($this->FileName,'.');
                if( false !== $pos )
                    $this->strExtension = substr($this->FileName, $pos +1); 
			}
		}

		public function GetJavaScriptAction() {
			return "onchange";
		}

		protected function GetControlHtml()
        {
            $strToReturn = '';
            $this->strTempUri = null;
			$this->strFileName = null;
            $this->strExtension = null;
            $this->strMimeType = null;
            $this->intSize = null;
            $this->intErrorCode = null;
            
			$strStyleAttributes = $this->GetStyleAttributes();
            $strStyle = '';         
			if (! empty($strStyleAttributes) )
				$strStyle = sprintf('style="%s"', $strStyleAttributes);
                
            $strToReturn = sprintf('<input type="hidden" name="MAX_FILE_SIZE" value="%d" />',
                                                    $this->intMaxFileSize);
			$strToReturn .= sprintf('<input type="file" name="%s" id="%s" %s%s />',
                                                    $this->strControlId,
                                                    $this->strControlId,
                                                    $this->GetAttributes(),
                                                    $strStyle);

			return $strToReturn;
		}

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
            if(strlen( $this->strName ))
                $this->strValidationError = $this->strName . ' ' . QApplication::Translate('is required');
            else
                $this->strValidationError = "Filename is required";

            return false;
		}

		public function __get($strName)
        {
			switch ($strName)
            {
                case "TempUri":
                    return $this->strTempUri;
				case "FileName":
                    return $this->strFileName;
				case "MimeType":
                    return $this->strMimeType;
                case "Size":
                    return $this->intSize;
                case "Extension":
                    return $this->strExtension;
                case "ErrorCode":
                    return $this->intErrorCode;
                case "ErrorMessage":
                    if($this->intErrorCode !== null)
                        return $this->aryErrorMessages[$this->intErrorCode];
                    return "Internal Error - Error code unset.";
                case "MaxFileSize":
                    return $this->intMaxFileSize;

				default:
					try {
						return parent::__get($strName);
					} catch (QCallerException $objExc) {
						$objExc->IncrementOffset();
						throw $objExc;
					}
			}
		}

		public function __set($strName, $mixValue)
        {
			$this->blnModified = true;

			switch ($strName)
            {
                case 'MaxFileSize':
                    try {
                        return ($this->intMaxFileSize = QType::Cast($mixValue, QType::Integer ));
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
					break;
			}
		}
	}
?>