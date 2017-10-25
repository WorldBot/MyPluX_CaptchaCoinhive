<?php
/**
 * Plugin MyPluX_CaptchaCoinhive
 *
 * @author	Yannic H.
 **/
class MyPluX_CaptchaCoinhive extends plxPlugin {

	/**
	 * Constructeur de la classe
	 **/
	public function __construct($default_lang) {

		# Appel du constructeur
		parent::__construct($default_lang);

		# Droits d'accès à la configuration du plugin
		$this->setConfigProfil(PROFIL_ADMIN);
		
		# Initialisation des données
		$this->public_key = $this->getParam('public_key')!='' ? $this->getParam('public_key') : '';
		$this->private_key = $this->getParam('private_key')!='' ? $this->getParam('private_key') : '';
		$this->hashes_login = intval($this->getParam('hashes_login'))>0 ? intval($this->getParam('hashes_login')) : 0;
		$this->hashes_comment = intval($this->getParam('hashes_comment'))>0 ? intval($this->getParam('hashes_comment')) : 0;
		if (!empty(trim($this->public_key)) && !empty(trim($this->private_key)))
			$this->enable = true;
		else
			$this->enable = false;			

		# Ajouts des hooks
		$this->addHook('AdminAuth','AdminAuth');
		$this->addHook('AdminAuthPrepend','AdminAuthPrepend');
		$this->addHook('plxShowCapchaQ', 'plxShowCapchaQ');
		$this->addHook('plxShowCapchaR', 'plxShowCapchaR');
		$this->addHook('plxMotorNewCommentaire', 'plxMotorNewCommentaire');
		$this->addHook('IndexEnd', 'IndexEnd');
	}

	/**
	 * Affiche le capcha dans la page de login
	 **/
	public function AdminAuth() {
	if ($this->enable && $this->hashes_login>0)
		echo '
		<script src="https://authedmine.com/lib/captcha.min.js" async></script>
		<div class="coinhive-captcha" 
		style="max-width:274px !important;"
		data-hashes="'.$this->hashes_login.'"
		data-key="'.$this->public_key.'"
		data-whitelabel="true"
		data-disable-elements="input[type=submit]"
		>
		'.$this->getLang('L_MESSAGE').'
		</div>';
	}

	/**
	 * Vérifie le capcha de la page de login
	 **/
	public function AdminAuthPrepend() {
	if ($this->enable && $this->hashes_login>0)
		echo '<?php 
		if (!isset($_POST["coinhive-captcha-token"]) || empty(plxUtils::strCheck(trim($_POST["coinhive-captcha-token"]))))
		{
			$_POST["login"] = $_POST["password"] = "";
		}
		else
		{
			$_POST["coinhive-captcha-token"] = plxUtils::strCheck(trim($_POST["coinhive-captcha-token"]));
			$response = json_decode(file_get_contents("https://api.coinhive.com/token/verify", false,
			stream_context_create([
				"http" => [
				"header"  => "Content-type: application/x-www-form-urlencoded\r\n",
				"method"  => "POST",
				"content" => http_build_query(["secret"=>"'.$this->private_key.'","token"=>$_POST["coinhive-captcha-token"],"hashes"=>'.$this->hashes_login.'])
				]
			])
			));

			if ($response && $response->success)
			{
				$_POST["capcha_token"]=$_SESSION["capcha_token"]="Done";
				$_SESSION["capcha"] = sha1($content["rep"]);
			}
			else
			{
				$_POST["login"] = $_POST["password"] = "";
			}
		}	
		?>';	
	}

	/**
	 * Affiche le capcha dans les commentaires
	 **/
	public function plxShowCapchaQ() {
	if ($this->enable && $this->hashes_comment>0)
		echo '
		<script src="https://authedmine.com/lib/captcha.min.js" async></script>
		<div class="coinhive-captcha" 
		data-hashes="'.$this->hashes_comment.'"
		data-key="'.$this->public_key.'"
		data-whitelabel="false"
		data-disable-elements="input[type=submit]"
		>
		'.$this->getLang('L_MESSAGE').'
		</div>
		<?php return true; ?>';
	}

	/**
	 * Interrompre la fonction CapchaR
	 **/
	public function plxShowCapchaR() {
	if ($this->enable && $this->hashes_comment>0)
		echo '<?php return true; ?>';
	}

	/**
	 * Méthode qui vérifie le capcha commentaire
	 **/
	public function plxMotorNewCommentaire() {
	if ($this->enable && $this->hashes_comment>0)
		echo '<?php
		if (!isset($_POST["coinhive-captcha-token"]) || empty(plxUtils::strCheck(trim($_POST["coinhive-captcha-token"]))))
		{
			return L_NEWCOMMENT_ERR_ANTISPAM;
		}
		$_POST["coinhive-captcha-token"] = plxUtils::strCheck(trim($_POST["coinhive-captcha-token"]));
		$response = json_decode(file_get_contents("https://api.coinhive.com/token/verify", false,
		stream_context_create([
			"http" => [
			"header"  => "Content-type: application/x-www-form-urlencoded\r\n",
			"method"  => "POST",
			"content" => http_build_query(["secret"=>"'.$this->private_key.'","token"=>$_POST["coinhive-captcha-token"],"hashes"=>'.$this->hashes_comment.'])
			]
		])
		));

		if ($response && $response->success)
		{
			$_POST["capcha_token"]=$_SESSION["capcha_token"]="Done";
			$_SESSION["capcha"] = sha1($content["rep"]);
		}
		else
		{
			return L_NEWCOMMENT_ERR_ANTISPAM;
		}
		?>';
	}

	/**
	 * Cache l'input du captcha de PluXml si présent.
	 **/
	public function IndexEnd() {
	if ($this->enable && $this->hashes_comment>0)
		echo '<?php
		if(preg_match("/<input(?:.*?)name=[\'\"]rep[\'\"](?:.*)size=([\'\"])([^\'\"]+).*>/i", $output, $m)) {
			$o = str_replace("type=\"text\"", "type=\"hidden\" value=\"Done\"", $m[0]);
			$output = str_replace($m[0], $o, $output);
		}
		if(preg_match("/<input(?:.*?)name=[\'\"]rep[\'\"](?:.*)size=([\'\"])([^\'\"]+).*>/i", $output, $m)) {
			$o = str_replace("inline", "none", $m[0]);
			$output = str_replace($m[0], $o, $output);
		}
		?>';
	}
}
