<?php

class articulo_proveedor extends fs_model
{
   
   public $id;
   public $referencia;
   public $codproveedor;
   public $refproveedor;
   public $descripcion;
   public $precio;
   public $dto;
   public $codimpuesto;
   public $stock;
   public $nostock;
   private $iva;
   public $codbarras;
   public $partnumber;
   private static $impuestos;
   private static $nombres;
   
   public function __construct($a = FALSE)
   {
      parent::__construct('articulosprov');
      
      if( !isset(self::$impuestos) )
      {
         self::$impuestos = array();
      }
      
      if( !isset(self::$nombres) )
      {
         self::$nombres = array();
      }
      
      if($a)
      {
         $this->id = $this->intval($a['id']);
         $this->referencia = $a['referencia'];
         $this->codproveedor = $a['codproveedor'];
         $this->refproveedor = $a['refproveedor'];
         $this->descripcion = $a['descripcion'];
         
         /// En algunos módulos de eneboo se usa coste como precio
         if( is_null($a['precio']) AND isset($a['coste']) )
         {
            $this->precio = floatval($a['coste']);
         }
         else
            $this->precio = floatval($a['precio']);
         
         $this->dto = floatval($a['dto']);
         $this->codimpuesto = $a['codimpuesto'];
         $this->stock = floatval($a['stock']);
         $this->nostock = $this->str2bool($a['nostock']);
         $this->codbarras = $a['codbarras'];
         $this->partnumber = $a['partnumber'];
      }
      else
      {
         $this->id = NULL;
         $this->referencia = NULL;
         $this->codproveedor = NULL;
         $this->refproveedor = NULL;
         $this->descripcion = NULL;
         $this->precio = 0;
         $this->dto = 0;
         $this->codimpuesto = NULL;
         $this->stock = 0;
         $this->nostock = TRUE;
         $this->codbarras = NULL;
         $this->partnumber = NULL;
      }
      
      $this->iva = NULL;
   }
   
   protected function install()
   {
      return '';
   }
   
   public function nombre_proveedor()
   {
      if( isset(self::$nombres[$this->codproveedor]) )
      {
         return self::$nombres[$this->codproveedor];
      }
      else
      {
         $data = $this->db->select("SELECT nombre FROM proveedores WHERE codproveedor = ".$this->var2str($this->codproveedor).";");
         if($data)
         {
            self::$nombres[$this->codproveedor] = $data[0]['nombre'];
            return $data[0]['nombre'];
         }
         else
            return '-';
      }
   }
   
   public function url_proveedor()
   {
      return 'index.php?page=compras_proveedor&cod='.$this->codproveedor;
   }
   
   public function get_iva($reload = TRUE)
   {
      if($reload)
      {
         $this->iva = NULL;
      }
      
      if( is_null($this->iva) )
      {
         $this->iva = 0;
         
         if( !is_null($this->codimpuesto) )
         {
            $encontrado = FALSE;
            foreach(self::$impuestos as $i)
            {
               if($i->codimpuesto == $this->codimpuesto)
               {
                  $this->iva = $i->iva;
                  $encontrado = TRUE;
                  break;
               }
            }
            if(!$encontrado)
            {
               $imp = new impuesto();
               $imp0 = $imp->get($this->codimpuesto);
               if($imp0)
               {
                  $this->iva = $imp0->iva;
                  self::$impuestos[] = $imp0;
               }
            }
         }
      }
      
      return $this->iva;
   }
  
   public function total_iva()
   {
      return $this->precio * (100-$this->dto) / 100 * (100+$this->get_iva()) / 100;
   }
   public function get($id)
   {
      $data = $this->db->select("SELECT * FROM articulosprov WHERE id = ".$this->var2str($id).";");
      if($data)
      {
         return new articulo_proveedor($data[0]);
      }
      else
         return FALSE;
   }
   
   public function get_by($ref, $codproveedor, $refprov = FALSE)
   {
      if($refprov)
      {
         $sql = "SELECT * FROM articulosprov WHERE codproveedor = ".$this->var2str($codproveedor)
                 ." AND (refproveedor = ".$this->var2str($refprov)
                 ." OR referencia = ".$this->var2str($ref).");";
      }
      else
      {
         $sql = "SELECT * FROM articulosprov WHERE referencia = ".$this->var2str($ref)
                 ." AND codproveedor = ".$this->var2str($codproveedor).";";
      }
      
      $data = $this->db->select($sql);
      if($data)
      {
         return new articulo_proveedor($data[0]);
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
         return $this->db->select("SELECT * FROM articulosprov WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function save()
   {
      $this->descripcion = $this->no_html($this->descripcion);
      
      if($this->nostock)
      {
         $this->stock = 0;
      }
      
      if( is_null($this->refproveedor) OR strlen($this->refproveedor) < 1 OR strlen($this->refproveedor) > 25 )
      {
         $this->new_error_msg('La referencia de proveedor debe contener entre 1 y 25 caracteres.');
      }
      else if( $this->exists() )
      {
         $sql = "UPDATE articulosprov SET referencia = ".$this->var2str($this->referencia).
                 ", codproveedor = ".$this->var2str($this->codproveedor).
                 ", refproveedor = ".$this->var2str($this->refproveedor).
                 ", descripcion = ".$this->var2str($this->descripcion).
                 ", precio = ".$this->var2str($this->precio).
                 ", dto = ".$this->var2str($this->dto).
                 ", codimpuesto = ".$this->var2str($this->codimpuesto).
                 ", stock = ".$this->var2str($this->stock).
                 ", nostock = ".$this->var2str($this->nostock).
                 ", codbarras = ".$this->var2str($this->codbarras).
                 ", partnumber = ".$this->var2str($this->partnumber).
                 " WHERE id = ".$this->var2str($this->id).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO articulosprov (referencia,codproveedor,refproveedor,descripcion,".
                 "precio,dto,codimpuesto,stock,nostock,codbarras,partnumber) VALUES ".
                 "(".$this->var2str($this->referencia).
                 ",".$this->var2str($this->codproveedor).
                 ",".$this->var2str($this->refproveedor).
                 ",".$this->var2str($this->descripcion).
                 ",".$this->var2str($this->precio).
                 ",".$this->var2str($this->dto).
                 ",".$this->var2str($this->codimpuesto).
                 ",".$this->var2str($this->stock).
                 ",".$this->var2str($this->nostock).
                 ",".$this->var2str($this->codbarras).
                 ",".$this->var2str($this->partnumber).");";
         
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
      return $this->db->exec("DELETE FROM articulosprov WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function all_from_ref($ref)
   {
      $alist = array();
      $sql = "SELECT * FROM articulosprov WHERE referencia = ".$this->var2str($ref)." ORDER BY precio ASC;";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $alist[] = new articulo_proveedor($d);
         }
      }
      
      return $alist;
   }
   
   public function mejor_from_ref($ref)
   {
      $sql = "SELECT * FROM articulosprov WHERE referencia = ".$this->var2str($ref)
              ." ORDER BY precio ASC;";
      
      $data = $this->db->select($sql);
      if($data)
      {
         return new articulo_proveedor($data[0]);
      }
      else
         return FALSE;
   }
   
   public function all_con_ref()
   {
      $alist = array();
      $sql = "SELECT * FROM articulosprov WHERE referencia !='' ORDER BY precio ASC;";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $alist[] = new articulo_proveedor($d);
         }
      }
      
      return $alist;
   }
}
