<?php



require_model('atributo_valor.php');

/**
 * Description of atributo
 *
 * @author carlos
 */
class atributo extends fs_model
{
   public $codatributo;
   public $nombre;
   
   public function __construct($a = FALSE)
   {
      parent::__construct('atributos');
      if($a)
      {
         $this->codatributo = $a['codatributo'];
         $this->nombre = $a['nombre'];
      }
      else
      {
         $this->codatributo = NULL;
         $this->nombre = NULL;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function url()
   {
      return 'index.php?page=ventas_atributos&cod='.$this->codatributo;
   }
   
   public function valores()
   {
      $valor0 = new atributo_valor();
      return $valor0->all_from_atributo($this->codatributo);
   }
   
   public function get($cod)
   {
      $data = $this->db->select("SELECT * FROM atributos WHERE codatributo = ".$this->var2str($cod).";");
      if($data)
      {
         return new atributo($data[0]);
      }
      else
      {
         return FALSE;
      }
   }
   
   public function exists()
   {
      if( is_null($this->codatributo) )
      {
         return FALSE;
      }
      else
      {
         return $this->db->select("SELECT * FROM atributos WHERE codatributo = ".$this->var2str($this->codatributo).";");
      }
   }
   
   public function save()
   {
      $this->nombre = $this->no_html($this->nombre);
      
      if( $this->exists() )
      {
         $sql = "UPDATE atributos SET nombre = ".$this->var2str($this->nombre)
                 ." WHERE codatributo = ".$this->var2str($this->codatributo).";";
      }
      else
      {
         $sql = "INSERT INTO atributos (codatributo,nombre) VALUES "
                 . "(".$this->var2str($this->codatributo)
                 . ",".$this->var2str($this->nombre).");";
      }
      
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM atributos WHERE codatributo = ".$this->var2str($this->codatributo).";");
   }
   
   public function all()
   {
      $lista = array();
      
      $data = $this->db->select("SELECT * FROM atributos ORDER BY nombre DESC;");
      if($data)
      {
         foreach($data as $d)
         {
            $lista[] = new atributo($d);
         }
      }
      
      return $lista;
   }
}
