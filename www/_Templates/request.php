<?
//не даем обращаться напрямую
if (!defined("MAIN_STARTED") || MAIN_STARTED!==true)	die("ошибка подключения шаблона");
?>

<?
echo $showPage->values[$lang]["Текст"];

$showform = true;
if ($_POST["sub"] == 99)	{
	if ($_SESSION['captcha'] != $_POST['captcha_phrase'])	{
		echo "<p class='error_mess'>".TemplateTranslate("captcha", $slang)."</p>";
	}
	elseif (strlen($_POST["name"])==0 || strlen($_POST["email"])==0 || strlen($_POST["phone"])==0 || strlen($_POST["text"])==0)	{
		echo "<p class='error_mess'>".TemplateTranslate("Заполните обязательные поля", $slang)."</p>";
	}
	elseif (!preg_match("/^[-a-zA-Z0-9_.]{1,}@[-a-zA-Z0-9_.]{1,}\.[a-zA-Z]{2,4}$/", $_POST["email"]))
		echo "<p class='error_mess'>".TemplateTranslate("Неверный формат email", $slang)."</p>";
	else	{
		$body = "<p>
            Контактное  лицо: ".$_POST["name"]."<br>
            email адрес: ".$_POST["email"]."<br>
            Контактный телефон: ".$_POST["phone"]."<br>
            Краткое содержание заявки: ".$_POST["text"];

		$showform = false;


		require_once(ROOTPATH . '/_Libs/PHPMailer_v5.1/class.phpmailer.php');
		$mail = new PHPMailer(); // defaults to using php "mail()"
		//$body = file_get_contents(ROOTPATH . '/contents.html');

        $mail->ContentType = "text/html";
        $mail->CharSet = "utf-8";

		$mail->AddReplyTo($modermail, $sysProperties["site_name"]);
		$mail->SetFrom($modermail, $sysProperties["site_name"]);
		$mail->AddAddress($modermail, $sysProperties["site_name"]);
		$mail->Subject = "Заявка с сайта";

		$mail->MsgHTML($body);
  		//$mail->AddAttachment(ROOTPATH . "1.doc");      // attachment

		if(!$mail->Send()) $error_mess .= "<p>Mailer Error: " . $mail->ErrorInfo;
		echo "<p>".TemplateTranslate("Спасибо", $slang)."</p>";
	}
}
		if ($showform) :
		?>
            <form name="form1" method="post" action="">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" class="zayavka2">
              <tr>
                <td width="220" align="left" valign="top" nowrap><strong><?=TemplateTranslate("Фамилия", $slang)?>:*</strong></td>
                <td align="left" valign="top"><strong>
                   <input type="text" name="name" id="name2" value="<?=$_POST["name"]?>">
                </strong></td>
              </tr>
              <tr>
                <td width="220" align="left" valign="top" nowrap><strong><?=TemplateTranslate("Вашemailадрес", $slang)?>:*</strong></td>
                <td align="left" valign="top"><input type="text" name="email" id="email2" value="<?=$_POST["email"]?>"></td>
              </tr>
              <tr>
                <td width="220" align="left" valign="top" nowrap><strong><?=TemplateTranslate("Контактный телефон", $slang)?>:*</strong></td>
                <td align="left" valign="top"><strong>
                  <input type="text" name="phone" id="phone2" value="<?=$_POST["phone"]?>">
                </strong></td>
              </tr>
              <tr>
                <td width="220" align="left" valign="top" nowrap><strong><?=TemplateTranslate("заявка", $slang)?>:*</strong></td>
                <td align="left" valign="top"><strong>
                  <textarea name="text" id="text2" cols="45" rows="5"><?=$_POST["text"]?></textarea>
                </strong></td>
              </tr>
              <tr>
                <td width="200" valign="middle"><strong>Символы на картинке <span class="theme"><span class="red">*</span></span>:</strong></td>
                <td valign="middle"><image src="/_Libs/ncaptcha.php" onClick="this.src='/_Libs/ncaptcha.php?'+(new Date()).getTime()"></br><input name="captcha_phrase" maxlength="50" value="" size="40" type="text"></td>
              </tr>
            </table>

            <p><input type="hidden" name="sub" value="99">
              <input name="button3" type="submit" class="button_2" id="button3" value=" ">
            </p>
          </form>


            <?endif;?>