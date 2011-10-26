        /**
         * Given the description of the Column's contents, this is a simple, express
         * way of adding a column to this <%= $objTable->ClassName %> datagrid.  The description of a column's
         * content can be either a text string description of a simple field name
         * in the <%= $objTable->ClassName %> object, or it can be any QQNode extending from QQN::<%= $objTable->ClassName %>().
         * 
         * MetaAddColumn will automatically pre-configure the column with the name, html
         * and sort rules given the content being specified.
         * 
         * Any of these things can be overridden with OverrideParameters.
         * 
         *
         * @param QControlProxy $pxyControl the control proxy to use
         * @param mixed $mixContents -  a string or QQNode 
         * @param string $strLinkHtml the HTML of the link text
         * @param string $strColumnTitle the HTML of the link text
         * @param string $aryOverrideParameters
         * @return QDataGridColumn
         */
        public function MetaAddProxyColumn(QControlProxy $pxyControl,
                                                                            $mixContent,
                                                                            $strLinkText = '',
                                                                            $strColumnTitle = '',
                                                                            $aryOverrideParameters = null)
        {
            $strHtml = '';
            $aryOverrides = null;
            $aryExtraOverrides = null;
            $aryParams = func_get_args();
            if( sizeof($aryParams > 4) )
                $aryExtraOverrides = array_slice($aryParams, 4);
            
            try {
                $objNode = $this->ResolveContentItem($mixContent);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            
            if( empty($strColumnTitle) )
                $strColumnTitle = QApplication::Translate(QConvertNotation::WordsFromCamelCase($objNode->_PropertyName));
            if( empty($strLinkText) )
                $strLinkText = '<?= $_ITEM->' . $objNode->_PropertyName . '?>';
                
            $strHtml = '<a href="#" <?= $_FORM->GetControl("' . $pxyControl->ControlId . '")->RenderAsEvents(<% foreach ($objTable->PrimaryKeyColumnArray as $objColumn) {%>$_ITEM-><%=$objColumn->PropertyName%> . "," . <%}%><%---------%>, false); ?>>' . $strLinkText . '</a>';

            $aryOverrides = array(
                    'HtmlEntities' => 'False',
                    'OrderByClause' => QQ::OrderBy($objNode->GetDataGridOrderByNode()),
                    'ReverseOrderByClause' => QQ::OrderBy($objNode->GetDataGridOrderByNode(), false)
                );

            if ($aryExtraOverrides)
                $aryOverrides = array_merge($aryOverrides, $aryExtraOverrides);
           $objNewColumn = new QDataGridColumn( $strColumnTitle, $strHtml, $aryOverrides );

            $this->AddColumn($objNewColumn);
            return $objNewColumn;
        }