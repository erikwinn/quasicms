<?php
	require(__DATAGEN_META_CONTROLS__ . '/ContentItemMetaControlGen.class.php');

	/**
	 * This is a MetaControl customizable subclass, providing a QForm or QPanel access to event handlers
	 * and QControls to perform the Create, Edit, and Delete functionality of the
	 * ContentItem class.  This code-generated class extends from
	 * the generated MetaControl class, which contains all the basic elements to help a QPanel or QForm
	 * display an HTML form that can manipulate a single ContentItem object.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QForm or QPanel which instantiates a ContentItemMetaControl
	 * class.
	 *
	 * This file is intended to be modified.  Subsequent code regenerations will NOT modify
	 * or overwrite this file.
	 * 
	 * @package Quasi
	 * @subpackage MetaControls
	 */
	class ContentItemMetaControl extends ContentItemMetaControlGen
    {
        // Controls that allow the viewing of ContentItem's individual data fields as QPanels 
        protected $pnlName;
        protected $pnlClass;
        protected $pnlTitle;
        protected $pnlDescription;
        protected $pnlText;
        protected $pnlCreatorId;
        protected $pnlCreationDate;
        protected $pnlLastModification;
        
        protected $pnlPublicPermissionsId;
        protected $pnlUserPermissionsId;
        protected $pnlGroupPermissionsId;
        protected $pnlTypeId;
        protected $pnlStatusId;
        
        /**
         * Create and setup QPanel pnlTitle
         * @param string $strControlId optional ControlId to use
         * @return QPanel
         */
        public function pnlTitle_Create($strControlId = null)
        {
            if($strControlId)
                $this->pnlTitle = new QPanel($this->objParentObject, $strControlId . 'Title');
            else
                $this->pnlTitle = new QPanel($this->objParentObject);
            
            $this->pnlTitle->Name = QApplication::Translate('Title');
            $this->pnlTitle->Text = $this->objContentItem->Title;
            return $this->pnlTitle;
        }

        /**
         * Create and setup QPanel pnlDescription
         * @param string $strControlId optional ControlId to use
         * @return QPanel
         */
        public function pnlDescription_Create($strControlId = null)
        {
            if($strControlId)
                $this->pnlDescription = new QPanel($this->objParentObject, $strControlId . 'Description');
            else
                $this->pnlDescription = new QPanel($this->objParentObject);
                            
            $this->pnlDescription->Name = QApplication::Translate('Description');
            $this->pnlDescription->Text = $this->objContentItem->Description;
            return $this->pnlDescription;
        }
        /**
         * Create and setup QPanel pnlText
         * @param string $strControlId optional ControlId to use
         * @return QPanel
         */
        public function pnlText_Create($strControlId = null)
        {
            if($strControlId)
                $this->pnlText = new QPanel($this->objParentObject, $strControlId . 'Text');
            else
                $this->pnlText = new QPanel($this->objParentObject );
            
            $this->pnlText->Name = QApplication::Translate('Text');
            $this->pnlText->Text = $this->objContentItem->Text;
            return $this->pnlText;
        }

        /**
         * Create and setup QPanel pnlCreatorId
         * @param string $strControlId optional ControlId to use
         * @return QPanel
         */
        public function pnlCreatorId_Create($strControlId = null)
        {
            if($strControlId)
                $this->pnlCreatorId = new QPanel($this->objParentObject, $strControlId . 'Creator');
            else
                $this->pnlCreatorId = new QPanel($this->objParentObject);
            
            $this->pnlCreatorId->Name = QApplication::Translate('Creator');
            $this->pnlCreatorId->Text = ($this->objContentItem->Creator) ? $this->objContentItem->Creator->__toString() : null;
            $this->pnlCreatorId->Required = true;
            return $this->pnlCreatorId;
        }

        /**
         * Create and setup QPanel pnlCreationDate
         * @param string $strControlId optional ControlId to use
         * @return QPanel
         */
        public function pnlCreationDate_Create($strControlId = null, $blnShowHour = false, $blnShowMinutes = false, $blnShowSeconds = false)
        {
            if($strControlId)
                $this->pnlCreationDate = new QPanel($this->objParentObject, $strControlId . 'CreationDate');
            else
                $this->pnlCreationDate = new QPanel($this->objParentObject);
            
            $this->pnlCreationDate->Name = QApplication::Translate('Creation Date');
            if ($this->blnEditMode)
            {
                $dttCreationDate = new DateTime($this->objContentItem->CreationDate);
                $strFormat = 'M j, Y';
                if($blnShowHour)
                    $strFormat .= ' H';
                if($blnShowMinutes)
                    $strFormat .= ' i';
                if($blnShowSeconds)
                    $strFormat .= ' s';
                    
                $this->pnlCreationDate->Text = $dttCreationDate->format($strFormat);
            }
            else
                $this->pnlCreationDate->Text = 'N/A';
            return $this->pnlCreationDate;
        }

        /**
         * Create and setup QPanel pnlLastModification
         * @param string $strControlId optional ControlId to use
         * @return QPanel
         */
        public function pnlLastModification_Create($strControlId = null, $blnShowHour = false, $blnShowMinutes = false, $blnShowSeconds = false)
        {
            if($strControlId)
                $this->pnlLastModification = new QPanel($this->objParentObject, $strControlId . 'LastModification');
            else
                $this->pnlLastModification = new QPanel($this->objParentObject);
            
            $this->pnlLastModification->Name = QApplication::Translate('Last Modification');
            if ($this->blnEditMode)
            {
                $dttCreationDate = new DateTime($this->objContentItem->LastModification);
                $strFormat = 'M j, Y';
                if($blnShowHour)
                    $strFormat .= ' H';
                if($blnShowMinutes)
                    $strFormat .= ' i';
                if($blnShowSeconds)
                    $strFormat .= ' s';
                    
                $this->pnlLastModification->Text = $dttCreationDate->format($strFormat);
            }
            else
                $this->pnlLastModification->Text = 'N/A';
            return $this->pnlLastModification;
        }
	}
?>