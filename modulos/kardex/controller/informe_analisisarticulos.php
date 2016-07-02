<?php

require_model('familias.php');
require_model('articulo.php');
require_model('almacen.php');
require_model('albaran_cliente.php');
require_model('albaran_proveedor.php');
require_model('cliente.php');
require_model('factura_cliente.php');
require_model('factura_proveedor.php');
require_model('forma_pago.php');
require_model('pais.php');
require_model('proveedor.php');
require_model('serie.php');
require_model('kardex.php');
require_once 'modulos/core/extras/xlsxwriter.class.php';


class informe_analisisarticulos extends fs_controller
{
   public $resultados;
   public $resultados_almacen;
   public $total_resultados;
   public $familia;
   public $familias;
   public $articulo;
   public $articulos;
   public $fecha_inicio;
   public $fecha_fin;
   public $almacen;
   public $almacenes;
   public $stock;
   public $lista_almacenes;
   public $fileName;
   public $writer;
   public $kardex;
   public $kardex_setup;
   public $kardex_ultimo_proceso;
   public $kardex_procesandose;
   public $kardex_usuario_procesando;
   public $kardex_programado;
   public $loop_horas;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, "Kardex", 'informes', FALSE, TRUE);
   }

   protected function private_core() {
      $this->familias = new familia();
      $this->articulos = new articulo();
      $this->almacenes = new almacen();
      $this->kardex = new kardex();
      $this->share_extension();
      $this->fecha_inicio = \date('01-m-Y');
      $this->fecha_fin = \date('t-m-Y');
      $this->reporte = '';
      $this->total_resultados = 0;
      $this->resultados_almacen = '';
      
      $this->fileName = '';
      $tiporeporte = \filter_input(INPUT_POST, 'procesar-reporte');

      for($x=0; $x<25;$x++)
      {
         $this->loop_horas[]=str_pad($x, 2,"0", STR_PAD_LEFT);
      }

      $fsvar = new fs_var();
      $this->kardex_setup = $fsvar->array_get(
         array(
         'kardex_ultimo_proceso' => '',
         'kardex_cron' => '',
         'kardex_programado' => '',
         'kardex_procesandose' => 'FALSE',
         'kardex_usuario_procesando' => 'cron'
         ), FALSE
      );
      $comparacion_fechas = (date('Y-m-d', strtotime($this->kardex_setup['kardex_ultimo_proceso'])) == date('Y-m-d', strtotime($this->kardex->ultimo_proceso())));
      $this->kardex_ultimo_proceso = ($comparacion_fechas)?$this->kardex_setup['kardex_ultimo_proceso']:$this->kardex->ultimo_proceso();
      $this->kardex_procesandose = ($this->kardex_setup['kardex_procesandose']=='TRUE')?TRUE:FALSE;
      $this->kardex_usuario_procesando = $this->kardex_setup['kardex_usuario_procesando'];
      $this->kardex_cron = $this->kardex_setup['kardex_cron'];
      $this->kardex_programado = $this->kardex_setup['kardex_programado'];
      if(!empty($tiporeporte)){
         $inicio = \date('Y-m-d', strtotime(\filter_input(INPUT_POST, 'inicio')));
         $fin = \date('Y-m-d', strtotime(\filter_input(INPUT_POST, 'fin')));
         $almacen = \filter_input(INPUT_POST, 'almacen');
         $familia = \filter_input(INPUT_POST, 'familia');
         $articulo = \filter_input(INPUT_POST, 'articulo');
         $this->fecha_inicio = $inicio;
         $this->fecha_fin = $fin;
         $this->reporte = $tiporeporte;
         $this->almacen = ($almacen!='null')?$this->comma_separated_to_array($almacen):NULL;
         $this->familia = ($familia!='null')?$this->comma_separated_to_array($familia):NULL;
         $this->articulo = ($articulo!='null')?$this->comma_separated_to_array($articulo):NULL;
         $this->kardex_almacen();
      }

      $kardex = \filter_input(INPUT_GET, 'procesar-kardex');
      if(!empty($kardex)){
         $kardex_inicio = \filter_input(INPUT_GET, 'kardex_inicio');
         $kardex_fin = \filter_input(INPUT_GET, 'kardex_fin');
         $k = new kardex();
         if(!empty($kardex_inicio)){
            $k->fecha_inicio = $kardex_inicio;
            $k->fecha_fin = $kardex_fin;
         }
         $this->template = false;
         header('Content-Type: application/json');
         $k->procesar_kardex($this->user->nick);
      }

      $opciones_kardex = \filter_input(INPUT_POST, 'opciones-kardex');
      if(!empty($opciones_kardex)){
         $data = array();
         $op_kardex_cron = \filter_input(INPUT_POST, 'kardex_cron');
         $op_kardex_programado = \filter_input(INPUT_POST, 'kardex_programado');
         $kardex_cron = ($op_kardex_cron == 'TRUE')?"TRUE":"FALSE";
         $kardex_programado = $op_kardex_programado;
         $kardex_config =
            array(
               'kardex_cron' => $kardex_cron,
               'kardex_programado' => $kardex_programado
            );
         if($fsvar->array_save($kardex_config)){
            $data['success']=true;
            $data['mensaje']='Cambios grabados correctamente';
         }
         else
         {
            $data['success']=false;
            $data['mensaje']='Ocurrio un error al grabar las opciones, intentelo nuevamente';
         }
         $this->template = false;
         header('Content-Type: application/json');
         echo json_encode($data);
      }
   }

   public function kardex_almacen(){
      $resumen = array();
      $this->fileName = 'tmp/'.FS_TMP_NAME.'/Kardex'."_".$this->user->nick.".xlsx";
      if(file_exists($this->fileName)){
         unlink($this->fileName);
      }
      $header = array(
         'Fecha'=>'date',
         'Documento'=>'string',
         'Número'=>'string',
         'Código'=>'string',
         'Artículo'=>'string',
         'Salida'=>'#,###,###.##',
         'Ingreso'=>'#,###,###.##',
         'Saldo'=>'#,###,###.##',
         'Salida Valorizada'=>'#,###,###.##',
         'Ingreso Valorizado'=>'#,###,###.##',
         'Saldo Valorizado'=>'#,###,###.##');
      $this->writer = new XLSXWriter();

      foreach($this->almacen as $index=>$codigo)
      {
         $almacen0 = $this->almacenes->get($codigo);
         $this->writer->writeSheetHeader($almacen0->nombre, $header );
         $resumen = array_merge($resumen, $this->stock_query($almacen0));
      }
      $this->writer->writeToFile($this->fileName);
      gc_collect_cycles();
      $this->resultados_almacen = $resumen;
      $data['rows'] = $resumen;
      $data['filename'] = $this->fileName;
      $this->template = false;
      header('Content-Type: application/json');
      echo json_encode($data);
   }

    public function stock_query($almacen){
      //Validamos el listado de Familias seleccionadas
      $codfamilia = ($this->familia)?" and codfamilia IN ({$this->familia_data()})":" ";

      //Validamos el listado de Productos seleccionados
      $referencia = ($this->articulo)?" and referencia IN ({$this->articulo_data()})":" ";

      //Generamos el select para la subconsulta
      $productos = "SELECT referencia FROM articulos where bloqueado = false and nostock = false $codfamilia $referencia";
      $lista = array();
      
      /*
       * Obtenemos el saldo inicial para el rango de fechas de la tabla de Inventario Diario
       */
      $listado = $this->kardex->saldo($this->fecha_inicio, $almacen->codalmacen, $referencia);
      if($listado){
         foreach($listado as $linea){
            $resultados['codalmacen'] = $almacen->codalmacen;
            $resultados['nombre'] = $almacen->nombre;
            $resultados['fecha'] = $this->fecha_inicio;
            $resultados['tipo_documento'] = "Saldo Inicial";
            $resultados['documento'] = 'STOCK';
            $resultados['referencia'] = $linea['referencia'];
            $resultados['descripcion'] = $linea['descripcion'];
            $resultados['saldo_cantidad'] = $linea['cantidad_inicial'];
            $resultados['saldo_monto'] = $linea['monto_inicial'];
            $resultados['salida_cantidad'] = 0;
            $resultados['ingreso_cantidad'] = 0;
            $resultados['salida_monto'] = 0;
            $resultados['ingreso_monto'] = 0;
            $lista[$this->fecha_inicio][] = $resultados;
            $this->total_resultados++;
         }
      }


      /*
       * Generamos la informacion de las facturas de proveedor ingresadas
       * que no esten asociadas a un albaran de proveedor
       */
      $sql_facturasprov = "select codalmacen,fc.fecha,fc.idfactura,referencia,descripcion,sum(cantidad) as cantidad, pvptotal as baseimp,ROUND(pvptotal/cantidad) as pre_uni 
         from facturasprov as fc
         join lineasfacturasprov as l ON (fc.idfactura=l.idfactura)
         where codalmacen = 'ALG' AND fecha between'2016-04-01' and '2016-04-30'
         and anulada=FALSE and idalbaran is null
         and l.referencia in (SELECT referencia FROM articulos where bloqueado = false and nostock = false)
         group by codalmacen,fc.fecha,fc.idfactura,referencia,descripcion
         order by codalmacen,referencia,fecha;";
      $data = $this->db->select($sql_facturasprov);
      if($data){
         foreach($data as $linea){
            $resultados['codalmacen'] = $linea['codalmacen'];
            $resultados['nombre'] = $almacen->nombre;
            $resultados['fecha'] = $linea['fecha'];
            $resultados['tipo_documento'] = ucfirst(FS_FACTURA)." compra";
            $resultados['documento'] = $linea['idfactura'];
            $resultados['referencia'] = $linea['referencia'];
            $resultados['descripcion'] = $linea['descripcion'];
            $resultados['salida_cantidad'] = ($linea['cantidad']<=0)?$linea['cantidad']:0;
            $resultados['salida_precio_uni'] = ($linea['pre_uni']>=0)?$linea['pre_uni']:0;
            $resultados['ingreso_cantidad'] = ($linea['cantidad']>=0)?$linea['cantidad']:0;
            $resultados['ingreso_precio_uni'] = ($linea['pre_uni']<=0)?$linea['pre_uni']:0;
            $resultados['salida_monto'] = ($linea['baseimp']<=0)?$linea['baseimp']:0;
            $resultados['ingreso_monto'] = ($linea['baseimp']>=0)?$linea['baseimp']:0;
            $lista[$linea['fecha']][] = $resultados;
            $this->total_resultados++;
         }
      }



      /*
       * Generamos la informacion de las facturas que se han generado sin albaran
       */
      $sql_facturas = "select codalmacen,fc.fecha,fc.idfactura,referencia,descripcion,sum(cantidad) as cantidad,pvptotal as baseimp,ROUND(pvptotal/cantidad) as pre_uni 
         from facturascli as fc
         join lineasfacturascli as l ON (fc.idfactura=l.idfactura)
         where codalmacen = 'ALG' AND fecha between '2016-04-01' and '2016-04-30'
         and anulada=FALSE and idalbaran is null
         and l.referencia in (SELECT referencia FROM articulos where bloqueado = false and nostock = false)
         group by codalmacen,fc.fecha,fc.idfactura,referencia,descripcion
         order by codalmacen,fecha;";
      $data = $this->db->select($sql_facturas);
      if($data){
         foreach($data as $linea){
            $resultados['codalmacen'] = $linea['codalmacen'];
            $resultados['nombre'] = $almacen->nombre;
            $resultados['fecha'] = $linea['fecha'];
            $resultados['tipo_documento'] = ucfirst(FS_FACTURA)." venta";
            $resultados['documento'] = $linea['idfactura'];
            $resultados['referencia'] = $linea['referencia'];
            $resultados['descripcion'] = $linea['descripcion'];
            $resultados['salida_cantidad'] = ($linea['cantidad']>=0)?$linea['cantidad']:0;
            $resultados['salida_precio_uni'] = ($linea['pre_uni']>=0)?$linea['pre_uni']:0;
            $resultados['ingreso_cantidad'] = ($linea['cantidad']<=0)?$linea['cantidad']:0;
            $resultados['ingreso_precio_uni'] = ($linea['pre_uni']<=0)?$linea['pre_uni']:0;
            $resultados['salida_monto'] = ($linea['baseimp']>=0)?$linea['baseimp']:0;
            $resultados['ingreso_monto'] = ($linea['baseimp']<=0)?$linea['baseimp']:0;
            $lista[$linea['fecha']][] = $resultados;
            $this->total_resultados++;
         }
      }
      return $this->generar_resultados($lista,$almacen);
   }

    public function generar_resultados($lista,$almacen){
       $intera = 0;
      $linea_resultado = array();
      $lista_resultado = array();
      $cabecera_export = array();
      $lista_export = array();
      $resumen = array();
      ksort($lista);
      foreach($lista as $fecha){
         foreach($fecha as $value){
            /*
            if(!isset($resumen[$value['codalmacen']][$value['referencia']]['saldo_cantidad'])){
                $resumen[$value['codalmacen']][$value['referencia']]['saldo_cantidad'] = 0;
            }
            if(!isset($resumen[$value['codalmacen']][$value['referencia']]['saldo_monto'])){
                $resumen[$value['codalmacen']][$value['referencia']]['saldo_monto'] = 0;
            }
            if(!isset($lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']][$value['documento']]['saldo_monto'])){
                $lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']][$value['documento']]['saldo_monto'] = 0;
            }
            if(!isset($lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']][$value['documento']]['salida_monto'])){
                $lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']][$value['documento']]['salida_monto'] = 0;
            }
            if(!isset($lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']][$value['documento']]['salida_cantidad'])){
                $lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']][$value['documento']]['salida_cantidad'] = 0;
            }
            if(!isset($lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']][$value['documento']]['ingreso_monto'])){
                $lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']][$value['documento']]['ingreso_monto'] = 0;
            }
            if(!isset($lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']][$value['documento']]['ingreso_cantidad'])){
                $lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']][$value['documento']]['ingreso_cantidad'] = 0;
            }


            $ingreso_cantidad = $value['ingreso_cantidad'];
            $ingreso_monto = $value['ingreso_monto'];
            $salida_cantidad = $value['salida_cantidad'];
            $salida_monto = $value['salida_monto'];
            $salida_precio_uni = $value['salida_precio_uni'];
            $ingreso_precio_uni= $value['ingreso_precio_uni'];
            $saldo_cantidad =$resumen[$value['codalmacen']][$value['referencia']]['saldo_cantidad'];
            $saldo_monto=$resumen[$value['codalmacen']][$value['referencia']]['saldo_monto'];




            $cabecera_export[$value['referencia']]=$value['descripcion'];
            $lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']] [$value['documento']]['ingreso_cantidad'] += $ingreso_cantidad;
            $lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']] [$value['documento']]['ingreso_monto'] += $ingreso_monto;
            $lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']] [$value['documento']]['salida_cantidad'] += $salida_cantidad;
            $lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']] [$value['documento']]['salida_monto'] += $salida_monto;
            $lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']] [$value['documento']]['saldo_cantidad'] = $saldo_cantidad;
            $lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']] [$value['documento']]['saldo_monto'] = $saldo_monto;
            $lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']] [$value['documento']]['ingreso_precio_uni'] = $ingreso_precio_uni;
            $lista_export[$value['referencia']][$value['fecha']][$value['tipo_documento']] [$value['documento']]['salida_precio_uni'] = $salida_precio_uni;
            */
            $lista_export[] = $value;

         }
      }

      /*foreach($lista_export as $referencia=>$listafecha){
         $lineas = 0;
         $sumaSalidasQda[$referencia]=0;
         $sumaSalidasMonto[$referencia]=0;
         $sumaIngresosQda[$referencia]=0;
         $sumaIngresosMonto[$referencia]=0;
         foreach($listafecha as $fecha=>$tipo_documentos){
            foreach($tipo_documentos as $tipo_documento=>$documentos){
               foreach($documentos as $documento=>$movimiento){
                  if($lineas == 0){
                     $this->writer->writeSheetRow($almacen->nombre,
                         array('', '', '', '', $cabecera_export[$referencia], '', '', '', '', '', '')
                     );
                  }
                  $this->writer->writeSheetRow($almacen->nombre,
                     array(
                        $fecha,
                        $tipo_documento,
                        $documento,
                        $referencia,
                        $cabecera_export[$referencia],
                        $movimiento['salida_cantidad'],
                        $movimiento['ingreso_cantidad'],
                        $movimiento['saldo_cantidad'],
                        $movimiento['salida_monto'],
                        $movimiento['ingreso_monto'],
                        $movimiento['saldo_monto']
                     )
                  );
                  $sumaSalidasQda[$referencia] +=$movimiento['salida_cantidad'];
                  $sumaSalidasMonto[$referencia] +=$movimiento['salida_monto'];
                  $sumaIngresosQda[$referencia] +=$movimiento['ingreso_cantidad'];
                  $sumaIngresosMonto[$referencia] +=$movimiento['ingreso_monto'];
                  $lineas++;
               }
            }
         }
         $this->writer->writeSheetRow($almacen->nombre,
            array('', '', '', '', 'Saldo Final', $sumaSalidasQda[$referencia], $sumaIngresosQda[$referencia], ($sumaIngresosQda[$referencia]-$sumaSalidasQda[$referencia]), $sumaSalidasMonto[$referencia], $sumaIngresosMonto[$referencia], ($sumaIngresosMonto[$referencia]-$sumaSalidasMonto[$referencia]))
         );
         $this->writer->writeSheetRow($almacen->nombre,
            array('', '', '', '', '', '', '', '', '', '', '')
         );
      }*/

      return $lista_export;
   }

   private function share_extension()
   {
      $extensiones = array(
         array(
            'name' => 'analisisarticulos_css001',
            'page_from' => __CLASS__,
            'page_to' => 'informe_analisisarticulos',
            'type' => 'head',
            'text' => '<link rel="stylesheet" type="text/css" media="screen" href="modulos/kardex/view/css/ui.jqgrid-bootstrap.css"/>',
            'params' => ''
         ),
         array(
            'name' => 'analisisarticulos_css002',
            'page_from' => __CLASS__,
            'page_to' => 'informe_analisisarticulos',
            'type' => 'head',
            'text' => '<link rel="stylesheet" type="text/css" media="screen" href="modulos/kardex/view/css/bootstrap-select.min.css"/>',
            'params' => ''
         ),
      );

      foreach ($extensiones as $ext) {
         $fsext0 = new fs_extension($ext);
         if (!$fsext0->save()) {
            $this->new_error_msg('Imposible guardar los datos de la extensión ' . $ext['name'] . '.');
         }
      }
   }

   private function familia_data()
   {
      $result = "'";
      foreach($this->familia as $key=>$value){
         $result .= $value."',";
      }
      return substr($result, 0, strlen($result)-1);
   }

   private function articulo_data()
   {
      $result = "'";
      foreach($this->articulo as $key=>$value){
         $result .= $value."',";
      }
      return substr($result, 0, strlen($result)-1);
   }

   private function comma_separated_to_array($string, $separator = ',')
   {
      //Explode on comma
      $vals = explode($separator, $string);

      //Trim whitespace
      foreach($vals as $key => $val) {
         $vals[$key] = trim($val);
      }
      //Return empty array if no items found
      //http://php.net/manual/en/function.explode.php#114273
      return array_diff($vals, array(""));
   }
}
