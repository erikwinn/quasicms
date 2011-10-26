<?php
	require(__DATAGEN_META_CONTROLS__ . '/OrderChangeMetaControlGen.class.php');

	/**
	 * This is a MetaControl customizable subclass, providing a QForm or QPanel access to event handlers
	 * and QControls to perform the Create, Edit, and Delete functionality of the
	 * OrderChange class.  This code-generated class extends from
	 * the generated MetaControl class, which contains all the basic elements to help a QPanel or QForm
	 * display an HTML form that can manipulate a single OrderChange object.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QForm or QPanel which instantiates a OrderChangeMetaControl
	 * class.
	 *
	 * This file is intended to be modified.  Subsequent code regenerations will NOT modify
	 * or overwrite this file.
	 * 
	 * @package Quasi
	 * @subpackage MetaControls
	 */
	class OrderChangeMetaControl extends OrderChangeMetaControlGen
    {
        /**
         * Create and setup QListBox lstOrder
         * @param string $strControlId optional ControlId to use
         * @return QListBox
         */
        public function lstOrder_Create($strControlId = null)
        {
            $objDatabase = Order::GetDatabase();
            $this->lstOrder = new QListBox($this->objParentObject, $strControlId);
            $this->lstOrder->Name = QApplication::Translate('Order');
            $this->lstOrder->Required = true;
            if (!$this->blnEditMode)
                $this->lstOrder->AddItem(QApplication::Translate('- Select One -'), null);
            $objResult =  $objDatabase->Query('SELECT `id` FROM `order` ORDER BY id ASC');
            while($aryRow = $objResult->FetchRow())
            {
                $objListItem = new QListItem($aryRow[0], $aryRow[0]);
                if (($this->objOrderChange->Order) && ($this->objOrderChange->Order->Id == $aryRow[0]))
                    $objListItem->Selected = true;
                $this->lstOrder->AddItem($objListItem);
            }
            return $this->lstOrder;
        }
	}
?>