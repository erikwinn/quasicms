<?php
	class QPanel extends QBlockControl {
		///////////////////////////
		// Private Member Variables
		///////////////////////////
		protected $strTagName = 'div';
        protected $strDefaultDisplayStyle = QDisplayStyle::Block;
        protected $strDisplayStyle = QDisplayStyle::Block;
		protected $blnIsBlockElement = true;
		protected $blnHtmlEntities = false;
	}
?>