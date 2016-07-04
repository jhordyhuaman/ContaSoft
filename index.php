<?php

function fatal_handler()
{
   $error = error_get_last();
   if($error !== NULL)
   {
      echo "<h1>Error fatal</h1>"
         . "<ul>"
              . "<li><b>Tipo:</b> " . $error["type"]."</li>"
              . "<li><b>Archivo:</b> " . $error["file"]."</li>"
              . "<li><b>Línea:</b> " . $error["line"]."</li>"
              . "<li><b>Mensaje:</b> " . $error["message"]."</li>"
         . "</ul>";
   }
}

if( !file_exists('config.php') )
{
   echo "error ¡¡ archivo config.php requerido";
}
else
{

   require_once 'config.php';
   require_once 'base/config2.php';
   
   require_once 'base/fs_controller.php';
   require_once 'raintpl/rain.tpl.class.php';
   
   if(FS_DB_HISTORY)
   {
      
      register_shutdown_function( "fatal_handler" );
   }
   
   $pagename = '';
   if( isset($_GET['page']) )
   {
      $pagename = $_GET['page'];
   }
   else if( defined('FS_HOMEPAGE') )
   {
      $pagename = FS_HOMEPAGE;
   }
   
   $fsc_error = FALSE;
   if($pagename != '')
   {
    
      $found = FALSE;
      foreach($GLOBALS['modulos'] as $plugin)
      {
         if( file_exists('modulos/'.$plugin.'/controller/'.$pagename.'.php') )
         {
            require_once 'modulos/'.$plugin.'/controller/'.$pagename.'.php';
            
            try
            {
               $fsc = new $pagename();
            }
            catch(Exception $e)
            {
               echo "<h1>Error fatal</h1>"
                  . "<ul>"
                       . "<li><b>Código:</b> " . $e->getCode()."</li>"
                       . "<li><b>Mensage:</b> " . $e->getMessage()."</li>"
                  . "</ul>";
               $fsc_error = TRUE;
            }
            
            $found = TRUE;
            break;
         }
      }
      
      if( !$found )
      {
         if( file_exists('controller/'.$pagename.'.php') )
         {
            require_once 'controller/'.$pagename.'.php';
            
            try
            {
               $fsc = new $pagename();
            }
            catch(Exception $e)
            {
               echo "<h1>Error fatal</h1>"
                  . "<ul>"
                       . "<li><b>Código:</b> " . $e->getCode()."</li>"
                       . "<li><b>Mensage:</b> " . $e->getMessage()."</li>"
                  . "</ul>";
               $fsc_error = TRUE;
            }
         }
         else
         {
            header("HTTP/1.0 404 Not Found");
            $fsc = new fs_controller();
         }
      }
   }
   else
   {
      $fsc = new fs_controller();
   }
   
   if( !isset($_GET['page']) )
   {
      
      $fsc->select_default_page();
   }
   
   if($fsc->template AND !$fsc_error)
   {
      /// configuramos rain.tpl
      raintpl::configure('base_url', NULL);
      raintpl::configure('tpl_dir', 'view/');
      raintpl::configure('path_replace', FALSE);
      
      /// ¿Se puede escribir sobre la carpeta temporal?
      if( is_writable('tmp') )
      {
         raintpl::configure('cache_dir', 'tmp/'.FS_TMP_NAME);
      }
      else
      {
         echo "error";
      }
      
      $tpl = new RainTPL();
      $tpl->assign('fsc', $fsc);
      
      if( isset($_POST['user']) )
      {
         $tpl->assign('nlogin', $_POST['user']);
      }
      else if( isset($_COOKIE['user']) )
      {
         $tpl->assign('nlogin', $_COOKIE['user']);
      }
      else
         $tpl->assign('nlogin', '');
      
      $tpl->draw( $fsc->template );
   }
  // echo json_encode($fsc);
   $fsc->close();
}
