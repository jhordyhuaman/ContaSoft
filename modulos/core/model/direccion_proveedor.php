<?php

class direccion_proveedor extends fs_model
{
   /**
    * Clave primaria.
    * @var type 
    */
   public $id;
   
   /**
    * Código del proveedor asociado.
    * @var type 
    */
   public $codproveedor;
   
   public $codpais;
   public $apartado;
   public $provincia;
   public $ciudad;
   public $codpostal;
   public $direccion;
   public $direccionppal;
   public $descripcion;
   
   /**
    * Fecha de la última modificación.
    * @var type 
    */
   public $fecha;
   
   public function __construct($d=FALSE)
   {
      parent::__construct('dirproveedores');
      if($d)
      {
         $this->id = $this->intval($d['id']);
         $this->codproveedor = $d['codproveedor'];
         $this->codpais = $d['codpais'];
         $this->apartado = $d['apartado'];
         $this->provincia = $d['provincia'];
         $this->ciudad = $d['ciudad'];
         $this->codpostal = $d['codpostal'];
         $this->direccion = $d['direccion'];
         $this->direccionppal = $this->str2bool($d['direccionppal']);
         $this->descripcion = $d['descripcion'];
         $this->fecha = date('d-m-Y', strtotime($d['fecha']));
      }
      else
      {
         $this->id = NULL;
         $this->codproveedor = NULL;
         $this->codpais = NULL;
         $this->apartado = NULL;
         $this->provincia = NULL;
         $this->ciudad = NULL;
         $this->codpostal = NULL;
         $this->direccion = NULL;
         $this->direccionppal = TRUE;
         $this->descripcion = NULL;
         $this->fecha = date('d-m-Y');
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function get($id)
   {
      $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE id = ".$this->var2str($id).";");
      if($data)
      {
         return new direccion_proveedor($data[0]);
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
         return $this->db->select("SELECT * FROM ".$this->table_name." WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function save()
   {
      $this->apartado = $this->no_html($this->apartado);
      $this->ciudad = $this->no_html($this->ciudad);
      $this->codpostal = $this->no_html($this->codpostal);
      $this->descripcion = $this->no_html($this->descripcion);
      $this->direccion = $this->no_html($this->direccion);
      $this->provincia = $this->no_html($this->provincia);
      
      /// actualizamos la fecha de modificación
      $this->fecha = date('d-m-Y');
      
      if( $this->exists() )
      {
         $sql = "UPDATE ".$this->table_name." SET codproveedor = ".$this->var2str($this->codproveedor)
                 .", codpais = ".$this->var2str($this->codpais)
                 .", apartado = ".$this->var2str($this->apartado)
                 .", provincia = ".$this->var2str($this->provincia)
                 .", ciudad = ".$this->var2str($this->ciudad)
                 .", codpostal = ".$this->var2str($this->codpostal)
                 .", direccion = ".$this->var2str($this->direccion)
                 .", direccionppal = ".$this->var2str($this->direccionppal)
                 .", descripcion = ".$this->var2str($this->descripcion)
                 .", fecha = ".$this->var2str($this->fecha)
                 ."  WHERE id = ".$this->var2str($this->id).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (codproveedor,codpais,apartado,provincia,ciudad,
            codpostal,direccion,direccionppal,descripcion,fecha) VALUES (".$this->var2str($this->codproveedor)
                 .",".$this->var2str($this->codpais)
                 .",".$this->var2str($this->apartado)
                 .",".$this->var2str($this->provincia)
                 .",".$this->var2str($this->ciudad)
                 .",".$this->var2str($this->codpostal)
                 .",".$this->var2str($this->direccion)
                 .",".$this->var2str($this->direccionppal)
                 .",".$this->var2str($this->descripcion)
                 .",".$this->var2str($this->fecha).");";
         
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
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function all_from_proveedor($codprov)
   {
      $dirlist = array();
      
      $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codproveedor = ".$this->var2str($codprov).";");
      if($data)
      {
         foreach($data as $d)
         {
            $dirlist[] = new direccion_proveedor($d);
         }
      }
      
      return $dirlist;
   }
}
