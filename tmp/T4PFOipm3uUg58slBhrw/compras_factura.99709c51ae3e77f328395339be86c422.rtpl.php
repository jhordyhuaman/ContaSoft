<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<?php if( $fsc->factura ){ ?>

<script type="text/javascript">
   $(document).ready(function() {
      $("#b_imprimir").click(function(event) {
         event.preventDefault();
         $("#modal_imprimir").modal('show');
      });
      $("#b_eliminar").click(function(event) {
         event.preventDefault();
         $("#modal_eliminar").modal('show');
      });
      <?php if( $fsc->factura->totalrecargo==0 ){ ?>

      $(".recargo").hide();
      <?php } ?>

      <?php if( $fsc->factura->totalirpf==0 ){ ?>

      $(".irpf").hide();
      <?php } ?>

   });
</script>

<form action="<?php echo $fsc->factura->url();?>" method="post" class="form">
   <input type="hidden" name="idfactura" value="<?php echo $fsc->factura->idfactura;?>"/>
   <div class="container-fluid">
      <div class="row">
         <div class="col-xs-8">
            <a class="btn btn-sm btn-default hidden-xs" href="<?php echo $fsc->url();?>" title="recargar la página">
               <span class="glyphicon glyphicon-refresh"></span>
            </a>
            <div class="btn-group">
               <a id="b_imprimir" class="btn btn-sm btn-default" href="#">
                  <span class="glyphicon glyphicon-print"></span>
                  <span class="hidden-xs">&nbsp; Imprimir</span>
               </a>
               <?php if( $fsc->factura->idasiento ){ ?>

               <div class="btn-group">
                  <button class="btn btn-sm btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true">
                     <span class="glyphicon glyphicon-eye-open"></span>
                     <span class="hidden-xs">&nbsp; Asientos</span>
                  </button>
                  <ul class="dropdown-menu">
                     <li><a href="<?php echo $fsc->factura->asiento_url();?>">Asiento principal</a></li>
                     <?php if( $fsc->factura->idasientop ){ ?>

                     <li><a href="<?php echo $fsc->factura->asiento_pago_url();?>">Asiento de pago</a></li>
                     <?php } ?>

                  </ul>
               </div>
               <?php }else{ ?>

               <a class="btn btn-sm btn-default" href="<?php echo $fsc->url();?>&gen_asiento=TRUE&petid=<?php echo $fsc->random_string();?>">
                  <span class="glyphicon glyphicon-paperclip"></span>
                  <span class="hidden-xs">&nbsp; Generar asiento</span>
               </a>
               <?php } ?>

            </div>
            
            <?php if( $fsc->mostrar_boton_pagada ){ ?>

            <div class="btn-group">
               <?php if( $fsc->factura->pagada ){ ?>

               <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown">
                  <span class="glyphicon glyphicon-ok"></span> &nbsp; Pagada <span class="caret"></span>
               </button>
               <?php }else{ ?>

               <button type="button" class="btn btn-sm btn-warning dropdown-toggle" data-toggle="dropdown">
                  <span class="glyphicon glyphicon-remove"></span> &nbsp; Sin pagar <span class="caret"></span>
               </button>
               <?php } ?>

               <ul class="dropdown-menu" role="menu">
                  <?php if( !$fsc->factura->pagada ){ ?>

                  <li><a href="<?php echo $fsc->url();?>&pagada=TRUE"><span class="glyphicon glyphicon-ok"></span> &nbsp; Pagada</a></li>
                  <?php }else{ ?>

                  <li><a href="<?php echo $fsc->url();?>&pagada=FALSE"><span class="glyphicon glyphicon-remove"></span> &nbsp; Sin pagar</a></li>
                  <?php } ?>

               </ul>
            </div>
            <?php }elseif( $fsc->factura->pagada ){ ?>

            <a class="btn btn-sm btn-info" href="#">
               <span class="glyphicon glyphicon-ok"></span>
               <span class="hidden-xs">&nbsp; Pagada</span>
            </a>
            <?php } ?>

            
            <div class="btn-group">
            <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <?php if( $value1->type=='button' ){ ?>

               <a href="index.php?page=<?php echo $value1->from;?>&id=<?php echo $fsc->factura->idfactura;?><?php echo $value1->params;?>" class="btn btn-sm btn-default"><?php echo $value1->text;?></a>
               <?php }elseif( $value1->type=='modal' ){ ?>

               <!--<?php $txt=$this->var['txt']=base64_encode($value1->text);?>-->
               <!--<?php echo $url='index.php?page='.$value1->from.'&id='.$fsc->factura->idfactura.$value1->params;?>-->
               <a href="#" class="btn btn-sm btn-default" onclick="fs_modal('<?php echo $txt;?>','<?php echo $url;?>')"><?php echo $value1->text;?></a>
               <?php } ?>

            <?php } ?>

            </div>
         </div>
         <div class="col-xs-4 text-right">
            <a class="btn btn-sm btn-success" href="index.php?page=nueva_compra&tipo=factura" title="Nueva factura">
               <span class="glyphicon glyphicon-plus"></span>
            </a>
            <div class="btn-group">
               <?php if( $fsc->allow_delete ){ ?>

               <a id="b_eliminar" class="btn btn-sm btn-danger" href="#">
                  <span class="glyphicon glyphicon-trash"></span>
                  <span class="hidden-sm hidden-xs">&nbsp; Eliminar</span>
               </a>
               <?php } ?>

               <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
                  <span class="glyphicon glyphicon-floppy-disk"></span>
                  <span class="hidden-xs">&nbsp; Guardar</span>
               </button>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-md-12">
            <br/>
            <ol class="breadcrumb" style="margin-bottom: 5px;">
               <li><a href="<?php echo $fsc->ppage->url();?>">Compras</a></li>
               <li><a href="<?php echo $fsc->ppage->url();?>">Factura</a></li>
               <li>
                  <?php $loop_var1=$fsc->serie->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                     <?php if( $value1->codserie==$fsc->factura->codserie ){ ?>

                     <a href="<?php echo $fsc->ppage->url();?>&codserie=<?php echo $value1->codserie;?>" class="text-capitalize"><?php echo $value1->descripcion;?></a>
                     <?php } ?>

                  <?php } ?>

               </li>
               <li>
                  <a href="<?php echo $fsc->factura->proveedor_url();?>"><?php echo $fsc->factura->nombre;?></a>
               </li>
               <?php if( $fsc->proveedor ){ ?>

                  <?php if( $fsc->proveedor->nombre!=$fsc->factura->nombre ){ ?>

                  <li>
                     <a href="#" onclick="alert('Proveedor conocido como: <?php echo $fsc->proveedor->nombre;?>')">
                        <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                     </a>
                  </li>
                  <?php } ?>

               <?php } ?>

               <li class="active"><b><?php echo $fsc->factura->codigo;?></b></li>
            </ol>
            <p>
               <?php if( $fsc->agente ){ ?>

               Factura creada por <a href="<?php echo $fsc->agente->url();?>"><?php echo $fsc->agente->get_fullname();?></a>.
               <?php }else{ ?>

               Sin datos de qué empleado ha creado esta factura.
               <?php } ?>

               &nbsp;
               <?php if( $fsc->rectificada ){ ?>

               <a href="<?php echo $fsc->rectificada->url();?>" class="label label-danger">
                  <span class="glyphicon glyphicon-flag" aria-hidden="true"></span>
                  &nbsp; <?php  echo FS_FACTURA_RECTIFICATIVA;?> de <?php echo $fsc->factura->codigorect;?>

               </a>
               <?php }elseif( $fsc->rectificativa ){ ?>

               <a href="<?php echo $fsc->rectificativa->url();?>" class="label label-warning">
                  Hay una <?php  echo FS_FACTURA_RECTIFICATIVA;?> asociada
               </a>
               <?php }elseif( $fsc->factura->anulada ){ ?>

               <span class="label label-danger">Anulada</span>
               <?php } ?>

            </p>
         </div>
      </div>
      
      <div class="row">
         <div class="col-sm-4">
            <div class="form-group">
               Núm. Proveedor:
               <input class="form-control" type="text" name="numproveedor" value="<?php echo $fsc->factura->numproveedor;?>"/>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               Fecha:
               <input class="form-control datepicker" type="text" name="fecha" value="<?php echo $fsc->factura->fecha;?>" autocomplete="off"/>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               Hora:
               <input class="form-control" type="text" name="hora" value="<?php echo $fsc->factura->hora;?>" autocomplete="off"/>
            </div>
         </div>
         <div class="col-sm-4">
            <div class="form-group">
               <a href="<?php echo $fsc->forma_pago->url();?>">Forma de pago</a>:
               <select name="forma_pago" class="form-control">
                  <?php $loop_var1=$fsc->forma_pago->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <option value="<?php echo $value1->codpago;?>"<?php if( $fsc->factura->codpago==$value1->codpago ){ ?> selected=""<?php } ?>><?php echo $value1->descripcion;?></option>
                  <?php } ?>

               </select>
            </div>
         </div>
      </div>
   </div>
   
   <div role="tabpanel">
      <ul class="nav nav-tabs" role="tablist">
         <li role="presentation" class="active">
            <a href="#lineas_f" aria-controls="lineas_f" role="tab" data-toggle="tab">
               <span class="glyphicon glyphicon-list" aria-hidden="true"></span>
               <span class="hidden-xs">&nbsp; Líneas</span>
            </a>
         </li>
         <li role="presentation">
            <a href="#detalles" aria-controls="detalles" role="tab" data-toggle="tab">
               <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
               <span class="hidden-xs">&nbsp; Detalles</span>
            </a>
         </li>
         <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

            <?php if( $value1->type=='tab' ){ ?>

            <li role="presentation">
               <a href="#ext_<?php echo $value1->name;?>" aria-controls="ext_<?php echo $value1->name;?>" role="tab" data-toggle="tab"><?php echo $value1->text;?></a>
            </li>
            <?php } ?>

         <?php } ?>

      </ul>
      <div class="tab-content">
         <div role="tabpanel" class="tab-pane active" id="lineas_f">
            <div class="table-responsive">
               <table class="table table-hover">
                  <thead>
                     <tr>
                        <th class="text-left text-capitalize"><?php  echo FS_ALBARAN;?></th>
                        <th class="text-left">Artículo</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Precio</th>
                        <th class="text-right">Dto</th>
                        <th class="text-right">Neto</th>
                        <th class="text-right"><?php  echo FS_IVA;?></th>
                        <th class="recargo text-right">RE</th>
                        <th class="irpf text-right"><?php  echo FS_IRPF;?></th>
                        <th class="text-right">Total</th>
                     </tr>
                  </thead>
                  <?php $loop_var1=$fsc->factura->get_lineas(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <tr<?php if( $value1->cantidad<=0 ){ ?> class="warning"<?php } ?>>
                     <td>
                        <?php if( $value1->idalbaran ){ ?>

                        <a href="<?php echo $value1->albaran_url();?>"><?php echo $value1->albaran_codigo();?></a>
                        <?php echo $value1->albaran_numero();?>

                        <?php }else{ ?>

                        -
                        <?php } ?>

                     </td>
                     <td>
                        <a href="<?php echo $value1->articulo_url();?>"><?php echo $value1->referencia;?></a>
                        <?php echo $value1->descripcion;?>

                     </td>
                     <td class="text-right"><?php echo $value1->cantidad;?></td>
                     <td class="text-right"><?php echo $fsc->show_precio($value1->pvpunitario, $fsc->factura->coddivisa);?></td>
                     <td class="text-right"><?php echo $fsc->show_numero($value1->dtopor, 2);?> %</td>
                     <td class="text-right"><?php echo $fsc->show_precio($value1->pvptotal, $fsc->factura->coddivisa);?></td>
                     <td class="text-right"><?php echo $fsc->show_numero($value1->iva, 2);?> %</td>
                     <td class="recargo text-right"><?php echo $fsc->show_numero($value1->recargo, 2);?> %</td>
                     <td class="irpf text-right"><?php echo $fsc->show_numero($value1->irpf, 2);?> %</td>
                     <td class="text-right"><?php echo $fsc->show_precio($value1->total_iva(), $fsc->factura->coddivisa);?></td>
                  </tr>
                  <?php }else{ ?>

                  <tr class="warning">
                     <td colspan="10">
                        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                        &nbsp; No hay líneas.
                     </td>
                  </tr>
                  <?php } ?>

                  <tr>
                     <?php if( $fsc->factura->coddivisa!=$fsc->empresa->coddivisa ){ ?>

                     <td colspan="5" class="text-right warning"><b><?php echo $fsc->factura->coddivisa;?>:</b></td>
                     <?php }else{ ?>

                     <td colspan="5"></td>
                     <?php } ?>

                     <td class="text-right"><b><?php echo $fsc->show_precio($fsc->factura->neto, $fsc->factura->coddivisa);?></b></td>
                     <td class="text-right"><b><?php echo $fsc->show_precio($fsc->factura->totaliva, $fsc->factura->coddivisa);?></b></td>
                     <td class="recargo text-right"><b><?php echo $fsc->show_precio($fsc->factura->totalrecargo, $fsc->factura->coddivisa);?></b></td>
                     <td class="irpf text-right"><b>-<?php echo $fsc->show_precio($fsc->factura->totalirpf, $fsc->factura->coddivisa);?></b></td>
                     <td class="text-right"><b><?php echo $fsc->show_precio($fsc->factura->total, $fsc->factura->coddivisa);?></b></td>
                  </tr>
               </table>
            </div>
            
            <div class="container-fluid">
               <div class="row">
                  <div class="col-sm-12">
                     <div class="form-group">
                        Observaciones:
                        <textarea class="form-control" name="observaciones" rows="3"><?php echo $fsc->factura->observaciones;?></textarea>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div role="tabpanel" class="tab-pane" id="detalles">
            <div class="container-fluid" style="margin-top: 10px;">
               <div class="row">
                  <div class="col-sm-3">
                     <div class="form-group">
                        Nombre del proveedor:
                        <input class="form-control" type="text" name="nombre" value="<?php echo $fsc->factura->nombre;?>" autocomplete="off"/>
                     </div>
                  </div>
                  <div class="col-sm-2">
                     <div class="form-group">
                        <?php  echo FS_CIFNIF;?>:
                        <input class="form-control" type="text" name="cifnif" value="<?php echo $fsc->factura->cifnif;?>" autocomplete="off"/>
                     </div>
                  </div>
                  <div class="col-sm-2">
                     <div class="form-group">
                        <a href="<?php echo $fsc->divisa->url();?>">Divisa</a>:
                        <select name="divisa" class="form-control" disabled="">
                        <?php $loop_var1=$fsc->divisa->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                           <?php if( $value1->coddivisa==$fsc->factura->coddivisa ){ ?>

                           <option value="<?php echo $value1->coddivisa;?>" selected=""><?php echo $value1->descripcion;?></option>
                           <?php }else{ ?>

                           <option value="<?php echo $value1->coddivisa;?>"><?php echo $value1->descripcion;?></option>
                           <?php } ?>

                        <?php } ?>

                        </select>
                     </div>
                  </div>
                  <div class="col-sm-2">
                     <div class="form-group">
                        Tasa de conversión (1€ = X)
                        <input type="text" name="tasaconv" value="<?php echo $fsc->factura->tasaconv;?>" class="form-control" readonly=""/>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-sm-12">
                     <h3>
                        <span class="glyphicon glyphicon-credit-card" aria-hidden="true"></span>
                        &nbsp; Cuentas bancarias del proveedor:
                     </h3>
                     <div class="table-responsive">
                        <table class="table table-hover">
                           <thead>
                              <tr>
                                 <th width="30"></th>
                                 <th>Codcuenta + Descripción</th>
                                 <th>IBAN</th>
                                 <th>SWIFT/BIC</th>
                              </tr>
                           </thead>
                           <?php $loop_var1=$fsc->get_cuentas_bancarias(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                           <tr class="clickableRow" href="<?php echo $value1->url();?>">
                              <td class="text-right">
                                 <?php if( $value1->principal ){ ?>

                                 <span class="glyphicon glyphicon-flag" aria-hidden="true" title="Cuenta principal"></span>
                                 <?php } ?>

                              </td>
                              <td>
                                 <a href="<?php echo $value1->url();?>"><?php echo $value1->codcuenta;?></a>
                                 <?php echo $value1->descripcion;?>

                              </td>
                              <td><?php echo $value1->iban;?></td>
                              <td><?php echo $value1->swift;?></td>
                           </tr>
                           <?php }else{ ?>

                           <tr class="warning">
                              <td></td>
                              <td colspan="3">
                                 Este proveedor no tiene ninguna cuenta bancaria asignada.
                                 <?php if( $fsc->proveedor ){ ?>

                                 <a href="<?php echo $fsc->proveedor->url();?>#cuentasb">Nueva cuenta bancaria</a>.
                                 <?php } ?>

                              </td>
                           </tr>
                           <?php } ?>

                        </table>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-sm-12">
                     <h3>
                        <span class="glyphicon glyphicon-book" aria-hidden="true"></span>
                        &nbsp; Desglose de impuestos:
                     </h3>
                     <div class="table-responsive">
                        <table class="table table-hover">
                           <thead>
                              <tr>
                                 <th class="text-left">Impuesto</th>
                                 <th class="text-right">Neto</th>
                                 <th class="text-right"><?php  echo FS_IVA;?></th>
                                 <th class="text-right">Total <?php  echo FS_IVA;?></th>
                                 <th class="text-right">RE</th>
                                 <th class="text-right">Total RE</th>
                                 <th class="text-right">Total</th>
                              </tr>
                           </thead>
                           <?php $loop_var1=$fsc->factura->get_lineas_iva(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                           <tr>
                              <td><?php echo $value1->codimpuesto;?></td>
                              <td class="text-right"><?php echo $fsc->show_precio($value1->neto, $fsc->factura->coddivisa);?></td>
                              <td class="text-right"><?php echo $fsc->show_numero($value1->iva, 2);?> %</td>
                              <td class="text-right"><?php echo $fsc->show_precio($value1->totaliva, $fsc->factura->coddivisa);?></td>
                              <td class="text-right"><?php echo $fsc->show_numero($value1->recargo, 2);?> %</td>
                              <td class="text-right"><?php echo $fsc->show_precio($value1->totalrecargo, $fsc->factura->coddivisa);?></td>
                              <td class="text-right"><?php echo $fsc->show_precio($value1->totallinea, $fsc->factura->coddivisa);?></td>
                           </tr>
                           <?php } ?>

                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

            <?php if( $value1->type=='tab' ){ ?>

            <div role="tabpanel" class="tab-pane" id="ext_<?php echo $value1->name;?>">
               <iframe src="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>&id=<?php echo $fsc->factura->idfactura;?>" width="100%" height="2000" frameborder="0"></iframe>
            </div>
            <?php } ?>

         <?php } ?>

      </div>
   </div>
</form>

<div class="modal fade" id="modal_imprimir">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Imprimir factura</h4>
         </div>
         <div class="modal-body">
            <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <?php if( $value1->type=='pdf' ){ ?>

               <a href="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>&id=<?php echo $fsc->factura->idfactura;?>" target="_blank" class="btn btn-block btn-default">
                  <span class="glyphicon glyphicon-print"></span> &nbsp; <?php echo $value1->text;?>

               </a>
               <?php } ?>

            <?php } ?>

         </div>
      </div>
   </div>
</div>

<form action="<?php echo $fsc->url();?>" method="post" class="form">
   <input type="hidden" name="anular" value="TRUE"/>
   <div class="modal fade" id="modal_eliminar">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
               <h4 class="modal-title">¿Quiere eliminar o anular esta factura?</h4>
            
            </div>
            <div class="modal-body bg-warning">
               <?php if( $fsc->factura->idasiento ){ ?>

               Si decide <b>eliminar</b>, hay asociado un asiento contable que será eliminado
               junto con la factura. Además, si no hay asociado un <?php  echo FS_ALBARAN;?> o <?php  echo FS_ALBARANES;?>,
               se restaurará el stock de los artículos.
               <?php }else{ ?>

               Si decide <b>eliminar</b>, se restaurará el stock de los artículos si no hay asociado un
               <?php  echo FS_ALBARAN;?> o <?php  echo FS_ALBARANES;?>.
               <?php } ?>

               <br/><br/>
               Si decide <b>anular</b>, se generará una <b><?php  echo FS_FACTURA_RECTIFICATIVA;?></b>
               y se restaurará el stock de los artículos, aunque primero debe elegir
               la serie para la <?php  echo FS_FACTURA_RECTIFICATIVA;?>:
               <div class="form-group">
                  <select name="codserie" class="form-control">
                  <?php $loop_var1=$fsc->serie->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                     <?php if( $value1->codserie==$fsc->factura->codserie ){ ?>

                     <option value="<?php echo $value1->codserie;?>" selected=""><?php echo $value1->descripcion;?></option>
                     <?php }else{ ?>

                     <option value="<?php echo $value1->codserie;?>"><?php echo $value1->descripcion;?></option>
                     <?php } ?>

                  <?php } ?>

                  </select>
               </div>
               <div class="form-group">
                  <textarea name="motivo" class="form-control" placeholder="Motivo de la anulación"></textarea>
               </div>
            </div>
            <div class="modal-footer">
               <a class="btn btn-sm btn-danger pull-left" href="<?php echo $fsc->ppage->url();?>&delete=<?php echo $fsc->factura->idfactura;?>">
                  <span class="glyphicon glyphicon-trash"></span> &nbsp; Eliminar
               </a>
               <button type="submit" class="btn btn-sm btn-warning">
                  <span class="glyphicon glyphicon-flag"></span> &nbsp; Anular
               </button>
            </div>
         </div>
      </div>
   </div>
</form>
<?php }else{ ?>

<div class="thumbnail">
   <img src="view/img/fuuu_face.png" alt="fuuuuu"/>
</div>
<?php } ?>


<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>