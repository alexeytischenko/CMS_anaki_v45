<?//не даем обращаться напрямую
if (!defined("INDEX_STARTED") || INDEX_STARTED!==true || (isset($_SESSION["an_uid"]) && $_SESSION["an_uid"] > 0))	die();?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Login</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link href="_Templates/default/_Styles/style.css" rel="stylesheet" type="text/css">
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" >
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>

    <td valign="top" bgcolor="#12395F">&nbsp;</td>
    <td align="center" valign="middle" class="main"><table width="477" height="312" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td height="33"><img src="_Templates/default/_Images/logo_1.gif" width="175" height="33"></td>
        </tr>
      <tr>
        <td height="312" valign="top"><table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0" class="enter">
          <tr>
            <td><img src="/_Images/1x1.gif" width="1" height="1"></td>

            <td><img src="/_Images/1x1.gif" width="1" height="1"></td>
            <td width="8" height="8"><img src="_Templates/default/_Images/enter_c2.gif" width="8" height="8"></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td align="center" valign="top" class="td_1"><table width="100%" border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td>Добро пожаловать в систему <br>

                  управления сайтом <strong><?=$sysProperties["site_name"]?></strong></td>
                <td width="146"><img src="/_Images/logo_2.gif"></td>
              </tr>
            </table>
              <p>&nbsp;</p>
             <form method="POST">
               <p>
               <input name="auth_name" type="text" class="input_enter" onclick="javascript:if(this.value=='Логин')this.value='';" value="Логин">

               </p>
               <p>                 <input name="auth_pass" type="password" class="input_enter" value="">
               </p>
               <p>                   <input type="submit" class="input_button" value="войти">
               </p>
             </form>
             <p>&nbsp;</p></td>
            <td>&nbsp;</td>

          </tr>
          <tr valign="bottom">
            <td width="7" height="20"><img src="_Templates/default/_Images/enter_c4.gif" width="7" height="10"></td>
            <td><img src="/_Images/1x1.gif" width="1" height="1"></td>
            <td><img src="_Templates/default/_Images/enter_c3.gif" width="8" height="10"></td>
          </tr>
        </table></td>
        </tr>
    </table></td>

    <td valign="top" bgcolor="#B5D539">&nbsp;</td>
  </tr>
  <tr align="right">
    <td colspan="3" valign="top" class="bottom">Copyright © <?=date("Y")?> ООО «MариКа», v4.1</td>
  </tr>
</table>


</body>
</html>



