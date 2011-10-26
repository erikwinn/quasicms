<?php
	require(__DATAGEN_META_CONTROLS__ . '/ModuleMetaControlGen.class.php');

	/**
	 * This is a MetaControl customizable subclass, providing a QForm or QPanel access to event handlers
	 * and QControls to perform the Create, Edit, and Delete functionality of the
	 * Module class.  This code-generated class extends from
	 * the generated MetaControl class, which contains all the basic elements to help a QPanel or QForm
	 * display an HTML form that can manipulate a single Module object.
	 *
	 *
	 * @package Quasi
	 * @subpackage MetaControls
	 */
	class ModuleMetaControl extends ModuleMetaControlGen {
        protected $pnlTitle;
        protected $pnlDescription;
        
        /**
         * Create and setup QPanel pnlTitle
         * @param string $strControlId optional ControlId to use
         * @return QPanel
         */
        public function pnlTitle_Create($strControlId = null) {
            $this->pnlTitle = new QPanel($this->objParentObject, $strControlId . 'Title');
            $this->pnlTitle->Name = QApplication::Translate('Title');
            $this->pnlTitle->Text = $this->objModule->Title;
            return $this->pnlTitle;
        }

        /**
         * Create and setup QPanel pnlDescription
         * @param string $strControlId optional ControlId to use
         * @return QPanel
         */
        public function pnlDescription_Create($strControlId = null) {
            $this->pnlDescription = new QPanel($this->objParentObject, $strControlId . 'Description');
            $this->pnlDescription->Name = QApplication::Translate('Description');
            $this->pnlDescription->Text = $this->objModule->Description;
            return $this->pnlDescription;
        }
	}
?>