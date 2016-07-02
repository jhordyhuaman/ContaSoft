<?php

class fs_var extends fs_model
{
   public $name;
   public $varchar;
   
   public function __construct($f=FALSE)
   {
      parent::__construct('fs_vars');
      if($f)
      {
         $this->name = $f['name'];
         $this->varchar = $f['varchar'];
      }
      else
      {
         $this->name = NULL;
         $this->varchar = NULL;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function exists()
   {
      if( is_null($this->name) )
      {
         return FALSE;
      }
      else
      {
         return $this->db->select("SELECT * FROM ".$this->table_name." WHERE name = ".$this->var2str($this->name).";");
      }
   }
   
   public function save()
   {
      $comillas = '';
      if( strtolower(FS_DB_TYPE) == 'mysql' )
      {
         $comillas = '`';
      }
      
      if( $this->exists() )
      {
         $sql = "UPDATE ".$this->table_name." SET "
                 .$comillas."varchar".$comillas." = ".$this->var2str($this->varchar)
                 ." WHERE name = ".$this->var2str($this->name).";";
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (name,".$comillas."varchar".$comillas.")
            VALUES (".$this->var2str($this->name)
                 .",".$this->var2str($this->varchar).");";
      }
      
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE name = ".$this->var2str($this->name).";");
   }
   
   public function all()
   {
      $vlist = array();
      
      $vars = $this->db->select("SELECT * FROM ".$this->table_name.";");
      if($vars)
      {
         foreach($vars as $v)
         {
            $vlist[] = new fs_var($v);
         }
      }
      
      return $vlist;
   }
  
   public function simple_get($name)
   {
      $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE name = ".$this->var2str($name).";");
      if($data)
      {
         return $data[0]['varchar'];
      }
      else
         return FALSE;
   }
 
   public function simple_save($name, $value)
   {
      $comillas = '';
      if( strtolower(FS_DB_TYPE) == 'mysql' )
      {
         $comillas = '`';
      }
      
      if( $this->db->select("SELECT * FROM ".$this->table_name." WHERE name = ".$this->var2str($name).";") )
      {
         $sql = "UPDATE ".$this->table_name." SET ".$comillas."varchar".$comillas." = ".$this->var2str($value).
                 " WHERE name = ".$this->var2str($name).";";
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (name,".$comillas."varchar".$comillas.") VALUES
            (".$this->var2str($name).",".$this->var2str($value).");";
      }
      
      return $this->db->exec($sql);
   }
   
   public function simple_delete($name)
   {
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE name = ".$this->var2str($name).";");
   }
 
   public function array_get($array, $replace=TRUE)
   {
      /// obtenemos todos los resultados y seleccionamos los que necesitamos
      $data = $this->db->select("SELECT * FROM ".$this->table_name.";");
      if($data)
      {
         foreach($array as $i => $value)
         {
            $encontrado = FALSE;
            foreach($data as $d)
            {
               if($d['name'] == $i)
               {
                  $array[$i] = $d['varchar'];
                  $encontrado = TRUE;
                  break;
               }
            }
            
            if($replace AND !$encontrado)
            {
               $array[$i] = FALSE;
            }
         }
      }
      
      return $array;
   }
  
   public function array_save($array)
   {
      $done = TRUE;
      
      foreach($array as $i => $value)
      {
         if($value === FALSE)
         {
            if( !$this->simple_delete($i) )
            {
               $done = FALSE;
            }
         }
         else
         {
            if( !$this->simple_save($i, $value) )
            {
               $done = FALSE;
            }
         }
      }
      
      return $done;
   }
}
