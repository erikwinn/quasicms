<?php
/**
* This file is a part of Quasi CMS
*@package Quasi
*/
if(!defined('QUASICMS') ) die('No Quasi.');

if (!defined("ORDERPRINTER.CLASS.PHP")){
define("ORDERPRINTER.CLASS.PHP",1);

 define('FPDF_FONTPATH', __QUASI_CONTRIB__ . '/assets/php/fpdf/font/');
 require_once(__QUASI_CONTRIB__ . '/assets/php/fpdf/fpdf.php');

/**
* Class OrderPrinter - prints out label images, invoices and packing slips for orders
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id: OrderPrinter.class.php 517 2009-03-24 17:59:23Z erikwinn $
*@version 0.1
*
*@copyright (C) 2008 by Erik Winn
*@license GPL v.2

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111 USA

*
*@package Quasi
* @subpackage CMS
*/

    class OrderPrinter
    {
        /**
        *@var Order member object
        */
        protected $objOrder;
        /**
        *@var string Errors
        */
        protected $strErrors;
        /**
        *@var string strTempDirectory
        */
        protected $strTempDirectory =  '/tmp' ;
        /**
        *@var string strPrinterName
        */
        protected $strPrinterName = '';
        /**
        *@var string strPrinterOptions
        */
        protected $strPrinterOptions = '';
        /**
        *@var string strFileNamePrefix
        */
        protected $strFileNamePrefix = STORE_NAME;
        
        /**
        * OrderPrinter Constructor
        *
        * @param Order objOrder
        */
        public function __construct($objOrder)
        {
            $this->objOrder = $objOrder;    
        }
        public function PrintInvoice()
        {
            $strFileName = $this->strTempDirectory . '/' . $this->strFileNamePrefix . '_invoice_' . $this->objOrder->Id . '.pdf';
            
            $objPdf = new FPDF();
            $objPdf->AddPage();
            //Logo
            $objPdf->Image(__QUASI_CORE__ . '/assets/images/header_logo.jpg',10, 8, 33);
            //Move to the right
            $objPdf->Cell(80);
            $objPdf->SetFont('Arial','B',16);
            $objPdf->Cell(30, 10, STORE_NAME . ' Order Invoice', 'B', 1, 'C');
            
            $objPdf->Ln(3);
            $objPdf->SetFont('Arial','I',11);
            $objPdf->Cell(0, 5, STORE_NAME . ' - ' . STORE_ADDRESS1 . ', ' .  STORE_ADDRESS2 . ', ' . STORE_CITY . ', ' . STORE_STATE . ' ' . STORE_POSTAL_CODE, 0, 1, 'C');
            
            $objPdf->Ln(5);
            $objPdf->SetFont('Times','B',15);
            $objPdf->Cell(20, 10, 'Order #' . $this->objOrder->Id , 0,0);
            $objPdf->Cell(60);
            $objPdf->Cell(0, 10, 'Order Date: ' . $this->objOrder->CreationDate , 0,0);
            $objPdf->Ln(10);
            $objPdf->Line(5,$objPdf->GetY(),200,$objPdf->GetY());
            $objPdf->SetFont('Times','',14);
            $objPdf->Ln(5);
            $objPdf->Cell(0, 10, 'Shipping Method:    ' . $this->objOrder->ShippingMethod->Title . ' ' .  $this->objOrder->ShippingMethod->ServiceType, 0,1);
            $objPdf->Cell(0, 10, 'Payment Method:    ' . $this->objOrder->PaymentMethod->Title . ' via ' . $this->objOrder->PaymentMethod->ServiceProvider , 0,1);
            $objPdf->Ln(5);
            $objPdf->Line(5,$objPdf->GetY(),200,$objPdf->GetY());
            $objPdf->SetFont('Times','B',14);
            $objPdf->Cell(20, 10, 'Ship to: ' , 0,0);
            $objPdf->Cell(75);
            $objPdf->Cell(0, 10, 'Bill to: ' , 0,1);
            $objPdf->SetFont('Times','',12);
            $objPdf->Cell(20, 5, $this->objOrder->FullShippingName, 0,0);
            $objPdf->Cell(75);
            $objPdf->Cell(0, 5, $this->objOrder->FullBillingName, 0,1);
            if($this->objOrder->ShippingStreet1)
            {
                $objPdf->Cell(20, 5, $this->objOrder->ShippingStreet1, 0,0);
                $objPdf->Cell(75);
                $objPdf->Cell(0, 5, $this->objOrder->BillingStreet1, 0,1);
            }
            if($this->objOrder->ShippingStreet2)
            {
                $objPdf->Cell(20, 5, $this->objOrder->ShippingStreet2, 0,0);
                $objPdf->Cell(75);
                $objPdf->Cell(0, 5, $this->objOrder->BillingStreet2, 0,1);
            }
            if($this->objOrder->ShippingSuburb)
            {
                $objPdf->Cell(20, 5, $this->objOrder->ShippingSuburb, 0,0);
                $objPdf->Cell(75);
                $objPdf->Cell(0, 5, $this->objOrder->BillingSuburb, 0,1);
            }
            $objPdf->Cell(20, 5, $this->objOrder->ShippingCity, 0,0);
            $objPdf->Cell(75);
            $objPdf->Cell(0, 5, $this->objOrder->BillingCity, 0,1);
            if($this->objOrder->ShippingCounty)
            {
                $objPdf->Cell(20, 5, $this->objOrder->ShippingCounty, 0,0);
                $objPdf->Cell(75);
                $objPdf->Cell(0, 5, $this->objOrder->BillingCounty, 0,1);
            }
            if(ZoneType::NoZone != $this->objOrder->ShippingZoneId )
            {
                $objPdf->Cell(20, 5, ZoneType::ToString($this->objOrder->ShippingZoneId) . ' - ' . $this->objOrder->ShippingPostalCode, 0,0);
                $objPdf->Cell(75);
                $objPdf->Cell(0, 5, ZoneType::ToString($this->objOrder->BillingZoneId) . ' - ' . $this->objOrder->BillingPostalCode, 0,1);
            }
            $objPdf->Cell(20, 5, CountryType::ToString($this->objOrder->ShippingCountryId), 0,0);
            $objPdf->Cell(75);
            $objPdf->Cell(0, 5, CountryType::ToString($this->objOrder->BillingCountryId), 0,1);
            $objPdf->Ln(5);
            $objPdf->Line(5,$objPdf->GetY(),200,$objPdf->GetY());
            
            $objPdf->SetFont('Times','B',14);
            $objPdf->Cell(130, 10, 'Product Name' , 0,0);
            $objPdf->Cell(15, 10, 'Qty' , 0,0);
            $objPdf->Cell(15, 10, 'Price' , 0,0);
            $objPdf->Cell(15, 10, 'Total' , 0,1);
            $objPdf->SetFont('Times','',12);
            foreach($this->objOrder->GetOrderItemArray() as $objOrderItem )
            {
                $objPdf->Cell(130, 5, $objOrderItem->Product->Model, 0,0);
                $objPdf->Cell(15, 5, $objOrderItem->Quantity, 0,0);
                $objPdf->Cell(18, 5, money_format('%n', $objOrderItem->Product->RetailPrice), 0,0);
                $objPdf->Cell(18, 5, money_format('%n', ($objOrderItem->Quantity * $objOrderItem->Product->RetailPrice)) , 0,1);
            }
            $objPdf->Ln(3);
            $objPdf->Line(5,$objPdf->GetY(),200,$objPdf->GetY());
            $objPdf->Ln(3);
            $objPdf->Cell(150, 5, 'Sub-Total: ' , 0, 0, 'R');
            $objPdf->Cell(27, 5, money_format('%n', $this->objOrder->ProductTotalCharged) , 0, 1, 'R');
            $objPdf->Cell(150, 5, 'Shipping and Handling: ' , 0, 0, 'R');
            $objPdf->Cell(27, 5, money_format('%n', $this->objOrder->ShippingCharged + $this->objOrder->HandlingCharged) , 'B', 1, 'R' );
            $objPdf->Ln(3);
            $objPdf->Cell(150, 5, 'Grand Total: ' , 0, 0, 'R');
            $objPdf->Cell(27, 5, money_format('%n', $this->objOrder->ProductTotalCharged + $this->objOrder->ShippingCharged + $this->objOrder->HandlingCharged) , 0, 1, 'R');
            
            $objPdf->Ln(10);
            $objPdf->SetFont('Arial','I',16);
            $objPdf->Cell(0, 5, 'Thanks for using ' . STORE_NAME . '!', 0, 1, 'C');
            
            $objPdf->Output($strFileName);
            $this->printFile($strFileName);
        }
        
        public function PrintShippingLabels()
        {
            $strShipLabelFilename = $this->strTempDirectory . '/' . $this->strFileNamePrefix . '_shipping_label_' . $this->objOrder->Id;
            $strCustomsFormFilename = $this->strTempDirectory . '/' . $this->strFileNamePrefix .  '_customs_form_' . $this->objOrder->Id;

            //use old images if possible - this is for reprints on errors ..
            //Note that it is assumed that if the shipping label images are there then the customs images
            //are also there - if this is not true there will be problems ..
            if(file_exists($strShipLabelFilename))
                return $this->reprintLabels( $strShipLabelFilename, $strCustomsFormFilename);
                
            $objImage = $this->objOrder->CreateShippingLabel();
            //local pickup does not need labels ..
            if(!$objImage)
                return;
            
            if(is_string($objImage))
            {
                $fp = fopen( $strShipLabelFilename, 'w+' );
                fwrite( $fp, $objImage );
                fclose($fp);
                $this->printFile($strShipLabelFilename);
                if($this->objOrder->ExtraDocumentImages)
                {
                    foreach($this->objOrder->ExtraDocumentImages as $objDocumentImage)
                    {
                        $strFilename = $strShipLabelFilename . '_' . $objDocumentImage->Type . '-' . $objDocumentImage->Copies;
                        $fp = fopen( $strFilename, 'w+' );
                        fwrite( $fp, $objDocumentImage->Image );
                        fclose($fp);
                        $this->printFile($strFilename, $objDocumentImage->Copies);
                    }
                }
                if($this->objOrder->CustomsFormImages)
                {                    
                    foreach($this->objOrder->CustomsFormImages as $intIdx => $strDocumentImage)
                    {
                        $fp = fopen( $strCustomsFormFilename . '-' . $intIdx, 'w+' );
                        fwrite( $fp, $strDocumentImage );
                        fclose($fp);
                        $this->printFile( $strCustomsFormFilename . '-' . $intIdx);
                    }
                }
            }
            else
            {
                imagepng($objImage, $strShipLabelFilename);
                $this->printFile($strShipLabelFilename);
                if($this->objOrder->ExtraDocumentImages)
                {
                    foreach($this->objOrder->ExtraDocumentImages as $objDocumentImage)
                    {
                        $strFilename = $strShipLabelFilename . '_' . $objDocumentImage->Type . '-' . $objDocumentImage->Copies;
                        imagepng($objDocumentImage->Image, $strFileName );
                        $this->printFile($strFilename, $objDocumentImage->Copies);
                    }
                }
                if($this->objOrder->CustomsFormImages)
                {
                    foreach($this->objOrder->CustomsFormImages as $intIdx => $strDocumentImage)
                        imagepng($strDocumentImage,$strCustomsFormFilename . '-' . $intIdx );
                        $this->printFile( $strCustomsFormFilename . '-' . $intIdx);
                }
            }
        }
        
        public function reprintLabels($strLabelFileName, $strCustomsFormFileName)
        {
            $aryDirList = scandir($this->strTempDirectory);

            foreach($aryDirList as $strFileName)
            {
                if('.' == $strFileName || '..' == $strFileName )
                    continue;
                if(false !== strpos( $strLabelFileName, $strFileName )
                    || false !== strpos( $strCustomsFormFileName, $strFileName ))
                    $this->printFile($this->strTempDirectory . '/' . $strFileName);
            }            
        }
        public function printFile($strFileName, $intQuantity=1)
        {
            $strCommand = sprintf("lpr -P%s %s '-#%s' %s",
                                                $this->strPrinterName,
                                                $this->strPrinterOptions,
                                                $intQuantity,
                                                $strFileName);

            exec($strCommand);
        }
        
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'TempDirectory':
                    return $this->strTempDirectory ;
                case 'FileNamePrefix':
                    return $this->strFileNamePrefix ;
                case 'PrinterName':
                    return $this->strPrinterName ;
                case 'PrinterOptions':
                    return $this->strPrinterOptions ;
                case 'Order':
                    return $this->objOrder ;
                case 'Errors':
                    return $this->objErrors ;
                default:
                    throw new QCallerException('OrderPrinter::__get() Unknown property: ' . $strName);
            }
        }
        
        public function __set($strName, $mixValue)
        {
            switch ($strName)
            {
                case 'Order':
                    try {
                        return ($this->objOrder = QType::Cast($mixValue, 'Order' ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'PrinterOptions':
                    try {
                        return ($this->strPrinterOptions = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'PrinterName':
                    try {
                        return ($this->strPrinterName = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'FileNamePrefix':
                    try {
                        return ($this->strFileNamePrefix = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'TempDirectory':
                    try {
                        return ($this->strTempDirectory = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Errors':
                    try {
                        return ($this->strErrors = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }

                default:
                        throw new QCallerException('OrderPrinter::__set() Unknown property: ' . $strName);
            }
        }
    
    }//end class
}//end define

?>