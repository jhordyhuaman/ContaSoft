<?php

class fs_log extends fs_model
{
  
   public $id;
   public $tipo;
   public $detalle;
   public $fecha;
   public $usuario;
   public $ip;
   public $alerta;

   public function __construct($l = FALSE)
   {
      parent::__construct('fs_logs');
      if($l)
      {
         $this->id = $l['id'];
         $this->tipo = $l['tipo'];
         $this->detalle = $l['detalle'];
         $this->fecha = date('d-m-Y H:i:s', strtotime($l['fecha']));
         $this->usuario = $l['usuario'];
         $this->ip = $l['ip'];
         $this->alerta = $this->str2bool($l['alerta']);
      }
      else
      {
         $this->id = NULL;
         $this->tipo = NULL;
         $this->detalle = NULL;
         $this->fecha = date('d-m-Y H:i:s');
         $this->usuario = NULL;
         $this->ip = NULL;
         $this->alerta = FALSE;
      }
   }

   protected function install()
   {
      return '';
   }

   public function get($id)
   {
      $data = $this->db->select("SELECT * FROM fs_logs WHERE id =" . $this->var2str($id) . ";");
      if($data)
      {
         return new fs_log($data[0]);
      }
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
         return $this->db->select("SELECT * FROM fs_logs WHERE id =" . $this->var2str($this->id) . ";");
   }

   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE fs_logs SET fecha = " . $this->var2str($this->fecha)
                 . ", tipo = " . $this->var2str($this->tipo)
                 . ", detalle = " . $this->var2str($this->detalle)
                 . ", usuario = " . $this->var2str($this->usuario)
                 . ", ip = " . $this->var2str($this->ip)
                 . ", alerta = " . $this->var2str($this->alerta)
                 . "  WHERE id=" . $this->var2str($this->id) . ";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO fs_logs (fecha,tipo,detalle,usuario,ip,alerta) "
                 . "VALUES (" . $this->var2str($this->fecha) . ","
                 . $this->var2str($this->tipo) . ","
                 . $this->var2str($this->detalle) . ","
                 . $this->var2str($this->usuario) . ","
                 . $this->var2str($this->ip) . ","
                 . $this->var2str($this->alerta) . ");";

         if( $this->db->exec($sql) )
         {
            $this->id = $this->db->lastval();
            return TRUE;
         }
         else
            return FALSE;
      }
   }

   public function delete()
   {
      return $this->db->exec("DELETE FROM fs_logs WHERE id =" . $this->var2str($this->id) . ";");
   }
   
   public function all($offset=0, $limit = FS_ITEM_LIMIT)
   {
      $lista = array();
      
      $data = $this->db->select_limit("SELECT * FROM fs_logs ORDER BY fecha DESC", $limit, $offset);
      if($data)
      {
         foreach($data as $d)
            $lista[] = new fs_log($d);
      }

      return $lista;
   }
   
   public function all_from($usuario)
   {
      $lista = array();

      $data = $this->db->select_limit("SELECT * FROM fs_logs WHERE usuario = ".$this->var2str($usuario)." ORDER BY fecha DESC", FS_ITEM_LIMIT, 0);
      if($data)
      {
         foreach($data as $d)
            $lista[] = new fs_log($d);
      }

      return $lista;
   }
   
   public function all_by($tipo)
   {
      $lista = array();

      $data = $this->db->select_limit("SELECT * FROM fs_logs WHERE tipo = ".$this->var2str($tipo)." ORDER BY fecha DESC", FS_ITEM_LIMIT, 0);
      if($data)
      {
         foreach($data as $d)
            $lista[] = new fs_log($d);
      }

      return $lista;
   }
}
