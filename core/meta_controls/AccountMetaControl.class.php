<?php
	require(__DATAGEN_META_CONTROLS__ . '/AccountMetaControlGen.class.php');

	/**
	 * This is a MetaControl customizable subclass, providing a QForm or QPanel access to event handlers
	 * and QControls to perform the Create, Edit, and Delete functionality of the
	 * Account class.  This code-generated class extends from
	 * the generated MetaControl class, which contains all the basic elements to help a QPanel or QForm
	 * display an HTML form that can manipulate a single Account object.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QForm or QPanel which instantiates a AccountMetaControl
	 * class.
	 *
	 * This file is intended to be modified.  Subsequent code regenerations will NOT modify
	 * or overwrite this file.
	 * 
	 * @package Quasi
	 * @subpackage MetaControls
	 */
	class AccountMetaControl extends AccountMetaControlGen
    {
       /**
         * Create and setup QTextBox txtUsername
         * @param string $strControlId optional ControlId to use
         * @return QTextBox
         */
        public function txtUsername_Create($strControlId = null, $blnNewAccount = false)
        {
            $this->txtUsername = new QTextBox($this->objParentObject, $strControlId);
            $this->txtUsername->Name = QApplication::Translate('Username');
            if(! $blnNewAccount)
                $this->txtUsername->Text = $this->objAccount->Username;
            $this->txtUsername->Required = true;
            $this->txtUsername->MaxLength = Account::UsernameMaxLength;
            return $this->txtUsername;
        }

        /**
         * Create and setup QTextBox txtPassword
         * @param string $strControlId optional ControlId to use
         * @return QTextBox
         */
        public function txtPassword_Create($strControlId = null)
        {
            $this->txtPassword = new QTextBox($this->objParentObject, $strControlId);
            $this->txtPassword->Name = QApplication::Translate('Password');
            $this->txtPassword->Required = true;
            $this->txtPassword->MaxLength = Account::PasswordMaxLength;
            return $this->txtPassword;
        }
        /**
         * This will save this object's Account instance,
         * updating only the fields which have had a control created for it.
         * It also encrypts the password field.
         */
 
        public function SaveAccount()
        {
            try {
                if ($this->txtUsername)
                    $this->objAccount->Username = $this->txtUsername->Text;
                if ($this->txtPassword)
                {
                    $strPassword = $this->txtPassword->Text;
                    if ( $strPassword && '' != $strPassword )
                        $this->objAccount->Password = sha1($strPassword);
                }                
                if ($this->txtNotes)
                    $this->objAccount->Notes = $this->txtNotes->Text;
                if ($this->txtLoginCount)
                    $this->objAccount->LoginCount = $this->txtLoginCount->Text;
                if ($this->chkOnline)
                    $this->objAccount->Online = $this->chkOnline->Checked;
                if ($this->chkOnetimePassword)
                    $this->objAccount->OnetimePassword = $this->chkOnetimePassword->Checked;
                if ($this->chkValidPassword)
                    $this->objAccount->ValidPassword = $this->chkValidPassword->Checked;
                if ($this->lstType)
                    $this->objAccount->TypeId = $this->lstType->SelectedValue;
                if ($this->lstStatus)
                    $this->objAccount->StatusId = $this->lstStatus->SelectedValue;
                if ($this->lstPerson)
                    $this->objAccount->PersonId = $this->lstPerson->SelectedValue;

                $this->objAccount->Save();

            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
        }

	}
?>