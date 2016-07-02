<?php


require_model('articulo.php');
require_model('articulo_traza.php');


class articulo_trazabilidad extends fs_controller
{
   public $articulo;
   public $trazas;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, '', 'ventas', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $art0 = new articulo();
      
      $this->articulo = FALSE;
      if( isset($_REQUEST['ref']) )
      {
         $this->articulo = $art0->get($_REQUEST['ref']);
      }
      
      if($this->articulo)
      {
         $atraza = new articulo_traza();
         
         if( isset($_POST['numserie']) )
         {
            if($_POST['numserie'] != '' OR $_POST['lote'] != '')
            {
               if( isset($_POST['id']) )
               {
                  $natraza = $atraza->get($_POST['id']);
               }
               else
               {
                  $natraza = new articulo_traza();
                  $natraza->referencia = $this->articulo->referencia;
               }
               
               $natraza->numserie = NULL;
               if($_POST['numserie'] != '')
               {
                  $natraza->numserie = $_POST['numserie'];
               }
               
               $natraza->lote = NULL;
               if($_POST['lote'] != '')
               {
                  $natraza->lote = $_POST['lote'];
               }
               
               if( $natraza->save() )
               {
                  $this->new_message('Datos guardados correctamente.');
               }
               else
               {
                  $this->new_error_msg('Error al guardar los datos.');
               }
            }
            else
            {
               $this->new_error_msg('Debes escribir un número de serie o un lote o ambos,'
                       . ' pero algo debes escribir.');
            }
         }
         else if( isset($_GET['delete']) )
         {
            $natraza = $atraza->get($_GET['delete']);
            if($natraza)
            {
               if( $natraza->delete() )
               {
                  $this->new_message('Datos eliminados correctamente.');
               }
               else
               {
                  $this->new_error_msg('Error al eliminar los datos.');
               }
            }
         }
         
         $this->trazas = $atraza->all_from_ref($this->articulo->referencia);
      }
      else
         $this->new_error_msg('Artículo no encontrado.');
   }
   
   public function url()
   {
      if($this->articulo)
      {
         return 'index.php?page='.__CLASS__.'&ref='.urlencode($this->articulo->referencia);
      }
      else
      {
         return parent::url();
      }
   }
}
