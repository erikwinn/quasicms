<?php

    $strProtocol = Quasi::$IsSsl ? 'https://' : 'http://';
    
    if($this->objPage)
    {
        $doctype = $this->objPage->DocType  . "\n";
        $htmlopen = '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"><head>' . "\n";
        
        ///@todo  pull in other METATAGS from Page object ..
        //Note: except for the expires, these seem to have little effect .. and i'm not sure about expires ..
        $metatags = '<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE" />' . "\n";
        $metatags .= '<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE" />' . "\n";       
//alt:        $metatags .= '<META HTTP-EQUIV="EXPIRES" CONTENT="' . gmdate('D, d M Y H:i:s', time()) . ' GMT" />' . "\n";
        $metatags .= '<META HTTP-EQUIV="EXPIRES" CONTENT="0" />' . "\n";
        
        if(Quasi::$EncodingType )
            $metatags .= '<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; CHARSET=' . Quasi::$EncodingType . '" />' . "\n";
                                    
        print $doctype . $htmlopen . $metatags ;
                
        if (isset($this->objPage))
            print '<title>' . $this->objPage->Title  . '</title>' . "\n";
            
        if( !empty($this->aryStyleSheets) )
            foreach($this->aryStyleSheets as $stylesheet)
            {
                if (file_exists(__WWWROOT__ . $stylesheet) )
                    print '<link rel="stylesheet" type="text/css" href="' . $strProtocol . Quasi::$ServerName . $stylesheet  . '">' . "\n";
            }
        else
            print "HEY - The stylesheet is missing!! Flying naked...<br />\n";
        
        if( !empty($this->aryJavaScripts) )
            foreach($this->aryJavaScripts as $javascript)
            {
                if (file_exists(__WWWROOT__ . $javascript) )
                    print '<script type="text/javascript" src="' . $strProtocol  . Quasi::$ServerName . $javascript  .  '"></script>' . "\n";
            }
        
        print '</head><body><div id="PageContainer">' . "\n";

        //Disabled javascript will really mess things up so ..
        $strNoJsMsg = Quasi::Translate('We are sorry, your browser does not support JavaScript! '
                                                             . ' This site is unlikely to work correctly.'
                                                             . ' Please enable JavaScript or visit using a different browser.');
        print '<noscript> <div class="warning"> ' . $strNoJsMsg . ' </div></noscript>';
        
        $this->RenderBegin();
        $this->objDefaultWaitIcon->Render('Position=absolute','Top=160','Left=200');
        $this->objPageView->Render();
        $this->RenderEnd();

        /* Make sure PageContainer extends to the entire layout. */
        print '<div class="spacer"></div>' . "\n";
        ?>
        
            <!--  Google Analytics   -->
            <script type="text/javascript">
            var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
            document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
            </script>
            <script type="text/javascript">
/*
            try {
                var pageTracker = _gat._getTracker("your google id here");
                pageTracker._trackPageview();
            } catch(err) {}

*/
            </script>

        <?php
        print '</div><!-- end PageContainer  --></body></html>';
    }
    else
    {
        $this->RenderBegin();
        print <<<HTML
        <html>
          <head><title>QuasiCMS - It Works!</title></head>
          <body>
            <h1>QuasiCMS - It Works!</h1>
            <p>So, now you need to add some pages and stuff...</p>
          </body>
        </html>
HTML;
        $this->RenderEnd();
    }
?>
