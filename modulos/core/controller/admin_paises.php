<?php


require_model('pais.php');

class admin_paises extends fs_controller
{
   public $allow_delete;
   public $pais;

   public function __construct()
   {
      parent::__construct(__CLASS__, 'Paises', 'admin', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      $this->pais = new pais();
      
      /// ¿El usuario tiene permiso para eliminar en esta página?
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      
      if( isset($_POST['scodpais']) )
      {
         $pais = $this->pais->get($_POST['scodpais']);
         if( !$pais )
         {
            $pais = new pais();
            $pais->codpais = $_POST['scodpais'];
         }
         
         $pais->codiso = $_POST['scodiso'];
         $pais->nombre = $_POST['snombre'];
         
         if( $pais->save() )
         {
            $this->new_message("País ".$pais->nombre." guardado correctamente.");
         }
         else
            $this->new_error_msg("¡Imposible guardar el país!");
      }
      else if( isset($_GET['delete']) )
      {
      }
   }
}
