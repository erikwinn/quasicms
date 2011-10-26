<?php
	require(__DATAGEN_CLASSES__ . '/PageGen.class.php');

	/**
	 * The Page class defined here represents the "page" table 
	 * in the database, and extends from the base class PageGen
	 * class, which contains all the basic CRUD-type functionality as well as
	 * basic methods to handle relationships and index-based loading.
	 *
     * This class is used by PageView - it contains no actual content but
     * is only the anchor object for ContentBlocks and various page related
     * meta data, (DOCTYPE, CSS sheets, Title, etc.). PageView provides some default
     * general locations for ContentBlocks (currently Header, LeftPanel, CenterPanel
     * RightPanel, and Footer) as divs with Ids and classes based on the flags in
     * the page table which may be configured via the Quasi dashboard.
     *
     * This is also the object to which a MenuItem or Href must link - ie. to the Name
     * of the page. Name must not contain spaces as it is part of the URL. eg:
     *  http://my.site.com/home will be the home page (also the default.)
     *
     * The bulk of the active processing is in PageGen, this subclass provides an
     * override interface that is protected from code generation in the event of
     * a database schema change.
     *
	 * @package Quasi
	 * @subpackage ORM
	 * 
	 */
	class Page extends PageGen {
		/**
		 * Default "to string" handler
		 * Allows pages to _p()/echo()/print() this object, and to define the default
		 * way this object would be outputted.
		 *
		 * Can also be called directly via $objPage->__toString().
		 *
		 * @return string a nicely formatted string representation of this object
		 */
		public function __toString() {
			return sprintf('%s',  $this->Name);
		}

        public function __get($strName)
        {
            switch ($strName)
            {
                case 'DocType':
                    return ($this->DocTypeId) ? PageDocType::$NameArray[$this->DocTypeId] : null;
                default:
                    try {
                        return parent::__get($strName);
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }


/*
		public function __set($strName, $mixValue) {
			switch ($strName) {
				case 'SomeNewProperty':
					try {
						return ($this->strSomeNewProperty = QType::Cast($mixValue, QType::String));
					} catch (QInvalidCastException $objExc) {
						$objExc->IncrementOffset();
						throw $objExc;
					}

				default:
					try {
						return (parent::__set($strName, $mixValue));
					} catch (QCallerException $objExc) {
						$objExc->IncrementOffset();
						throw $objExc;
					}
			}
		}
*/

	}
?>