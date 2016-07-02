<?php

echo 'Iniciando cron...';

chdir(__DIR__);

require_once 'config.php';
require_once 'base/config2.php';

$tiempo = explode(' ', microtime());
$uptime = $tiempo[1] + $tiempo[0];

require_once 'base/fs_db2.php';
$db = new fs_db2();

require_once 'base/fs_default_items.php';

require_once 'base/fs_model.php';
require_model('empresa.php');
require_model('fs_var.php');
require_model('fs_log.php');

if( $db->connect() )
{
   $fsvar = new fs_var();
   $cron_vars = $fsvar->array_get( array('cron_exists' => FALSE, 'cron_lock' => FALSE, 'cron_error' => FALSE) );
   echo json_encode($cron_vars);
   if($cron_vars['cron_lock'])
   {
      echo "ERROR: Ya hay un cron en ejecución. Si crees que es un error,"
      . " ve a Admin > Información del sistema para solucionar el problema.";
  
      $cron_vars['cron_error'] = 'TRUE';
   }
   else
   {
   
      $cron_vars['cron_lock'] = 'TRUE';
      $cron_vars['cron_exists'] = 'TRUE';
      
      /// guardamos las variables
      $fsvar->array_save($cron_vars);
      
      /// indicamos el inicio en el log
      $fslog = new fs_log();
      $fslog->tipo = 'cron';
      $fslog->detalle = 'Ejecutando el cron...';
      $fslog->save();
      
      /// establecemos los elementos por defecto
      $fs_default_items = new fs_default_items();
      $empresa = new empresa();
      $fs_default_items->set_codalmacen( $empresa->codalmacen );
      $fs_default_items->set_coddivisa( $empresa->coddivisa );
      $fs_default_items->set_codejercicio( $empresa->codejercicio );
      $fs_default_items->set_codpago( $empresa->codpago );
      $fs_default_items->set_codpais( $empresa->codpais );
      $fs_default_items->set_codserie( $empresa->codserie );
      
      
      foreach($GLOBALS['modulos'] as $plugin)
      {
         if( file_exists('modulos/'.$plugin.'/cron.php') )
         {
            echo "\n***********************\nEjecutamos el cron.php del plugin ".$plugin."\n";
            
            include 'modulos/'.$plugin.'/cron.php';
            
            echo "\n***********************";
         }
      }
      
      /// indicamos el fin en el log
      $fslog = new fs_log();
      $fslog->tipo = 'cron';
      $fslog->detalle = 'Terminada la ejecución del cron.';
      $fslog->save();
      
      /// Eliminamos la variable cron_lock puesto que ya hemos terminado
      $cron_vars['cron_lock'] = FALSE;
   }
   
   /// guardamos las variables
   $fsvar->array_save($cron_vars);
   
   $db->close();
}
else
{
   echo "¡Imposible conectar a la base de datos!\n";
   
   foreach($db->get_errors() as $err)
   {
      echo $err."\n";
   }
}

$tiempo = explode(' ', microtime());
echo "\nTiempo de ejecución: ".number_format($tiempo[1] + $tiempo[0] - $uptime, 3)." s\n";