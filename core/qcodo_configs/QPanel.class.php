<?php
	class QPanel extends QBlockControl {
		///////////////////////////
		// Private Member Variables
		///////////////////////////
		protected $strTagName = 'div';
        protected $strDefaultDisplayStyle = QDisplayStyle::Block;

        //this is a bug fix - strDefaultDisplayStyle does not exist ..
        protected $strDisplayStyle = QDisplayStyle::Block;

		protected $blnIsBlockElement = true;
		protected $blnHtmlEntities = false;
	}
?>