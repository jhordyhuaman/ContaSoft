<?php

require_model('fabricante.php');

class ventas_fabricantes extends fs_controller
{
   public $fabricante;
   public $resultados;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Fabricantes', 'ventas', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->share_extensions();
      $this->fabricante = new fabricante();

      if( isset($_POST['ncodfabricante']) )
      {
         $fab = $this->fabricante->get($_POST['ncodfabricante']);
         if($fab)
         {
            $this->new_error_msg('El fabricante <a href="'.$fab->url().'">'.$fab->codfabricante.'</a> ya existe.');
         }
         else
         {
            $fab = new fabricante();
            $fab->codfabricante = $_POST['ncodfabricante'];
            $fab->nombre = $_POST['nnombre'];
            if( $fab->save() )
            {
               Header('location: ' . $fab->url());
            }
            else
               $this->new_error_msg("¡Imposible guardar el fabricante!");
         }
      }
      else if( isset($_GET['delete']) )
      {
         $fab = $this->fabricante->get($_GET['delete']);
         if($fab)
         {
            if( $fab->delete() )
            {
               $this->new_message("Fabricante ".$_GET['delete']." eliminado correctamente");
            }
            else
               $this->new_error_msg("¡Imposible eliminar el fabricante ".$_GET['delete']."!");
         }
         else
            $this->new_error_msg("Fabricante ".$_GET['delete']." no encontrado.");
      }
      
      $this->resultados = $this->fabricante->search($this->query);
   }
   
   public function total_fabricantes()
   {
      $data = $this->db->select("SELECT COUNT(codfabricante) as total FROM fabricantes;");
      if($data)
      {
         return intval($data[0]['total']);
      }
      else
         return 0;
   }
   
   private function share_extensions()
   {
      /// añadimos la extensión para ventas_artículos
      $fsext = new fs_extension();
      $fsext->name = 'btn_fabricantes';
      $fsext->from = __CLASS__;
      $fsext->to = 'ventas_articulos';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-folder-open" aria-hidden="true"></span><span class="hidden-xs"> &nbsp; Fabricantes</span>';
      $fsext->save();
   }
}
