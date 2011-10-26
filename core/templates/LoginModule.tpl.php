<script>
function clearText(thefield){
    if (thefield.defaultValue==thefield.value)
        thefield.value = ""
} 
</script>

<?php 

if(! $_CONTROL->Account)
{
    $strCreateAccountLink = __QUASI_SUBDIRECTORY__ . '/index.php/CreateAccount';
    $strLostPasswordLink = __QUASI_SUBDIRECTORY__ . '/index.php/LostPassword';
    
    
    print '<table><tr><td><div id="LoginBoxTitle">Sign in to view orders</div>';
    print '<div id="LoginUsername">';

    $_CONTROL->txtUsername->RenderWithError();

    print '</div> </td></tr>' . "\n";    
    print '<tr><td><div id="RegisterLink"><a tabindex="4" href="' . $strCreateAccountLink . '"> Join Now! </a><br /> (It\'s fast and free!)</div>';
    print '<div id="LoginPassword">' . "\n";

    $_CONTROL->txtPassword->RenderWithError();

    print '</div></td></tr>' . "\n";

    print '<tr><td><div id="ForgotLink"><a tabindex="5" href="' . $strLostPasswordLink . '">Forgot password?</a></div>
        <div id="LoginButton">' . "\n";

    $_CONTROL->btnLogin->Render();

    print '</div></td></tr></table>' . "\n";
}
else
{ ?>
    
    <div id="OnlineStatus">
    <table>    
        <tr><td colspan="2"><?php $_CONTROL->lblSignedInAs->Render(); ?></td></tr>
        <tr><td colspan="2"><?php $_CONTROL->lblLoginSpan->Render(); ?></td></tr> 
        <tr>
            <td><?php $_CONTROL->lblShoppingCartStatus->Render(); ?></td>
            <td><?php $_CONTROL->btnLogout->Render(); ?></td>
        </tr> 
    </table>
    </div>
<?php } ?>