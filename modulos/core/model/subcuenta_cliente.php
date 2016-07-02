<?php

class subcuenta_cliente extends fs_model
{
   public $codcliente;
   public $codsubcuenta;
   public $codejercicio;
   public $idsubcuenta;
   public $id; /// pkey
   
   public function __construct($s = FALSE)
   {
      parent::__construct('co_subcuentascli');
      if($s)
      {
         $this->codcliente = $s['codcliente'];
         $this->codsubcuenta = $s['codsubcuenta'];
         $this->codejercicio = $s['codejercicio'];
         $this->idsubcuenta = $this->intval($s['idsubcuenta']);
         $this->id = $this->intval($s['id']);
      }
      else
      {
         $this->codcliente = NULL;
         $this->codsubcuenta = NULL;
         $this->codejercicio = NULL;
         $this->idsubcuenta = NULL;
         $this->id = NULL;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function get_subcuenta()
   {
      $subc = new subcuenta();
      return $subc->get($this->idsubcuenta);
   }
   
   public function get($cli, $idsc)
   {
      $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codcliente = ".$this->var2str($cli)."
         AND idsubcuenta = ".$this->var2str($idsc).";");
      if($data)
         return new subcuenta_cliente($data[0]);
      else
         return FALSE;
   }
   
   public function get2($id)
   {
      $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE id = ".$this->var2str($id).";");
      if($data)
         return new subcuenta_cliente($data[0]);
      else
         return FALSE;
   }
   
   public function exists()
   {
      if( is_null($this->id) )
      {
         return FALSE;
      }
      else
         return $this->db->select("SELECT * FROM ".$this->table_name." WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function test()
   {
      return TRUE;
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE ".$this->table_name." SET codcliente = ".$this->var2str($this->codcliente).",
            codsubcuenta = ".$this->var2str($this->codsubcuenta).",
            codejercicio = ".$this->var2str($this->codejercicio).",
            idsubcuenta = ".$this->var2str($this->idsubcuenta)."
            WHERE id = ".$this->var2str($this->id).";";
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (codcliente,codsubcuenta,codejercicio,idsubcuenta)
            VALUES (".$this->var2str($this->codcliente).",".$this->var2str($this->codsubcuenta).",
            ".$this->var2str($this->codejercicio).",".$this->var2str($this->idsubcuenta).");";
         $resultado = $this->db->exec($sql);
         if($resultado)
         {
            $newid = $this->db->lastval();
            if($newid)
               $this->id = intval($newid);
         }
         return $resultado;
      }
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function all_from_cliente($cod)
   {
      $sublist = array();
      $subcs = $this->db->select("SELECT * FROM ".$this->table_name.
         " WHERE codcliente = ".$this->var2str($cod)." ORDER BY codejercicio DESC;");
      if($subcs)
      {
         foreach($subcs as $s)
            $sublist[] = new subcuenta_cliente($s);
      }
      return $sublist;
   }
}
