<?php


require_model('cuenta.php');
require_model('ejercicio.php');

class contabilidad_cuentas extends fs_controller
{
   public $cuenta;
   public $ejercicio;
   public $resultados;
   public $resultados2;
   public $offset;

   public function __construct()
   {
      parent::__construct(__CLASS__, 'Cuentas', 'contabilidad', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      $this->cuenta = new cuenta();
      $this->ejercicio = new ejercicio();
      
      $this->offset = 0;
      if( isset($_GET['offset']) )
      {
         $this->offset = intval($_GET['offset']);
      }
      
      if( isset($_GET['delete']) )
      {
         $cuenta2 = $this->cuenta->get($_GET['delete']);
         if($cuenta2)
         {
            if( $cuenta2->delete() )
            {
               $this->new_message('Cuenta eliminada correctamente.');
            }
            else
               $this->new_error_msg('Error al eliminar la cuenta.');
         }
         else
            $this->new_error_msg('Cuenta no encontrada.');
         
         $this->resultados = $this->cuenta->all_from_ejercicio($this->default_items->codejercicio(), $this->offset);
         $this->resultados2 = array();
      }
      else if( isset($_POST['codejercicio']) )
      {
         $this->nueva_cuenta();
      }
      else if($this->query != '')
      {
         $this->resultados = $this->cuenta->search($this->query);
         $subc = new subcuenta();
         $this->resultados2 = $subc->search($this->query);
      }
      else if( isset($_POST['ejercicio']) )
      {
         $this->resultados = $this->cuenta->all_from_ejercicio($_POST['ejercicio'], $this->offset);
         $this->resultados2 = array();
      }
      else
      {
         $this->resultados = $this->cuenta->all_from_ejercicio($this->default_items->codejercicio(), $this->offset);
         $this->resultados2 = array();
      }
   }
   
   public function anterior_url()
   {
      $url = '';
      if($this->query!='' AND $this->offset>0)
      {
         $url = $this->url()."&query=".$this->query."&offset=".($this->offset-FS_ITEM_LIMIT);
      }
      else if($this->query=='' AND $this->offset>0)
      {
         $url = $this->url()."&offset=".($this->offset-FS_ITEM_LIMIT);
      }
      return $url;
   }
   
   public function siguiente_url()
   {
      $url = '';
      if($this->query!='' AND count($this->resultados)==FS_ITEM_LIMIT)
      {
         $url = $this->url()."&query=".$this->query."&offset=".($this->offset+FS_ITEM_LIMIT);
      }
      else if($this->query=='' AND count($this->resultados)==FS_ITEM_LIMIT)
      {
         $url = $this->url()."&offset=".($this->offset+FS_ITEM_LIMIT);
      }
      return $url;
   }
}
