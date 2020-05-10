<?//не даем обращаться напрямую
if (!defined("INDEX_STARTED") || INDEX_STARTED!==true || (isset($_SESSION["an_uid"]) && $_SESSION["an_uid"] > 0))	die();?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Login</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>
body {background-color:#364359;}
input {font: normal 11px Verdana,Tahoma;color:#000000;}

td{
	font-family: Tahoma, Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #000000;
	line-height : 18px;
}

p {
padding: 4px 0;
margin: 4px 0
}

a{
	color: #000000;
	text-decoration : underline;
}
a:hover{
	color: #ef6c0b;
}

.errorpanel {background-color:#F17474;font-weight:bold;color:#CCCCCC;padding:10px;}
.actionpanel {background-color:#41A941;font-weight:bold;color:#CCCCCC;padding:10px;}
.editpanel {background-color:#FCE9BC;}


</style>

</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" >
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td>&nbsp;</td>
    <td align="center" valign="middle" class="main"><table width="477" height="312" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td height="312" valign="top">
        <table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td>&nbsp;</td>
            <td align="center" valign="top">
             <form method="post">
               <p>
               <input name="auth_name" type="text" class="input_enter" onclick="javascript:if(this.value=='Login')this.value='';" value="Login"></p>
               <p><input name="auth_pass" type="password" class="input_enter" value=""></p>
               <p><input type="submit" class="input_button" value="GO!"></p>
             </form>
            </td>
            <td>&nbsp;</td>
          </tr>
        </table></td>
        </tr>
    </table></td>
    <td>&nbsp;</td>
  </tr>
  <tr><td colspan="2"><table width="100%" border="0" cellpadding="0" cellspacing="0"><tr>
<td valign="top"><img src="logo.gif" align="absmiddle" style="margin-left:50px "></td></tr>
</table></td></tr>
</table>
</body>
</html>


