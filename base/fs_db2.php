<?php

if(strtolower(FS_DB_TYPE) == 'mysql')
{
   require_once 'base/fs_mysql.php';
}

class fs_db2
{
   private $engine;
   
   public function __construct()
   {
      if(strtolower(FS_DB_TYPE) == 'mysql')
      {
         $this->engine = new fs_mysql();
      }

   }
   
   public function connect()
   {
      return $this->engine->connect();
   }
   
   public function connected()
   {
      return $this->engine->connected();
   }
   
   public function close()
   {
      return $this->engine->close();
   }
   
   public function version()
   {
      return $this->engine->version();
   }
   
   public function get_errors()
   {
      return $this->engine->get_errors();
   }
   
   public function get_selects()
   {
      return $this->engine->get_selects();
   }
   
   public function get_transactions()
   {
      return $this->engine->get_transactions();
   }
  
   public function get_history()
   {
      return $this->engine->get_history();
   }
   
   public function list_tables()
   {
      return $this->engine->list_tables();
   }
   
   public function table_exists($name, $list = FALSE)
   {
      $resultado = FALSE;
      
      if($list === FALSE)
      {
         $list = $this->engine->list_tables();
      }
      
      foreach($list as $tabla)
      {
         if($tabla['name'] == $name)
         {
            $resultado = TRUE;
            break;
         }
      }
      
      return $resultado;
   }
   

   public function select($sql)
   {
      return $this->engine->select($sql);
   }

   public function select_limit($sql, $limit, $offset)
   {
      return $this->engine->select_limit($sql, $limit, $offset);
   }
   

   public function exec($sql, $transaccion = TRUE)
   {
      return $this->engine->exec($sql, $transaccion);
   }
   
   public function lastval()
   {
      return $this->engine->lastval();
   }
   
   public function begin_transaction()
   {
      return $this->engine->begin_transaction();
   }
   
   public function commit()
   {
      return $this->engine->commit();
   }
   
   public function rollback()
   {
      return $this->engine->rollback();
   }
   
   public function escape_string($s)
   {
      return $this->engine->escape_string($s);
   }
   
   public function date_style()
   {
      return $this->engine->date_style();
   }
   
   public function sql_to_int($col)
   {
      return $this->engine->sql_to_int($col);
   }

   public function get_columns($table)
   {
      return $this->engine->get_columns($table);
   }

   public function get_constraints($table)
   {
      return $this->engine->get_constraints($table);
   }
 
   public function get_indexes($table)
   {
      return $this->engine->get_indexes($table);
   }

   public function get_locks()
   {
      return $this->engine->get_locks();
   }

   public function compare_columns($table_name, $xml_cols, $columnas)
   {
      return $this->engine->compare_columns($table_name, $xml_cols, $columnas);
   }
 
   public function compare_constraints($table_name, $c_nuevas, $c_old, $solo_eliminar = FALSE)
   {
      return $this->engine->compare_constraints($table_name, $c_nuevas, $c_old, $solo_eliminar);
   }
 
   public function generate_table($table_name, $xml_columnas, $xml_restricciones)
   {
      return $this->engine->generate_table($table_name, $xml_columnas, $xml_restricciones);
   }
   public function check_table_aux($table_name)
   {
      return $this->engine->check_table_aux($table_name);
   }
}
