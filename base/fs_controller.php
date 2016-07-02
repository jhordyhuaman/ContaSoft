<?php

require_once 'base/fs_cache.php';
require_once 'base/fs_db2.php';
require_once 'base/fs_default_items.php';
require_once 'base/fs_model.php';

require_model('agente.php');
require_model('divisa.php');
require_model('empresa.php');
require_model('fs_access.php');
require_model('fs_page.php');
require_model('fs_user.php');
require_model('fs_extension.php');
require_model('fs_log.php');
require_model('fs_var.php');


class fs_controller
{
   
   protected $db;
   private $uptime;
   private $errors;
   private $messages;
   private $advices;
   private $last_changes;
   private $simbolo_divisas;
   
   public $user;
   public $page;
   protected $menu;
   public $template;
   public $query;
   public $empresa;
   public $default_items;
   protected $cache;
   public $extensions;
   
 
   public function __construct($name = '', $title = 'home', $folder = '', $admin = FALSE, $shmenu = TRUE, $important = FALSE)
   {
      $tiempo = explode(' ', microtime());
      $this->uptime = $tiempo[1] + $tiempo[0];
      $this->errors = array();
      $this->messages = array();
      $this->advices = array();
      $this->simbolo_divisas = array();
      $this->extensions = array();
      
      $this->cache = new fs_cache();
      $this->db = new fs_db2();
      
      /// comprobamos la versión de PHP
      if( floatval( substr(phpversion(), 0, 3) ) < 5.3 )
      {
         $this->new_error_msg('FacturaScripts necesita PHP 5.3 o superior, y tú tienes PHP '.phpversion().'.');
      }
      
      if( $this->db->connect() )
      {
         $this->user = new fs_user();
         $this->page = new fs_page(
                 array(
                     'name' => $name,
                     'title' => $title,
                     'folder' => $folder,
                     'version' => $this->version(),
                     'show_on_menu' => $shmenu,
                     'important' => $important
                 )
         );
         if($name != '')
         {
            $this->page->save();
         }
         
         $this->empresa = new empresa();
         $this->default_items = new fs_default_items();
         
         /// cargamos las extensiones
         $fsext = new fs_extension();
         foreach($fsext->all() as $ext)
         {
            if($ext->to == $name OR ( is_null($ext->to) AND in_array($ext->type,array('head','hidden_iframe')) ) )
            {
               $this->extensions[] = $ext;
            }
         }
         
         if( isset($_GET['logout']) )
         {
            $this->template = 'login/default';
            $this->log_out();
         }
         else if( isset($_POST['new_password']) AND isset($_POST['new_password2']) )
         {
            $ips = array();
            
            if( $this->ip_baneada($ips) )
            {
               $this->banear_ip($ips);
               $this->new_error_msg('Tu IP ha sido baneada. Tendrás que esperar 10 minutos antes de volver a intentar entrar.');
            }
            else if($_POST['new_password'] != $_POST['new_password2'])
            {
               $this->new_error_msg('Las contraseñas no coinciden.');
            }
            else if($_POST['new_password'] == '')
            {
               $this->new_error_msg('Tienes que escribir una contraseña nueva.');
            }
            else if($_POST['db_password'] != FS_DB_PASS)
            {
               $this->banear_ip($ips);
               $this->new_error_msg('La contraseña de la base de datos es incorrecta.');
            }
            else
            {
               $suser = $this->user->get($_POST['user']);
               if($suser)
               {
                  $suser->set_password($_POST['new_password']);
                  if( $suser->save() )
                  {
                     $this->new_message('Contraseña cambiada correctamente.');
                  }
                  else
                     $this->new_error_msg('Imposible cambiar la contraseña del usuario.');
               }
            }
            
            $this->template = 'login/default';
         }
         else if( !$this->log_in() )
         {
            $this->template = 'login/default';
            $this->public_core();
         }
         else if( $this->user->have_access_to($this->page->name) )
         {
            if($name == '')
            {
               $this->template = 'index';
            }
            else
            {
               $this->set_default_items();
               
               $this->template = $name;
               
               $this->query = '';
               if( isset($_REQUEST['query']) )
               {
                  $this->query = $_REQUEST['query'];
               }
               
               /// quitamos extensiones de páginas a las que el usuario no tenga acceso
               foreach($this->extensions as $i => $value)
               {
                  if($value->type != 'config')
                  {
                     if( !$this->user->have_access_to($value->from) )
                     {
                        unset($this->extensions[$i]);
                     }
                  }
               }
               
               $this->private_core();
            }
         }
         else if($name == '')
         {
            $this->template = 'index';
         }
         else
         {
            $this->template = 'access_denied';
            $this->user->clean_cache(TRUE);
            $this->empresa->clean_cache();
         }
      }
      else
      {
         $this->template = 'no_db';
         $this->new_error_msg('¡Imposible conectar con la base de datos <b>'.FS_DB_NAME.'</b>!');
      }
   }
   
   
   public function version()
   {
      if( file_exists('VERSION') )
      {
         $v = file_get_contents('VERSION');
         return trim($v);
      }
      else
         return '0';
   }
   
   public function close()
   {
      $this->db->close();
   }
   
   public function new_error_msg($msg = FALSE, $tipo = 'error', $alerta = FALSE)
   {
      if($msg)
      {
         $this->errors[] = str_replace("\n", ' ', $msg);
         
         $fslog = new fs_log();
         $fslog->tipo = $tipo;
         $fslog->detalle = $msg;
         $fslog->ip = $_SERVER['REMOTE_ADDR'];
         $fslog->alerta = $alerta;
         
         if($this->user)
         {
            $fslog->usuario = $this->user->nick;
         }
         
         $fslog->save();
      }
   }
   
  
   public function get_errors()
   {
      $full = array_merge( $this->errors, $this->db->get_errors() );
      
      if( isset($this->empresa) )
      {
         $full = array_merge( $full, $this->empresa->get_errors() );
      }
      
      return $full;
   }
 
   public function new_message($msg=FALSE, $save=FALSE, $tipo = 'msg')
   {
      if($msg)
      {
         $this->messages[] = str_replace("\n", ' ', $msg);
         
         if($save)
         {
            $fslog = new fs_log();
            $fslog->tipo = $tipo;
            $fslog->detalle = $msg;
            $fslog->ip = $_SERVER['REMOTE_ADDR'];
            
            if($this->user)
            {
               $fslog->usuario = $this->user->nick;
            }
            
            $fslog->save();
         }
      }
   }

   public function get_messages()
   {
      return $this->messages;
   }

   public function new_advice($msg=FALSE)
   {
      if($msg)
      {
         $this->advices[] = str_replace("\n", ' ', $msg);
      }
   }
   
   public function get_advices()
   {
      return $this->advices;
   }
   

   public function url()
   {
      return $this->page->url();
   }
   

   private function ip_baneada(&$ips)
   {
      $baneada = FALSE;
      
      if( file_exists('tmp/'.FS_TMP_NAME.'ip.log') )
      {
         $file = fopen('tmp/'.FS_TMP_NAME.'ip.log', 'r');
         if($file)
         {
            /// leemos las líneas
            while( !feof($file) )
            {
               $linea = explode(';', trim(fgets($file)));
               
               if( intval($linea[2]) > time() )
               {
                  if($linea[0] == $_SERVER['REMOTE_ADDR'] AND intval($linea[1]) > 5)
                  {
                     $baneada = TRUE;
                  }
                  
                  $ips[] = $linea;
               }
            }
            
            fclose($file);
         }
      }
      
      return $baneada;
   }
   

   private function banear_ip(&$ips)
   {
      $file = fopen('tmp/'.FS_TMP_NAME.'ip.log', 'w');
      if($file)
      {
         $encontrada = FALSE;
         
         foreach($ips as $ip)
         {
            if($ip[0] == $_SERVER['REMOTE_ADDR'])
            {
               fwrite( $file, $ip[0].';'.( 1+intval($ip[1]) ).';'.( time()+600 ) );
               $encontrada = TRUE;
            }
            else
               fwrite( $file, join(';', $ip) );
         }
         
         if(!$encontrada)
         {
            fwrite( $file, $_SERVER['REMOTE_ADDR'].';1;'.( time()+600 ) );
         }
         
         fclose($file);
      }
   }

   private function ip_in_whitelist($ip)
   {
      if(FS_IP_WHITELIST == '*')
      {
         return TRUE;
      }
      else
      {
         $aux = explode(',', FS_IP_WHITELIST);
         return in_array($ip, $aux);
      }
   }
   
   private function log_in()
   {
      $ips = array();
      
      if( $this->ip_baneada($ips) )
      {
         $this->banear_ip($ips);
         $this->new_error_msg('Tu IP ha sido baneada. Tendrás que esperar 10 minutos antes de volver a intentar entrar.', 'login', TRUE);
      }
      else if( isset($_POST['user']) AND isset($_POST['password']) )
      {
         if( FS_DEMO ) 
         {
            $user = $this->user->get($_POST['user']);
            if( !$user )
            {
               $user = new fs_user();
               $user->nick = $_POST['user'];
               $user->set_password('demo');
               
               /// creamos un agente para asociarlo
               $agente = new agente();
               $agente->codagente = $agente->get_new_codigo();
               $agente->nombre = $_POST['user'];
               $agente->apellidos = 'Demo';
               if( $agente->save() )
               {
                  $user->codagente = $agente->codagente;
               }
            }
            
            $user->new_logkey();
            if( $user->save() )
            {
               setcookie('user', $user->nick, time()+FS_COOKIES_EXPIRE);
               setcookie('logkey', $user->log_key, time()+FS_COOKIES_EXPIRE);
               $this->user = $user;
               $this->load_menu();
            }
         }
         else
         {
            $user = $this->user->get($_POST['user']);
            $password = $_POST['password'];
            if($user)
            {
               /**
                * En versiones anteriores se guardaban las contraseñas siempre en
                * minúsculas, por eso, para dar compatibilidad comprobamos también
                * en minúsculas.
                */
               if( $user->password == sha1($password) OR $user->password == sha1( mb_strtolower($password, 'UTF8') ) )
               {
                  $user->new_logkey();
                  
                  if( !$user->admin AND !$this->ip_in_whitelist($user->last_ip) )
                  {
                     $this->new_error_msg('No puedes acceder desde esta IP.', 'login', TRUE);
                  }
                  else if( $user->save() )
                  {
                     setcookie('user', $user->nick, time()+FS_COOKIES_EXPIRE);
                     setcookie('logkey', $user->log_key, time()+FS_COOKIES_EXPIRE);
                     $this->user = $user;
                     $this->load_menu();
                     
                     /// añadimos el mensaje al log
                     $fslog = new fs_log();
                     $fslog->usuario = $user->nick;
                     $fslog->tipo = 'login';
                     $fslog->detalle = 'Login correcto.';
                     $fslog->ip = $user->last_ip;
                     $fslog->save();
                  }
                  else
                  {
                     $this->new_error_msg('Imposible guardar los datos de usuario.');
                     $this->cache->clean();
                  }
               }
               else
               {
                  $this->new_error_msg('¡Contraseña incorrecta!');
                  $this->banear_ip($ips);
               }
            }
            else
            {
               $this->new_error_msg('El usuario '.$_POST['user'].' no existe!');
               $this->user->clean_cache(TRUE);
               $this->cache->clean();
            }
         }
      }
      else if( isset($_COOKIE['user']) AND isset($_COOKIE['logkey']) )
      {
         $user = $this->user->get($_COOKIE['user']);
         if($user)
         {
            if($user->log_key == $_COOKIE['logkey'])
            {
               $user->logged_on = TRUE;
               $user->update_login();
               $this->user = $user;
               $this->load_menu();
            }
            else if( !is_null($user->log_key) )
            {
               $this->new_message('¡Cookie no válida! Alguien ha accedido a esta cuenta desde otro PC con IP: '
                       .$user->last_ip.". Si has sido tú, ignora este mensaje.");
               $this->log_out();
            }
         }
         else
         {
            $this->new_error_msg('¡El usuario '.$_COOKIE['user'].' no existe!');
            $this->log_out(TRUE);
            $this->user->clean_cache(TRUE);
            $this->cache->clean();
         }
      }
      
      return $this->user->logged_on;
   }

   private function log_out($rmuser = FALSE)
   {
      /// borramos las cookies
      setcookie('logkey', '', time()-FS_COOKIES_EXPIRE);
      if( isset($_SERVER['REQUEST_URI']) )
      {
         $aux = parse_url( str_replace('/index.php', '', $_SERVER['REQUEST_URI']) );
         setcookie('logkey', '', time()-FS_COOKIES_EXPIRE, $aux['path'].'/');
      }
      
      /// ¿Eliminamos la cookie del usuario?
      if($rmuser)
      {
         setcookie('user', '', time()-FS_COOKIES_EXPIRE);
         if( isset($_SERVER['REQUEST_URI']) )
         {
            $aux = parse_url( str_replace('/index.php', '', $_SERVER['REQUEST_URI']) );
            setcookie('user', '', time()-FS_COOKIES_EXPIRE, $aux['path'].'/');
         }
      }
      
      /// guardamos el evento en el log
      $fslog = new fs_log();
      $fslog->tipo = 'login';
      $fslog->detalle = 'El usuario ha cerrado la sesión.';
      $fslog->ip = $_SERVER['REMOTE_ADDR'];
      
      if( isset($_COOKIE['user']) )
      {
         $fslog->usuario = $_COOKIE['user'];
      }
      
      $fslog->save();
   }
   public function duration()
   {
      $tiempo = explode(" ", microtime());
      return (number_format($tiempo[1] + $tiempo[0] - $this->uptime, 3) . ' s');
   }
  
   public function selects()
   {
      return $this->db->get_selects();
   }
  
   public function transactions()
   {
      return $this->db->get_transactions();
   }
   
   public function get_db_history()
   {
      return $this->db->get_history();
   }

   protected function load_menu($reload=FALSE)
   {
      $this->menu = $this->user->get_menu($reload);
   }
 
   public function folders()
   {
      $folders = array();
      foreach($this->menu as $m)
      {
         if($m->folder!='' AND $m->show_on_menu AND !in_array($m->folder, $folders) )
         {
            $folders[] = $m->folder;
         }
      }
      return $folders;
   }
 
   public function pages($f='')
   {
      $pages = array();
      foreach($this->menu as $p)
      {
         if($f == $p->folder AND $p->show_on_menu AND !in_array($p, $pages) )
         {
            $pages[] = $p;
         }
      }
      return $pages;
   }
  
   protected function public_core()
   {
      
   }
  
   protected function private_core()
   {
      
   }
 
   public function select_default_page()
   {
      if( $this->db->connected() )
      {
         if( $this->user->logged_on )
         {
            $page = '';
            if( is_null($this->user->fs_page) )
            {
               $page = 'admin_home';
          
               foreach($this->menu as $p)
               {
                  if($p->show_on_menu)
                  {
                     $page = $p->name;
                  }
               }
            }
            else
               $page = $this->user->fs_page;
            
            header('Location: index.php?page='.$page);
         }
      }
   }

   private function set_default_items()
   {
      if( isset($_GET['default_page']) )
      {
         if($_GET['default_page'] == 'FALSE')
         {
            $this->default_items->set_default_page(NULL);
            $this->user->fs_page = NULL;
         }
         else
         {
            $this->default_items->set_default_page( $this->page->name );
            $this->user->fs_page = $this->page->name;
         }
         
         $this->user->save();
      }
      else if( is_null($this->default_items->default_page()) )
      {
         $this->default_items->set_default_page( $this->user->fs_page );
      }
      
      if( is_null($this->default_items->showing_page()) )
      {
         $this->default_items->set_showing_page( $this->page->name );
      }
      
      $this->default_items->set_codejercicio($this->empresa->codejercicio);
      
      if( isset($_COOKIE['default_almacen']) )
      {
         $this->default_items->set_codalmacen( $_COOKIE['default_almacen'] );
      }
      else
      {
         $this->default_items->set_codalmacen( $this->empresa->codalmacen );
      }
      
      if( isset($_COOKIE['default_formapago']) )
      {
         $this->default_items->set_codpago( $_COOKIE['default_formapago'] );
      }
      else
      {
         $this->default_items->set_codpago( $this->empresa->codpago );
      }
      
      if( isset($_COOKIE['default_impuesto']) )
      {
         $this->default_items->set_codimpuesto( $_COOKIE['default_impuesto'] );
      }
      
      $this->default_items->set_codpais( $this->empresa->codpais );
      $this->default_items->set_codserie( $this->empresa->codserie );
      $this->default_items->set_coddivisa( $this->empresa->coddivisa );
   }

   protected function save_codejercicio($cod)
   {
      $this->new_error_msg('fs_controller::save_codejercicio() es una función obsoleta.');
   }
   
  
   protected function save_codalmacen($cod)
   {
      setcookie('default_almacen', $cod, time()+FS_COOKIES_EXPIRE);
      $this->default_items->set_codalmacen($cod);
   }
   
  
   protected function save_codcliente($cod)
   {
      $this->new_error_msg('fs_controller::save_codcliente() es una función obsoleta.');
   }
   
  
   protected function save_coddivisa($cod)
   {
      $this->new_error_msg('fs_controller::save_coddivisa() es una función obsoleta.');
   }
   
  
   protected function save_codfamilia($cod)
   {
      $this->new_error_msg('fs_controller::save_codfamilia() es una función obsoleta.');
   }
   
   
   protected function save_codpago($cod)
   {
      setcookie('default_formapago', $cod, time()+FS_COOKIES_EXPIRE);
      $this->default_items->set_codpago($cod);
   }
  
   protected function save_codimpuesto($cod)
   {
      setcookie('default_impuesto', $cod, time()+FS_COOKIES_EXPIRE);
      $this->default_items->set_codimpuesto($cod);
   }

   protected function save_codpais($cod)
   {
      $this->new_error_msg('fs_controller::save_codpais() es una función obsoleta.');
   }
   
   protected function save_codproveedor($cod)
   {
      $this->new_error_msg('fs_controller::save_codproveedor() es una función obsoleta.');
   }
 
   protected function save_codserie($cod)
   {
      $this->new_error_msg('fs_controller::save_codserie() es una función obsoleta.');
   }

   public function today()
   {
      return date('d-m-Y');
   }

   public function hour()
   {
      return Date('H:i:s');
   }
   
   public function random_string($length = 30)
   {
      return mb_substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"),
              0, $length);
   }
   
   protected function duplicated_petition($id)
   {
      $ids = $this->cache->get_array('petition_ids');
      if( in_array($id, $ids) )
      {
         return TRUE;
      }
      else
      {
         $ids[] = $id;
         $this->cache->set('petition_ids', $ids, 300);
         return FALSE;
      }
   }
  
   public function system_info()
   {
      $txt = 'datos'.$this->version()."\n";
      
      if( $this->db->connect() )
      {
         if($this->user->logged_on)
         {
            $txt .= 'os: '.php_uname()."\n";
            $txt .= 'php: '.phpversion()."\n";
            $txt .= 'database type: '.FS_DB_TYPE."\n";
            $txt .= 'database version: '.$this->db->version()."\n";
            
            if( $this->cache->connected() )
            {
               $txt .= "memcache: YES\n";
               $txt .= 'memcache version: '.$this->cache->version()."\n";
            }
            else
               $txt .= "memcache: NO\n";
            
            if( function_exists('curl_init') )
            {
               $txt .= "curl: YES\n";
            }
            else
               $txt .= "curl: NO\n";
            
            $txt .= 'modulos: '.join(',', $GLOBALS['modulos'])."\n";
            
            if( isset($_SERVER['REQUEST_URI']) )
            {
               $txt .= 'url: '.$_SERVER['REQUEST_URI']."\n------";
            }
         }
      }
      
      foreach($this->get_errors() as $e)
      {
         $txt .= "\n" . $e;
      }
      
      return str_replace('"', "'", $txt);
   }
   
   public function simbolo_divisa($coddivisa = FALSE)
   {
      if(!$coddivisa)
      {
         $coddivisa = $this->empresa->coddivisa;
      }
      
      if( isset($this->simbolo_divisas[$coddivisa]) )
      {
         return $this->simbolo_divisas[$coddivisa];
      }
      else
      {
         $divisa = new divisa();
         $divi0 = $divisa->get($coddivisa);
         if($divi0)
         {
            $this->simbolo_divisas[$coddivisa] = $divi0->simbolo;
            return $divi0->simbolo;
         }
         else
            return '?';
      }
   }
   
 
   public function show_precio($precio=0, $coddivisa=FALSE, $simbolo=TRUE, $dec=FS_NF0)
   {
      if($coddivisa === FALSE)
      {
         $coddivisa = $this->empresa->coddivisa;
      }
      
      if(FS_POS_DIVISA == 'right')
      {
         if($simbolo)
         {
            return number_format($precio, $dec, FS_NF1, FS_NF2).' '.$this->simbolo_divisa($coddivisa);
         }
         else
            return number_format($precio, $dec, FS_NF1, FS_NF2).' '.$coddivisa;
      }
      else
      {
         if($simbolo)
         {
            return $this->simbolo_divisa($coddivisa).number_format($precio, $dec, FS_NF1, FS_NF2);
         }
         else
            return $coddivisa.' '.number_format($precio, $dec, FS_NF1, FS_NF2);
      }
   }
  
   public function show_numero($num=0, $decimales=FS_NF0, $js=FALSE)
   {
      if($js)
      {
         return number_format($num, $decimales, '.', '');
      }
      else
         return number_format($num, $decimales, FS_NF1, FS_NF2);
   }
 
   public function new_change($txt, $url, $nuevo=FALSE)
   {
      $this->get_last_changes();
      if( count($this->last_changes) > 0 )
      {
         if($this->last_changes[0]['url'] == $url)
         {
            $this->last_changes[0]['nuevo'] = $nuevo;
         }
         else
            array_unshift($this->last_changes, array('texto' => ucfirst($txt), 'url' => $url, 'nuevo' => $nuevo, 'cambio' => date('d-m-Y H:i:s')) );
      }
      else
         array_unshift($this->last_changes, array('texto' => ucfirst($txt), 'url' => $url, 'nuevo' => $nuevo, 'cambio' => date('d-m-Y H:i:s')) );
     
      $num = 10;
      foreach($this->last_changes as $i => $value)
      {
         if($num > 0)
         {
            $num--;
         }
         else
         {
            unset($this->last_changes[$i]);
         }
      }
      
      $this->cache->set('last_changes_'.$this->user->nick, $this->last_changes);
   }

   public function get_last_changes()
   {
      if( !isset($this->last_changes) )
      {
         $this->last_changes = $this->cache->get_array('last_changes_'.$this->user->nick);
      }
      
      return $this->last_changes;
   }
   
  
   public function clean_last_changes()
   {
      $this->last_changes = array();
      $this->cache->delete('last_changes_'.$this->user->nick);
   }
   
   
   public function check_for_updates()
   {
      if($this->user->admin)
      {
         $desactivado = FALSE;
         if( defined('FS_DISABLE_MOD_PLUGINS') )
         {
            $desactivado = FS_DISABLE_MOD_PLUGINS;
         }
         
         if($desactivado)
         {
            return FALSE;
         }
         else
         {
            $fsvar = new fs_var();
            return $fsvar->simple_get('updates');
         }
      }
      else
         return FALSE;
   }

   public function get_js_location($filename)
   {
      $found = FALSE;
      foreach($GLOBALS['modulos'] as $plugin)
      {
         if( file_exists('modulos/'.$plugin.'/view/js/'.$filename) )
         {
            return FS_PATH.'modulos/'.$plugin.'/view/js/'.$filename;
         }
      }

      /// si no está en los modulos estará en el núcleo
      return FS_PATH.'view/js/'.$filename;
   }

   public function get_max_file_upload()
   {
      $max = intval( ini_get('post_max_size') );
      
      if( intval(ini_get('upload_max_filesize')) < $max )
      {
         $max = intval(ini_get('upload_max_filesize'));
      }
      
      return $max;
   }
}
