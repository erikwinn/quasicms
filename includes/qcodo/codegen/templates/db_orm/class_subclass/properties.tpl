        protected $strObjectClassName = <%= $objTable->ClassName %>;
        protected $strObjectTableName = <%= $objTable->Name %>;
        protected $aryQDatabaseFields;

        /**
         * Override method to perform a property "Get"
         * This will get the value of $strName
         *
         * @param string $strName Name of the property to get
         * @return mixed
         */

        public function __get($strName)
        {
            switch ($strName)
            {
                case 'ObjectClassName':
                    if(! $this->strObjectClassName)
                        return $this->strObjectClassName = get_class($this);
                case 'ObjectTableName':
                    if(! $this->strObjectTableName)
                        return $this->strObjectTableName = QConvertNotation::UnderscoreFromCamelCase($this->ObjectClassName);

/* were php object array handling not broken we could do:
                case 'QDatabaseFields':
                    if(! $this->aryQDatabaseFields)
                        return $this->aryQDatabaseFields = self::GetDatabase()->GetFieldsForTable($this->ObjectTableName);
*/
                default:
                    try {
                        return parent::__get($strName);
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }

        /**
         * Override method to perform a property "Set"
         * This will set the property $strName to be $mixValue
         *
         * @param string $strName Name of the property to set
         * @param string $mixValue New value of the property
         * @return mixed
         */
        public function __set($strName, $mixValue)
        {
            switch ($strName)
            {
/* example:
                case 'Foo':
                    try {
                        return ($this->strFoo = QType::Cast($mixValue, QType::String));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
*/
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