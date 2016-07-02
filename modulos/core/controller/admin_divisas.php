<?php


require_model('divisa.php');

class admin_divisas extends fs_controller
{
   public $allow_delete;
   public $divisa;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Divisas', 'admin', TRUE, TRUE);
   }
   
   protected function private_core()
   {
      $this->divisa = new divisa();
      
      /// ¿El usuario tiene permiso para eliminar en esta página?
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      
      if( isset($_POST['coddivisa']) )
      {
         $div0 = $this->divisa->get($_POST['coddivisa']);
         if(!$div0)
         {
            $div0 = new divisa();
            $div0->coddivisa = $_POST['coddivisa'];
         }
         $div0->simbolo = $_POST['simbolo'];
         $div0->descripcion = $_POST['descripcion'];
         $div0->codiso = $_POST['codiso'];
         $div0->tasaconv = floatval($_POST['tasaconv']);
         $div0->tasaconv_compra = floatval($_POST['tasaconv_compra']);
         if( $div0->save() )
         {
            $this->new_message('Divisa '.$div0->coddivisa.' guardada correctamente.');
         }
         else
            $this->new_error_msg('Error al guardar la divisa.');
      }
      else if( isset($_GET['delete']) )
      {
         $div0 = $this->divisa->get($_GET['delete']);
         if($div0)
         {
            if( !$this->user->admin )
            {
               $this->new_error_msg('Sólo un administrador puede eliminar divisas.');
            }
            else if( $div0->delete() )
            {
               $this->new_message('Divisa '.$div0->coddivisa.' eliminada correctamente.');
            }
            else
               $this->new_error_msg('Error al eliminar la divisa '.$div0->coddivisa.'.');
         }
         else
            $this->new_error_msg('Divisa no encontrada.');
      }
   }
}
