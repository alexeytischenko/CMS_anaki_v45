<?
//не даем обращаться напрямую
if (!defined("MAIN_STARTED") || MAIN_STARTED!==true)	die("ошибка подключения шаблона");
?>
<form method="post" action="/page/<?=$showPage->url?>/" name="regform" enctype="multipart/form-data">
	<table class="register" cellpadding="0" cellspacing="0">
	  <tbody>
	  <? if ($curUser->uid != -1)	:?>
	  <tr>
	  <td width="200" valign="middle"><strong>Аватар<span class="theme"><span class="red"></span></span>:</strong></td>
		<td valign="middle">
		<?$avat = $curUser->userdata["avat"];
		if (strlen($avat) > 0)	{echo "<img src='/_Upload/_users/".$avat."' width='70' height='70'>";}?>
		<input name="userdata_avat" size="28" type="file">
		<input type=hidden name='load_avat' value='<?=$avat?>'>
		</td>
	    </tr>
	   <?endif;?>
	    <tr>
	      <td width="200" valign="middle"><strong>E-mail<span class="theme"><span class="red"> *</span></span>:</strong></td>
	      <td valign="middle"><input size="40" name="email" value="<?=CustomLibs::getFormValue("email", $curUser->email)?>" type="text"></td>
	    </tr>
	    <tr>
	      <td width="200" valign="middle"><strong>Пароль <span class="theme"><span class="red"><?=(($curUser->uid == -1)? "*" : "")?></span></span>:</strong></td>
	      <td valign="middle"><input size="40" name="passwd" type="password"></td>
	    </tr>
	    <tr>
	      <td width="200" valign="middle"><strong>Повторить пароль <span class="theme"><span class="red"><?=(($curUser->uid == -1)? "*" : "")?></span></span>:</strong></td>
	      <td valign="middle"><input size="40" name="newpasswd" type="password"></td>
	    </tr>
	    <tr>
	      <td width="200" valign="middle"><strong>Имя <span class="theme"><span class="red">*</span></span>:</strong></td>
	      <td valign="middle"><input size="40" name="name" value="<?=CustomLibs::getFormValue("name", $curUser->name)?>" type="text"></td>
	    </tr>
	    <tr>
	      <td width="200" valign="middle"><strong>Фамилия</strong></td>
	      <td valign="middle"><input size="40" name="lastname" value="<?=CustomLibs::getFormValue("lastname", $curUser->lastname)?>" type="text"></td>
	    </tr>
	    <tr>
	      <td width="200" valign="middle"><strong>Телефон</strong></td>
	      <td valign="middle"><input size="40" name="userdata_tel" value="<?=CustomLibs::getFormValue("userdata_tel", $curUser->userdata["tel"])?>" type="text"></td>
	    </tr>
	    <tr>
	      <td width="200" valign="middle"><strong>Организация</strong></td>
	      <td valign="middle"><input size="40" name="userdata_office" value="<?=CustomLibs::getFormValue("userdata_office", $curUser->userdata["office"])?>" type="text"></td>
	    </tr>

	    <tr class="tr_1">
	      <td width="200" valign="top"><strong>Информация о себе:</strong></td>
	      <td valign="middle"><label>
	        <textarea name="userdata_inn" id="info" cols="40" rows="5"><?=CustomLibs::getFormValue("userdata_inn", $curUser->userdata["inn"])?></textarea>
	      </label></td>
	    </tr>

	    <tr class="tr_1">
	      <td width="200" valign="middle"><strong>Подписка на новости:</strong></td>
	      <td valign="middle"><input name="userdata_news" type="checkbox" class="check" id="checkbox2" <?=((strlen(CustomLibs::getFormValue("userdata_news", $curUser->userdata["news"])) > 0)? " checked" : "")?>></td>
	    </tr>
	    <? if ($curUser->uid == -1)	:?>
	    <tr>
	      <td width="200" valign="middle"><strong>Символы на картинке <span class="theme"><span class="red">*</span></span>:</strong></td>
	      <td valign="middle"><image src="/_Libs/ncaptcha.php" onClick="this.src='/_Libs/ncaptcha.php?'+(new Date()).getTime()"></br><input name="captcha_phrase" maxlength="50" value="" size="40" type="text"></td>
	    </tr>
	    <?endif;?>
	    <tr class="tr_1">
	      <td width="200" valign="middle">&nbsp;</td>
	      <td valign="middle">
	<br/><label>
	        <input name="button" type="submit" class="button orange_b" id="button" value="<?=(($curUser->uid == -1)? "Зарегистрироваться" : "Сохранить")?>">
	      </label></td>
	    </tr>
	    <tr class="tr_1">
	      <td colspan="2" valign="middle" class="theme"><span class="red">*</span> Поля, отмеченные звёздочкой, являются обязательными для заполнения</td>
	    </tr>
	  </tbody>
	  <tfoot>
	  </tfoot>
	</table>
	<input type="hidden" name="sub" value="1">
</form>



