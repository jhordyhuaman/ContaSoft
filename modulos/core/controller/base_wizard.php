<?php


require_model('almacen.php');
require_model('divisa.php');
require_model('ejercicio.php');
require_model('forma_pago.php');
require_model('pais.php');
require_model('serie.php');


class base_wizard extends fs_controller
{
   public $almacen;
   public $bad_password;
   public $divisa;
   public $ejercicio;
   public $forma_pago;
   public $irpf;
   public $pais;
   public $serie;
   public $step;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Asistente de instalación', 'admin', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->check_menu();
      
      $this->almacen = new almacen();
      $this->bad_password = FALSE;
      $this->divisa = new divisa();
      $this->ejercicio = new ejercicio();
      $this->forma_pago = new forma_pago();
      $this->irpf = 0;
      $this->pais = new pais();
      $this->serie = new serie();
      
      /// ¿Hay errores? Usa informes > Errores
      if( $this->get_errors() )
      {
         $this->new_message('Puedes solucionar la mayoría de errores en la base de datos ejecutando el '
                 . '<a href="index.php?page=informe_errores" target="_blank">informe de errores</a> '
                 . 'sobre las tablas.');
      }
      
      if( $this->user->password == sha1('admin') )
      {
         $this->bad_password = TRUE;
      }
      
     
      if( isset($_POST['nombrecorto']) )
      {
         /// guardamos los datos de la empresa
         $this->empresa->nombre = $_POST['nombre'];
         $this->empresa->nombrecorto = $_POST['nombrecorto'];
         $this->empresa->cifnif = $_POST['cifnif'];
         $this->empresa->administrador = $_POST['administrador'];
         $this->empresa->codpais = $_POST['codpais'];
         $this->empresa->provincia = $_POST['provincia'];
         $this->empresa->ciudad = $_POST['ciudad'];
         $this->empresa->direccion = $_POST['direccion'];
         $this->empresa->codpostal = $_POST['codpostal'];
         $this->empresa->telefono = $_POST['telefono'];
         $this->empresa->fax = $_POST['fax'];
         $this->empresa->web = $_POST['web'];
         
         $continuar = TRUE;
         if( isset($_POST['npassword']) )
         {
            if($_POST['npassword'] != '')
            {
               if($_POST['npassword'] == $_POST['npassword2'])
               {
                  $this->user->set_password($_POST['npassword']);
                  $this->user->save();
               }
               else
               {
                  $this->new_error_msg('Las contraseñas no coinciden.');
                  $continuar = FALSE;
               }
            }
         }
         
         if(!$continuar)
         {
            /// no hacemos nada
         }
         else if( $this->empresa->save() )
         {
            $this->new_message('Datos guardados correctamente.');
         }
         else
            $this->new_error_msg ('Error al guardar los datos.');
      }
      else if( isset($_POST['coddivisa']) )
      {
         $this->empresa->coddivisa = $_POST['coddivisa'];
         
         if( $this->empresa->save() )
         {
            foreach($GLOBALS['config2'] as $i => $value)
            {
               if( isset($_POST[$i]) )
               {
                  $GLOBALS['config2'][$i] = $_POST[$i];
               }
            }
            
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
         else
            $this->new_error_msg ('Error al guardar los datos.');
      }
      else if( isset($_POST['codejercicio']) )
      {
         $this->empresa->contintegrada = isset($_POST['contintegrada']);
         $this->empresa->codejercicio = $_POST['codejercicio'];
         $this->empresa->codserie = $_POST['codserie'];
         $this->empresa->codpago = $_POST['codpago'];
         $this->empresa->codalmacen = $_POST['codalmacen'];
         $this->empresa->recequivalencia = isset($_POST['recequivalencia']);
         
         if( $this->empresa->save() )
         {
            /// guardamos las opciones por defecto de almacén y forma de pago
            $this->save_codalmacen($_POST['codalmacen']);
            $this->save_codpago($_POST['codpago']);
            
            foreach($GLOBALS['config2'] as $i => $value)
            {
               if( isset($_POST[$i]) )
               {
                  $GLOBALS['config2'][$i] = $_POST[$i];
               }
            }
            
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
            $this->step = 5;
         }
         else
            $this->new_error_msg ('Error al guardar los datos.');
      }
      foreach($this->serie->all() as $serie)
      {
         if($serie->codserie == $this->empresa->codserie)
         {
            if( isset($_POST['irpf_serie']) )
            {
               $serie->irpf = floatval($_POST['irpf_serie']);
               $serie->save();
            }
            
            $this->irpf = $serie->irpf;
            break;
         }
      }
   }
   private function check_menu()
   {
      if( !$this->page->get('ventas_articulos') )
      {
         if( file_exists(__DIR__) )
         {
            /// activamos las páginas del plugin
            foreach( scandir(__DIR__) as $f)
            {
               if( is_string($f) AND strlen($f) > 0 AND !is_dir($f) AND $f != __CLASS__.'.php' )
               {
                  $page_name = substr($f, 0, -4);
                  
                  require_once __DIR__.'/'.$f;
                  $new_fsc = new $page_name();
                  
                  if( !$new_fsc->page->save() )
                  {
                     $this->new_error_msg("Imposible guardar la página ".$page_name);
                  }
                  
                  unset($new_fsc);
               }
            }
         }
         else
         {
            $this->new_error_msg('No se encuentra el directorio '.__DIR__);
         }
         
         $this->load_menu(TRUE);
      }
   }
   public function get_timezone_list()
   {
      $zones_array = array();
      
      $timestamp = time();
      foreach(timezone_identifiers_list() as $key => $zone)
      {
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
}
