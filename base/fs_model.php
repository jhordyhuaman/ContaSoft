<?php

require_once 'base/bround.php';
require_once 'base/fs_cache.php';
require_once 'base/fs_db2.php';
require_once 'base/fs_default_items.php';

function require_model($name)
{
   if( !isset($GLOBALS['models']) )
   {
      $GLOBALS['models'] = array();
   }
   
   if( !in_array($name, $GLOBALS['models']) )
   {
      /// primero buscamos en los modulos
      $found = FALSE;
      foreach($GLOBALS['modulos'] as $plugin)
      {
         if( file_exists('modulos/'.$plugin.'/model/'.$name) )
         {
            require_once 'modulos/'.$plugin.'/model/'.$name;
            $GLOBALS['models'][] = $name;
            $found = TRUE;
            break;
         }
      }
      
      if( !$found )
      {
         if( file_exists('model/'.$name) )
         {
            require_once 'model/'.$name;
            $GLOBALS['models'][] = $name;
         }
      }
   }
}

function get_class_name($object = NULL)
{
   $name = get_class($object);
   
   $pos = strrpos($name, '\\');
   if($pos !== FALSE)
   {
      $name = substr($name, $pos + 1);
   }
   
   return $name;
}


abstract class fs_model
{
  
   protected $db;
   
   protected $table_name;
 
   protected $base_dir;
   
   protected $cache;
 
   protected $default_items;
   
   private static $checked_tables;
   private static $errors;
 
   public function __construct($name = '')
   {
      $this->cache = new fs_cache();
      $this->db = new fs_db2();
      $this->table_name = $name;
      
      /// buscamos el xml de la tabla en los modulos
      $this->base_dir = '';
      foreach($GLOBALS['modulos'] as $plugin)
      {
         if( file_exists('modulos/'.$plugin.'/model/table/'.$name.'.xml') )
         {
            $this->base_dir = 'modulos/'.$plugin.'/';
            break;
         }
      }
      
      $this->default_items = new fs_default_items();
      
      if( !self::$errors )
      {
         self::$errors = array();
      }
      
      if( !self::$checked_tables )
      {
         self::$checked_tables = $this->cache->get_array('fs_checked_tables');
         if(self::$checked_tables)
         {
            /// nos aseguramos de que existan todas las tablas 
            $tables = $this->db->list_tables();
            foreach(self::$checked_tables as $ct)
            {
               if( !$this->db->table_exists($ct, $tables) )
               {
                  $this->clean_checked_tables();
                  break;
               }
            }
         }
      }
      
      if($name != '')
      {
         if( !in_array($name, self::$checked_tables) )
         {
            if( $this->check_table($name) )
            {
               self::$checked_tables[] = $name;
               $this->cache->set('fs_checked_tables', self::$checked_tables, 5400);
            }
         }
      }
   }
 
   protected function clean_checked_tables()
   {
      self::$checked_tables = array();
      $this->cache->delete('fs_checked_tables');
   }
  
   protected function new_error_msg($msg = FALSE)
   {
      if($msg)
      {
         self::$errors[] = $msg;
      }
   }
   
   public function get_errors()
   {
      return self::$errors;
   }

   public function clean_errors()
   {
      self::$errors = array();
   }

   abstract protected function install();
   

   abstract public function exists();
  
   abstract public function save();
  
   abstract public function delete();
 
   protected function escape_string($s = '')
   {
      return $this->db->escape_string($s);
   }

   public function var2str($v)
   {
      if( is_null($v) )
      {
         return 'NULL';
      }
      else if( is_bool($v) )
      {
         if($v)
         {
            return 'TRUE';
         }
         else
            return 'FALSE';
      }
      else if( preg_match('/^([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})$/i', $v) ) /// es una fecha
      {
         return "'".Date($this->db->date_style(), strtotime($v))."'";
      }
      else if( preg_match('/^([0-9]{1,2})-([0-9]{1,2})-([0-9]{4}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})$/i', $v) ) /// es una fecha+hora
      {
         return "'".Date($this->db->date_style().' H:i:s', strtotime($v))."'";
      }
      else
         return "'" . $this->db->escape_string($v) . "'";
   }

   protected function bin2str($v)
   {
      if( is_null($v) )
      {
         return 'NULL';
      }
      else
         return "'".base64_encode($v)."'";
   }

   protected function str2bin($v)
   {
      if( is_null($v) )
      {
         return NULL;
      }
      else
         return base64_decode($v);
   }
   

   public function str2bool($v)
   {
      return ($v == 't' OR $v == '1');
   }

   public function intval($s)
   {
      if( is_null($s) )
      {
         return NULL;
      }
      else
         return intval($s);
   }

   public function floatcmp($f1, $f2, $precision = 10, $round = FALSE)
   {
      if( $round OR !function_exists('bccomp') )
      {
         return( abs($f1-$f2) < 6/pow(10,$precision+1) );
      }
      else
         return( bccomp( (string)$f1, (string)$f2, $precision ) == 0 );
   }
   
   protected function date_range($first, $last, $step = '+1 day', $format = 'd-m-Y' )
   {
      $dates = array();
      $current = strtotime($first);
      $last = strtotime($last);
      
      while( $current <= $last )
      {
         $dates[] = date($format, $current);
         $current = strtotime($step, $current);
      }
      
      return $dates;
   }
   
 
   public function no_html($t)
   {
      $newt = str_replace(
              array('<','>','"',"'"),
              array('&lt;','&gt;','&quot;','&#39;'),
              $t
      );
      
      return trim($newt);
   }
   
   protected function random_string($length = 10)
   {
      return mb_substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
   }
  
   protected function check_table($table_name)
   {
      $done = TRUE;
      $consulta = '';
      $xml_columnas = array();
      $xml_restricciones = array();
      
      if( $this->get_xml_table($table_name, $xml_columnas, $xml_restricciones) )
      {
         if( $this->db->table_exists($table_name) )
         {
            if( !$this->db->check_table_aux($table_name) )
            {
               $this->new_error_msg('Error al convertir la tabla a InnoDB.');
            }
            
            /// eliminamos restricciones
            $restricciones = $this->db->get_constraints($table_name);
            $consulta2 = $this->db->compare_constraints($table_name, $xml_restricciones, $restricciones, TRUE);
            if($consulta2 != '')
            {
               if( !$this->db->exec($consulta2) )
               {
                  $this->new_error_msg('Error al comprobar la tabla '.$table_name);
               }
            }
            
            /// comparamos las columnas
            $columnas = $this->db->get_columns($table_name);
            $consulta .= $this->db->compare_columns($table_name, $xml_columnas, $columnas);
            
            /// comparamos las restricciones
            $restricciones = $this->db->get_constraints($table_name);
            $consulta .= $this->db->compare_constraints($table_name, $xml_restricciones, $restricciones);
         }
         else
         {
            /// generamos el sql para crear la tabla
            $consulta .= $this->db->generate_table($table_name, $xml_columnas, $xml_restricciones);
            $consulta .= $this->install();
         }
         
         if($consulta != '')
         {
            if( !$this->db->exec($consulta) )
            {
               $this->new_error_msg('Error al comprobar la tabla '.$table_name);
               $done = FALSE;
            }
         }
      }
      else
      {
         $this->new_error_msg('Error con el xml.');
         $done = FALSE;
      }
      
      return $done;
   }
 
   protected function get_xml_table($table_name, &$columnas, &$restricciones)
   {
      $retorno = FALSE;
      $filename = $this->base_dir.'model/table/'.$table_name.'.xml';
      
      if( file_exists($filename) )
      {
         $xml = simplexml_load_string( file_get_contents('./'.$filename, FILE_USE_INCLUDE_PATH) );
         if($xml)
         {
            if($xml->columna)
            {
               $i = 0;
               foreach($xml->columna as $col)
               {
                  $columnas[$i]['nombre'] = $col->nombre;
                  $columnas[$i]['tipo'] = $col->tipo;
                  
                  $columnas[$i]['nulo'] = 'YES';
                  if($col->nulo)
                  {
                     if( strtolower($col->nulo) == 'no')
                     {
                        $columnas[$i]['nulo'] = 'NO';
                     }
                  }
                  
                  if($col->defecto == '')
                  {
                     $columnas[$i]['defecto'] = NULL;
                  }
                  else
                     $columnas[$i]['defecto'] = $col->defecto;
                  
                  $i++;
               }
               
               /// debe de haber columnas, sino es un fallo
               $retorno = TRUE;
            }
            
            if($xml->restriccion)
            {
               $i = 0;
               foreach($xml->restriccion as $col)
               {
                  $restricciones[$i]['nombre'] = $col->nombre;
                  $restricciones[$i]['consulta'] = $col->consulta;
                  $i++;
               }
            }
         }
         else
            $this->new_error_msg('Error al leer el archivo '.$filename);
      }
      else
         $this->new_error_msg('Archivo '.$filename.' no encontrado.');
      
      return $retorno;
   }
}
