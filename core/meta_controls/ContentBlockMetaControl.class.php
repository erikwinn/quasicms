<?php
	require(__DATAGEN_META_CONTROLS__ . '/ContentBlockMetaControlGen.class.php');

	/**
	 * This is a MetaControl customizable subclass, providing a QForm or QPanel access to event handlers
	 * and QControls to perform the Create, Edit, and Delete functionality of the
	 * ContentBlock class.  This code-generated class extends from
	 * the generated MetaControl class, which contains all the basic elements to help a QPanel or QForm
	 * display an HTML form that can manipulate a single ContentBlock object.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QForm or QPanel which instantiates a ContentBlockMetaControl
	 * class.
	 *
	 * This file is intended to be modified.  Subsequent code regenerations will NOT modify
	 * or overwrite this file.
	 * 
	 * @package Quasi
	 * @subpackage MetaControls
	 */
	class ContentBlockMetaControl extends ContentBlockMetaControlGen {
        // Controls that allow the viewing of ContentBlock's individual data fields as QPanels
        protected $pnlTitle;
        protected $pnlDescription;
        
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
            $this->pnlTitle->Text = $this->objContentBlock->Title;
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
            $this->pnlDescription->Text = $this->objContentBlock->Description;
            return $this->pnlDescription;
        }        
	}
?>