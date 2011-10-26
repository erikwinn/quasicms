<?php
	require(__DATAGEN_META_CONTROLS__ . '/ProductCategoryMetaControlGen.class.php');

	/**
	 * This is a MetaControl customizable subclass, providing a QForm or QPanel access to event handlers
	 * and QControls to perform the Create, Edit, and Delete functionality of the
	 * ProductCategory class.  This code-generated class extends from
	 * the generated MetaControl class, which contains all the basic elements to help a QPanel or QForm
	 * display an HTML form that can manipulate a single ProductCategory object.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QForm or QPanel which instantiates a ProductCategoryMetaControl
	 * class.
	 *
	 * This file is intended to be modified.  Subsequent code regenerations will NOT modify
	 * or overwrite this file.
	 * 
	 * @package Quasi
	 * @subpackage MetaControls
	 */
	class ProductCategoryMetaControl extends ProductCategoryMetaControlGen
    {
        protected $lblManufacturer;
        protected $lblSupplier;
        
        /**
         * Create and setup QLabel lblSupplier
         * @param string $strControlId optional ControlId to use
         * @return QLabel
         */
        public function lblSupplier_Create($strControlId = null)
        {
            $this->lblSupplier = new QLabel($this->objParentObject, $strControlId);
            $this->lblSupplier->Name = QApplication::Translate('Supplier');
            $this->lblSupplier->Text = ($this->objProduct->Supplier) ? $this->objProduct->Supplier->__toString() : null;
            return $this->lblSupplier;
        }
        /**
         * Create and setup QLabel lblManufacturer
         * @param string $strControlId optional ControlId to use
         * @return QLabel
         */
        public function lblManufacturer_Create($strControlId = null)
        {
            $this->lblManufacturer = new QLabel($this->objParentObject, $strControlId);
            $this->lblManufacturer->Name = QApplication::Translate('Manufacturer');
            $this->lblManufacturer->Text = ($this->objProduct->Manufacturer) ? $this->objProduct->Manufacturer->__toString() : null;
            return $this->lblManufacturer;
        }
        
	}
?>