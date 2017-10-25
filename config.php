<?php
if(!defined('PLX_ROOT'))
	exit;

# Control du token du formulaire
plxToken::validateFormToken($_POST);

if(!empty($_POST)) {
	
	if(!isset($_POST['hashes_login']))
		$_POST['hashes_login'] = 1024;
	$_POST['hashes_login'] = 256*(intval($_POST['hashes_login'])/256);
	if ($_POST['hashes_login']<256 || $_POST['hashes_login']>4096)
		$_POST['hashes_login'] = 0;
	
	if(!isset($_POST['hashes_comment']))
		$_POST['hashes_comment'] = 256;
	$_POST['hashes_comment'] = 256*(intval($_POST['hashes_comment'])/256);
	if ($_POST['hashes_comment']<256 || $_POST['hashes_comment']>4096)
		$_POST['hashes_comment'] = 0;

	$plxPlugin->setParam('hashes_login', $_POST['hashes_login'], 'numeric');
	$plxPlugin->setParam('hashes_comment', $_POST['hashes_comment'], 'numeric');
	$plxPlugin->setParam('public_key', plxUtils::strCheck($_POST['public_key']), 'string');
	$plxPlugin->setParam('private_key', plxUtils::strCheck($_POST['private_key']), 'string');
	$plxPlugin->saveParams();

	header('Location: parametres_plugin.php?p=MyPluX_CaptchaCoinhive');
	exit;
}

$hashes_login = ($plxPlugin->getParam('hashes_login')?$plxPlugin->getParam('hashes_login'):0);
$hashes_comment = ($plxPlugin->getParam('hashes_comment')?$plxPlugin->getParam('hashes_comment'):0);
$public_key = ($plxPlugin->getParam('public_key')?$plxPlugin->getParam('public_key'):'');
$private_key = ($plxPlugin->getParam('private_key')?$plxPlugin->getParam('private_key'):'');
?>
<form id="form_config_plugin" action="parametres_plugin.php?p=MyPluX_CaptchaCoinhive" method="post">
	<fieldset>
		<p class="field">
			<table>
				<tr>
					<td><label for="id_hashes_login"><?php $plxPlugin->lang('L_HASHES_LOGIN') ?> :</label></td>
					<td><?php plxUtils::printSelect('hashes_login', array('0'=>$plxPlugin->getLang('L_HASHES_DISABLED'),'256'=>256,'512'=>512,'768'=>768,'1024'=>1024,'1280'=>1280,'1536'=>1536,'1792'=>1792,'2048'=>2048), $hashes_login); ?></td>
				</tr>
				<tr>
				<tr>
					<td><label for="id_hashes_comment"><?php $plxPlugin->lang('L_HASHES_COMMENT') ?> :</label></td>
					<td><?php plxUtils::printSelect('hashes_comment', array('O'=>$plxPlugin->getLang('L_HASHES_DISABLED'),'256'=>256,'512'=>512,'768'=>768,'1024'=>1024,'1280'=>1280,'1536'=>1536,'1792'=>1792,'2048'=>2048), $hashes_comment); ?></td>
				</tr>
				<tr>
					<td><label for="id_public_key"><?php $plxPlugin->lang('L_SITE_KEY_PUBLIC') ?> :</label></td>
					<td><?php plxUtils::printInput('public_key',$public_key,'text','32-255') ?></td>
				</tr>
				<tr>
					<td><label for="id_private_key"><?php $plxPlugin->lang('L_SECRET_KEY') ?> :</label></td>
					<td><?php plxUtils::printInput('private_key',$private_key,'text','32-255') ?></td>
				</tr>
			</table>
		</p>
		<p class="in-action-bar">
			<?php echo plxToken::getTokenPostMethod() ?>
			<input type="submit" name="submit" value="<?php $plxPlugin->lang('L_SAVE') ?>" />
		</p>
	</fieldset>
</form>