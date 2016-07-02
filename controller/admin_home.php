<?php

class admin_home extends fs_controller
{
   public $disable_mod_plugins;
   public $disable_add_plugins;
   public $disable_rm_plugins;
   public $download_list;
   public $download_list2;
   public $last_download_check;
   public $new_downloads;
   public $paginas;
   public $step;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Panel de control', 'admin', TRUE, TRUE);
   }
   
   protected function private_core()
   {
      $this->check_htaccess();
      
      $this->disable_mod_plugins = FALSE;
      $this->disable_add_plugins = FALSE;
      $this->disable_rm_plugins = FALSE;
      if( defined('FS_DISABLE_MOD_PLUGINS') )
      {
         $this->disable_mod_plugins = FS_DISABLE_MOD_PLUGINS;
         $this->disable_add_plugins = FS_DISABLE_MOD_PLUGINS;
         $this->disable_rm_plugins = FS_DISABLE_MOD_PLUGINS;
      }
      
      if(!$this->disable_mod_plugins)
      {
         if( defined('FS_DISABLE_ADD_PLUGINS') )
         {
            $this->disable_add_plugins = FS_DISABLE_ADD_PLUGINS;
         }
         
         if( defined('FS_DISABLE_RM_PLUGINS') )
         {
            $this->disable_rm_plugins = FS_DISABLE_RM_PLUGINS;
         }
      }

      $fsvar = new fs_var();
      
      if( isset($_GET['check4updates']) )
      {
         $this->template = FALSE;
         if( $this->check_for_updates2() )
         {
         }
         else
            echo '';
          }
      else if( isset($_GET['updated']) )
      {
         $fsvar->simple_delete('updates');
      }

      else if( !$this->user->admin )
      {
         $this->new_error_msg('Sólo un administrador puede hacer cambios en esta página.');
      }

      else if( isset($_POST['modpages']) )
      {

         foreach($this->all_pages() as $p)
         {
            if( !$p->exists ) /// la página está en la base de datos pero ya no existe el controlador
            {
               if( $p->delete() )
               {
                  $this->new_message('Se ha eliminado automáticamente la página '.$p->name.
                          ' ya que no tiene un controlador asociado en la carpeta controller.');
               }
            }
            else if( !isset($_POST['enabled']) ) /// ninguna página marcada
            {
               $this->disable_page($p);
            }
            else if( !$p->enabled AND in_array($p->name, $_POST['enabled']) ) /// página no activa marcada para activar
            {
               $this->enable_page($p);
            }
            else if( $p->enabled AND !in_array($p->name, $_POST['enabled']) ) /// págine activa no marcada (desactivar)
            {
               $this->disable_page($p);
            }
         }
         
         $this->new_message('Datos guardados correctamente.');
      }

      else
      {
         /// ¿Guardamos las opciones de la pestaña avanzado?
         $guardar = FALSE;
         foreach($GLOBALS['config2'] as $i => $value)
         {
            if( isset($_POST[$i]) )
            {
               $GLOBALS['config2'][$i] = $_POST[$i];
               $guardar = TRUE;
            }
         }
         
         if($guardar)
         {
            $file = fopen('tmp/'.FS_TMP_NAME.'config2.ini', 'w');
            if($file)
            {
               foreach($GLOBALS['config2'] as $i => $value)
               {
                  if( is_numeric($value) )
                  {
                     fwrite($file, $i." = ".$value.";\n");
                  }
                  else
                  {
                     fwrite($file, $i." = '".$value."';\n");
                  }
               }
               
               fclose($file);
            }
            
            $this->new_message('Datos guardados correctamente.');
         }
      }
      
      
      $this->paginas = $this->all_pages();
      $this->load_menu(TRUE);
   }

   private function all_pages()
   {
      $pages = array();
      $page_names = array();

      foreach($this->plugins() as $plugin)
      {
         if( file_exists(getcwd().'/modulos/'.$plugin.'/controller') )
         {
            foreach( scandir(getcwd().'/modulos/'.$plugin.'/controller') as $f )
            {
               if( substr($f, -4) == '.php' )
               {
                  $p = new fs_page();
                  $p->name = substr($f, 0, -4);
                  $p->exists = TRUE;
                  $p->show_on_menu = FALSE;
                  
                  if( !in_array($p->name, $page_names) )
                  {
                     $pages[] = $p;
                     $page_names[] = $p->name;
                  }
               }
            }
         }
      }

      foreach( scandir(getcwd().'/controller') as $f)
      {
         if( substr($f, -4) == '.php' )
         {
            $p = new fs_page();
            $p->name = substr($f, 0, -4);
            $p->exists = TRUE;
            $p->show_on_menu = FALSE;
            
            if( !in_array($p->name, $page_names) )
            {
               $pages[] = $p;
               $page_names[] = $p->name;
            }
         }
      }
      
      /// completamos los datos de las páginas con los datos de la base de datos
      foreach($this->page->all() as $p)
      {
         $encontrada = FALSE;
         foreach($pages as $i => $value)
         {
            if($p->name == $value->name)
            {
               $pages[$i] = $p;
               $pages[$i]->enabled = TRUE;
               $pages[$i]->exists = TRUE;
               $encontrada = TRUE;
               break;
            }
         }
         if( !$encontrada )
         {
            $p->enabled = TRUE;
            $pages[] = $p;
         }
      }
      
      /// ordenamos
      usort($pages, function($a,$b){
         if($a->name == $b->name)
         {
            return 0;
         }
         else if($a->name > $b->name)
         {
            return 1;
         }
         else
            return -1;
      });
      
      return $pages;
   }

   private function plugins()
   {
      return $GLOBALS['modulos'];
   }
   

   private function enable_page($page)
   {
      $found = FALSE;
      foreach($this->plugins() as $plugin)
      {
         if( file_exists('modulos/'.$plugin.'/controller/'.$page->name.'.php') )
         {
            require_once 'modulos/'.$plugin.'/controller/'.$page->name.'.php';
            $new_fsc = new $page->name();
            $found = TRUE;
            
            if( isset($new_fsc->page) )
            {
               if( !$new_fsc->page->save() )
               {
                  $this->new_error_msg("Imposible guardar la página ".$page->name);
               }
            }
            else
            {
               $this->new_error_msg("Error al leer la página ".$page->name);
            }
            
            unset($new_fsc);
            break;
         }
      }
      
      if( !$found )
      {
         require_once 'controller/'.$page->name.'.php';
         $new_fsc = new $page->name(); /// cargamos el controlador asociado
         
         if( !$new_fsc->page->save() )
         {
            $this->new_error_msg("Imposible guardar la página ".$page->name);
         }
         
         unset($new_fsc);
      }
   }

   private function disable_page($page)
   {
      if($page->name == $this->page->name)
      {
         $this->new_error_msg("No puedes desactivar esta página (".$page->name.").");
      }
      else if( !$page->delete() )
      {
         $this->new_error_msg('Imposible eliminar la página '.$page->name.'.');
      }
   }

   public function traducciones()
   {
      $clist = array();
      $include = array(
          'factura','facturas','factura_simplificada','factura_rectificativa',
          'albaran','albaranes','pedido','pedidos','presupuesto','presupuestos',
          'provincia','apartado','cifnif','iva','irpf','numero2','serie','series'
      );
      
      foreach($GLOBALS['config2'] as $i => $value)
      {
         if( in_array($i, $include) )
         {
            $clist[] = array('nombre' => $i, 'valor' => $value);
         }
      }
      
      return $clist;
   }


   public function get_timezone_list()
   {
      $zones_array = array();
      
      $timestamp = time();
      foreach(timezone_identifiers_list() as $key => $zone) {
         date_default_timezone_set($zone);
         $zones_array[$key]['zone'] = $zone;
         $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
      }
      
      return $zones_array;
   }

   public function nf0()
   {
      return array(0, 1, 2, 3, 4, 5);
   }

   public function nf1()
   {
      return array(
          ',' => 'coma',
          '.' => 'punto',
          ' ' => '(espacio en blanco)'
      );
   }

   public function plugin_advanced_list()
   {
      $plugins = array();
      $disabled = array();
      
      if( defined('FS_DISABLED_PLUGINS') )
      {
         foreach( explode(',', FS_DISABLED_PLUGINS) as $aux )
         {
            $disabled[] = $aux;
         }
      }
      
      foreach( scandir(getcwd().'/modulos') as $f)
      {
         if( is_dir('modulos/'.$f) AND $f != '.' AND $f != '..' AND !in_array($f, $disabled) )
         {
            $plugin = array(
                'compatible' => FALSE,
                'description' => 'Sin descripción.',
                'download2_url' => '',
                'enabled' => FALSE,
                'idplugin' => NULL,
                'name' => $f,
                'prioridad' => '-',
                'require' => array(),
                'update_url' => '',
                'version' => 0,
                'version_url' => '',
                'wizard' => FALSE,
            );

            $plugins[] = $plugin;
         }
      }
      
      return $plugins;
   }

   private function delTree($dir)
   {
      $files = array_diff(scandir($dir), array('.','..'));
      foreach ($files as $file)
      {
         (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
      }
      return rmdir($dir);
   }

   private function enable_plugin($name)
   {
      if( substr($name, -7) == '-master' )
      {
         /// renombramos el directorio
         $name = substr($name, 0, -7);
         rename('modulos/'.$name.'-master', 'modulos/'.$name);
      }
      
      /// comprobamos las dependencias
      $install = TRUE;
      $wizard = FALSE;
      foreach($this->plugin_advanced_list() as $pitem)
      {
         if($pitem['name'] == $name)
         {
            $wizard = $pitem['wizard'];
            
            foreach($pitem['require'] as $req)
            {
               if( !in_array($req, $GLOBALS['modulos']) )
               {
                  $install = FALSE;
                  $txt = 'Dependencias incumplidas: <b>'.$req.'</b>';
                  
                  foreach($this->download_list2 as $value)
                  {
                     if($value->nombre == $req)
                     {
                        $txt .= '. Puedes descargar este plugin desde la <b>pestaña descargas</b>.';
                        break;
                     }
                  }
                  
                  $this->new_error_msg($txt);
               }
            }
            break;
         }
      }
      
      if( $install AND !in_array($name, $GLOBALS['modulos']) )
      {
         array_unshift($GLOBALS['modulos'], $name);
         
         if( file_put_contents('tmp/'.FS_TMP_NAME.'enabled_plugins.list', join(',', $GLOBALS['modulos']) ) !== FALSE )
         {
            if($wizard)
            {
               $this->new_advice('Ya puedes <a href="index.php?page='.$wizard.'">configurar el plugin</a>.');
               header('Location: index.php?page='.$wizard);
            }
            else
            {
               /// cargamos el archivo functions.php
               if( file_exists('modulos/'.$name.'/functions.php') )
               {
                  require_once 'modulos/'.$name.'/functions.php';
               }
               
               if( file_exists(getcwd().'/modulos/'.$name.'/controller') )
               {
                  /// activamos las páginas del plugin
                  $page_list = array();
                  foreach( scandir(getcwd().'/modulos/'.$name.'/controller') as $f)
                  {
                     if( is_string($f) AND strlen($f) > 0 AND !is_dir($f) )
                     {
                        if( substr($f, -4) == '.php' )
                        {
                           $page_name = substr($f, 0, -4);
                           $page_list[] = $page_name;
                           
                           require_once 'modulos/'.$name.'/controller/'.$f;
                           $new_fsc = new $page_name();
                           
                           if( !$new_fsc->page->save() )
                           {
                              $this->new_error_msg("Imposible guardar la página ".$page_name);
                           }
                           
                           unset($new_fsc);
                        }
                     }
                  }
                  
                  $this->new_message('Se han activado automáticamente las siguientes páginas: '.join(', ', $page_list) . '.');
               }
               
               $this->new_message('Plugin <b>'.$name.'</b> activado correctamente.');
               $this->load_menu(TRUE);
            }
            
            /// limpiamos la caché
            $this->cache->clean();
         }
         else
            $this->new_error_msg('Imposible activar el plugin <b>'.$name.'</b>.');
      }
   }

   private function disable_plugin($name)
   {
      if( file_exists('tmp/'.FS_TMP_NAME.'enabled_plugins.list') )
      {
         if( in_array($name, $this->plugins()) )
         {
            if( count($GLOBALS['modulos']) == 1 AND $GLOBALS['modulos'][0] == $name )
            {
               $GLOBALS['modulos'] = array();
               unlink('tmp/'.FS_TMP_NAME.'enabled_plugins.list');
               
               $this->new_message('Plugin <b>'.$name.'</b> desactivado correctamente.');
            }
            else
            {
               foreach($GLOBALS['modulos'] as $i => $value)
               {
                  if($value == $name)
                  {
                     unset($GLOBALS['modulos'][$i]);
                     break;
                  }
               }
               
               if( file_put_contents('tmp/'.FS_TMP_NAME.'enabled_plugins.list', join(',', $GLOBALS['modulos']) ) !== FALSE )
               {
                  $this->new_message('Plugin <b>'.$name.'</b> desactivado correctamente.');
               }
               else
                  $this->new_error_msg('Imposible desactivar el plugin <b>'.$name.'</b>.');
            }
         }
         
         
         /*
          * Desactivamos las páginas que ya no existen
          */
         $eliminadas = array();
         foreach($this->page->all() as $p)
         {
            $encontrada = FALSE;
            
            if( file_exists(getcwd().'/controller/'.$p->name.'.php') )
            {
               $encontrada = TRUE;
            }
            else
            {
               foreach($GLOBALS['modulos'] as $plugin)
               {
                  if( file_exists(getcwd().'/modulos/'.$plugin.'/controller/'.$p->name.'.php') AND $name != $plugin)
                  {
                     $encontrada = TRUE;
                     break;
                  }
               }
            }
            
            if( !$encontrada )
            {
               if( $p->delete() )
               {
                  $eliminadas[] = $p->name;
               }
            }
         }
         if($eliminadas)
         {
            $this->new_message('Se han eliminado automáticamente las siguientes páginas: '.join(', ', $eliminadas));
         }
         
         /// desactivamos los modulos que dependan de este
         foreach($this->plugin_advanced_list() as $plug)
         {
            /// ¿El plugin está activo?
            if( in_array($plug['name'], $GLOBALS['modulos']) )
            {
               /**
                * Si el plugin que hemos desactivado, es requerido por el plugin
                * que estamos comprobando, lo desativamos también.
                */
               if( in_array($name, $plug['require']) )
               {
                  $this->disable_plugin($plug['name']);
               }
            }
         }
         
         /// borramos los archivos temporales del motor de plantillas
         foreach( scandir(getcwd().'/tmp/'.FS_TMP_NAME) as $f)
         {
            if( substr($f, -4) == '.php' )
            {
               unlink('tmp/'.FS_TMP_NAME.$f);
            }
         }
         
         /// limpiamos la caché
         $this->cache->clean();
      }
   }

   public function check_for_updates2()
   {
      if( !$this->user->admin )
      {
         return FALSE;
      }
      else
      {
         $fsvar = new fs_var();
         
         /// comprobamos actualizaciones en los modulos
         $updates = FALSE;
         foreach($this->plugin_advanced_list() as $plugin)
         {
            if($plugin['version_url'] != '' AND $plugin['update_url'] != '')
            {
               /// plugin con descarga gratuita
               $internet_ini = @parse_ini_string( $this->curl_get_contents($plugin['version_url']) );
               if($internet_ini)
               {
                  if( $plugin['version'] < intval($internet_ini['version']) )
                  {
                     $updates = TRUE;
                     break;
                  }
               }
            }
            else if($plugin['idplugin'])
            {
               /// plugin de pago/oculto
               
               if($plugin['download2_url'] != '')
               {
                  /// download2_url implica que hay actualización
                  $updates = TRUE;
                  break;
               }
            }
         }

         if($updates)
         {
            $fsvar->simple_save('updates', 'true');
            return TRUE;
         }
         else
         {
            $fsvar->name = 'updates';
            $fsvar->delete();
            return FALSE;
         }
      }
   }

   private function curl_get_contents($url)
   {
      if( function_exists('curl_init') )
      {
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
         $data = curl_exec($ch);
         $info = curl_getinfo($ch);
         
         if($info['http_code'] == 301 OR $info['http_code'] == 302)
         {
            $redirs = 0;
            return $this->curl_redirect_exec($ch, $redirs);
         }
         else
         {
            curl_close($ch);
            return $data;
         }
      }
      else
         return file_get_contents($url);
   }

   private function curl_redirect_exec($ch, &$redirects, $curlopt_header = false)
   {
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $data = curl_exec($ch);
      $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      
      if($http_code == 301 || $http_code == 302)
      {
         list($header) = explode("\r\n\r\n", $data, 2);
         $matches = array();
         preg_match("/(Location:|URI:)[^(\n)]*/", $header, $matches);
         $url = trim(str_replace($matches[1], "", $matches[0]));
         $url_parsed = parse_url($url);
         if( isset($url_parsed) )
         {
            curl_setopt($ch, CURLOPT_URL, $url);
            $redirects++;
            return $this->curl_redirect_exec($ch, $redirects, $curlopt_header);
         }
      }
      
      if($curlopt_header)
      {
         curl_close($ch);
         return $data;
      }
      else
      {
         list(, $body) = explode("\r\n\r\n", $data, 2);
         curl_close($ch);
         return $body;
      }
   }
   
   private function check_htaccess()
   {
      if( !file_exists('.htaccess') )
      {
         $txt = file_get_contents('htaccess-sample');
         file_put_contents('.htaccess', $txt);
      }
      
      /// ahora comprobamos el de tmp/XXXXX/private_keys
      if( file_exists('tmp/'.FS_TMP_NAME.'private_keys') )
      {
         if( !file_exists('tmp/'.FS_TMP_NAME.'private_keys/.htaccess') )
         {
            file_put_contents('tmp/'.FS_TMP_NAME.'private_keys/.htaccess', 'Deny from all');
         }
      }
   }
}
