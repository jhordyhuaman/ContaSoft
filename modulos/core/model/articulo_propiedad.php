<?php

class articulo_propiedad extends fs_model
{
   public $name;
   public $referencia;
   public $text;
   
   public function __construct($a = FALSE)
   {
      parent::__construct('articulo_propiedades');
      if($a)
      {
         $this->name = $a['name'];
         $this->referencia = $a['referencia'];
         $this->text = $a['text'];
      }
      else
      {
         $this->name = NULL;
         $this->referencia = NULL;
         $this->text = NULL;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function exists()
   {
      if( is_null($this->name) OR is_null($this->referencia) )
      {
         return FALSE;
      }
      else
      {
         return $this->db->select("SELECT * FROM articulo_propiedades WHERE name = ".
                 $this->var2str($this->name)." AND referencia = ".$this->var2str($this->referencia).";");
      }
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE articulo_propiedades SET text = ".$this->var2str($this->text)
                 ." WHERE name = ".$this->var2str($this->name)
                 ." AND referencia = ".$this->var2str($this->referencia).";";
      }
      else
      {
         $sql = "INSERT INTO articulo_propiedades (name,referencia,text) VALUES
                   (".$this->var2str($this->name)
                 .",".$this->var2str($this->referencia)
                 .",".$this->var2str($this->text).");";
      }
      
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM articulo_propiedades WHERE name = ".
                 $this->var2str($this->name)." AND referencia = ".$this->var2str($this->referencia).";");
   }
   
   
   public function array_get($ref)
   {
      $vlist = array();
      
      $data = $this->db->select("SELECT * FROM articulo_propiedades WHERE referencia = ".$this->var2str($ref).";");
      if($data)
      {
         foreach($data as $d)
            $vlist[ $d['name'] ] = $d['text'];
      }
      
      return $vlist;
   }
   
   public function array_save($ref, $values)
   {
      $done = TRUE;
      
      foreach($values as $key => $value)
      {
         $aux = new articulo_propiedad();
         $aux->name = $key;
         $aux->referencia = $ref;
         $aux->text = $value;
         if( !$aux->save() )
         {
            $done = FALSE;
            break;
         }
      }
      
      return $done;
   }
   
   public function simple_get($ref, $name)
   {
      $sql = "SELECT * FROM articulo_propiedades WHERE referencia = ".$this->var2str($ref)
              ." AND name = ".$this->var2str($name).";";
      $data = $this->db->select($sql);
      if($data)
      {
         return $data[0]['text'];
      }
      else
         return FALSE;
   }
   
   public function simple_get_ref($name, $text)
   {
      $sql = "SELECT * FROM articulo_propiedades WHERE text = ".$this->var2str($text)
              ." AND name = ".$this->var2str($name).";";
      $data = $this->db->select($sql);
      if($data)
      {
         return $data[0]['referencia'];
      }
      else
         return FALSE;
   }
   
   public function simple_delete($ref, $name)
   {
      return $this->db->exec("DELETE FROM articulo_propiedades WHERE referencia = ".$this->var2str($ref)
              ." AND name = ".$this->var2str($name).";");
   }
}
