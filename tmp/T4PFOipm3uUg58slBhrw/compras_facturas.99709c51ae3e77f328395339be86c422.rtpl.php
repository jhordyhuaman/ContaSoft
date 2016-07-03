<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>

<script type="text/javascript">
   function buscar_lineas()
   {
      if(document.f_buscar_lineas.buscar_lineas.value == '')
      {
         $('#search_results').html('');
      }
      else
      {
         $.ajax({
            type: 'POST',
            url: '<?php echo $fsc->url();?>',
            dataType: 'html',
            data: $('form[name=f_buscar_lineas]').serialize(),
            success: function(datos) {
               var re = /<!--(.*?)-->/g;
               var m = re.exec( datos );
               if( m[1] == document.f_buscar_lineas.buscar_lineas.value )
               {
                  $('#search_results').html(datos);
               }
            }
         });
      }
   }
   function clean_proveedor()
   {
      document.f_custom_search.ac_proveedor.value='';
      document.f_custom_search.codproveedor.value='';
      document.f_custom_search.ac_proveedor.focus();
   }
   $(document).ready(function() {



      var numlineas =0
      <?php if( $fsc->mostrar=='buscar' ){ ?>

      document.f_custom_search.query.focus();
      <?php } ?>


      $('#b_buscar_lineas').click(function(event) {
         event.preventDefault();
         $('#modal_buscar_lineas').modal('show');
         document.f_buscar_lineas.buscar_lineas.focus();
      });
      $('#f_buscar_lineas').keyup(function() {
         buscar_lineas();
      });
      $('#f_buscar_lineas').submit(function(event) {
         event.preventDefault();
         buscar_lineas();
      });
      $("#ac_proveedor").autocomplete({
         serviceUrl: '<?php echo $fsc->url();?>',
         paramName: 'buscar_proveedor',
         onSelect: function (suggestion) {
            if(suggestion)
            {
               if(document.f_custom_search.codproveedor.value != suggestion.data && suggestion.data != '')
               {
                  document.f_custom_search.codproveedor.value = suggestion.data;
                  document.f_custom_search.submit();
               }
            }
         }
      });



      $('#centra_compra').click(function () {
         $.ajax({
            Type : 'GET',
            url: 'index.php?page=compras_facturas&geturl=1',
            dataType: 'json',
            success:function (data) {
               console.log(data);
               var cuenta = "4011";
               var descri = "IMPUESTO GENERAL A LAS VENTAS";
               var saldo = 0;
               var total_libro = 0;
               $.each(data,function (i,json) {
                  if(json.codimpuesto != 'EXO'){
                     saldo +=json.pvptotal*0.18;
                  }
                  total_libro += json.pvptotal;
                  numlineas = i+1;
                  var datos = "<tr id='partida_"+numlineas+"'>\n\
             <td>\n\
            <input class='form-control' id='codsubcuenta_"+numlineas+"' name='codsubcuenta_"+numlineas+"' type='text' value='"+json.idsubcuenta+"'</td>\n\
             <td>\n\
            <input class='form-control' type='text' id='desc_"+numlineas+"' name='desc_"+numlineas+"' disabled='disabled' value='"+json.scuenta_name+"'/>\n\
             </td>"+
                          '<td><div class="form-control">'+
                          '<div class="row" style="position: relative; left: 8px; bottom: 9px">' +
                          '<div type="checkbox" class="col-ms-6">' +
                          '<label class="radio-inline" style="font-size: 9px; color:#1a237e"><input  id="checdebe_'+numlineas+'" value="debe" type="radio" name="saldo_' + numlineas +'" onclick="combobox(this)" />Debe</label>'+
                          '</div>'+
                          '<div type="checkbox" class="col-ms-6" style="position: relative; bottom: 3px">'+
                          '<label class="radio-inline" style="font-size: 9px; color:#91170a"><input  id="chechaber_'+numlineas+'"  value="haber" type="radio" name="saldo_' + numlineas +'" onclick="combobox(this)"/>Haber</label>'+
                          '</div>'+
                          '</div>'+
                          '</div></td>'+
                          "<td>\n\
                         <input class='form-control text-right' type='text' id='saldo_"+numlineas+"' name='saldo_"+numlineas+"' value='"+json.pvptotal+"' disabled='disabled'/>\n\
            </td>\n\
             <td>\n\
            <input class='form-control text-right' type='text' id='debe_"+numlineas+"' name='debe_"+numlineas+"' value='0'\n\
             </td>\n\
            <td>\n\
            <input class='form-control text-right' type='text' id='haber_"+numlineas+"' name='haber_"+numlineas+"' value='0'\n\
            </td></tr>";

                  $('#partidas').append(datos);

               });
               total_libro += saldo;
               numlineas++;
               var datos = "<tr id='partida_"+numlineas+"'>\n\
             <td>\n\
            <input class='form-control' id='codsubcuenta_"+numlineas+"' name='codsubcuenta_"+numlineas+"' type='text' value='"+cuenta+"'</td>\n\
             <td>\n\
            <input class='form-control' type='text' id='desc_"+numlineas+"' name='desc_"+numlineas+"' disabled='disabled' value='"+descri+"'/>\n\
             </td>"+
                       '<td><div class="form-control">'+
                       '<div class="row" style="position: relative; left: 8px; bottom: 9px">' +
                       '<div type="checkbox" class="col-ms-6">' +
                       '<label class="radio-inline" style="font-size: 9px; color:#1a237e"><input  id="checdebe_'+numlineas+'" value="debe" type="radio" name="saldo_' + numlineas +'" onclick="combobox(this)" />Debe</label>'+
                       '</div>'+
                       '<div type="checkbox" class="col-ms-6" style="position: relative; bottom: 3px">'+
                       '<label class="radio-inline" style="font-size: 9px; color:#91170a"><input  id="chechaber_'+numlineas+'"  value="haber" type="radio" name="saldo_' + numlineas +'" onclick="combobox(this)" />Haber</label>'+
                       '</div>'+
                       '</div>'+
                       '</div></td>'+
                       " <td><input class='form-control text-right' type='text' id='saldo_"+numlineas+"' name='saldo_"+numlineas+"' value='"+saldo+"' disabled='disabled'/>\n\
            </td>\n\
             <td>\n\
            <input class='form-control text-right' type='text' id='debe_"+numlineas+"' name='debe_"+numlineas+"' value='0' />\n\
             </td>\n\
            <td>\n\
            <input class='form-control text-right' type='text' id='haber_"+numlineas+"' name='haber_"+numlineas+"' value='0'/>\n\
            </td></tr>";

               $('#partidas').append(datos);




               $('.total_libro').val(total_libro);
               $('#modal_cent_compra').modal('show');
               $('#num_linea').val(numlineas);
            },
            error:function (data) {
               alert("Error ¡¡");
               console.log(data);

            }
         });



      });


   });

   function combobox(e){

      console.log(e);
      var saldo=$(e).parents('tr').find('td').eq(3).find('input').val();
      var debe=$(e).parents('tr').find('td').eq(4).find('input').val();
      var haber=$(e).parents('tr').find('td').eq(5).find('input').val();

      if($(e).val()=='haber'){
         $(e).parents('tr').find('td').eq(5).find('input').val(saldo);
         $(e).parents('tr').find('td').eq(4).find('input').val('0');
      }else{
         $(e).parents('tr').find('td').eq(4).find('input').val(saldo);
         $(e).parents('tr').find('td').eq(5).find('input').val('0');

      }



   }

   function cent_compra(v) {
      if(v===true){
         $('#f_cent_compras').submit();
         console.log($('#f_cent_compras').serializeArray());
         $('#modal_cent_compra').modal('hidden');

      }

   }
</script>
<form class="form" role="form" id="f_cent_compras" name="f_cent_compras" action="<?php echo $fsc->url();?>" method="post">
   <input type="hidden" id="num_linea" name="numlineas" value="0">
   <div class="modal" id="modal_cent_compra">
      <div class="modal-dialog" style="width: 99%; max-width: 950px;">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
               <h4 class="modal-title">Libros Generados</h4>
            </div>
            <div class="modal-body">
               <div class="tab-content">
                  <div role="tabpanel" class="tab-pane active" id="lineas">
                     <div class="row">
                        <div class="col-lg-2 col-md-2 col-sm-2">
                           <div class="form-group">
                              Fecha:
                              <input class="form-control datepicker" name="fecha" type="text" value="<?php echo $fsc->asiento->fecha;?>"/>
                           </div>
                        </div>
                        <div class="col-lg-3 col-md-2 col-sm-2">
                           <div class="form-group">
                              Concepto:
                              <input class="form-control" name="concepto" type="text" value="" autocomplete="off"/>
                           </div>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-2">
                           <div class="form-group">
                              Importe:
                              <input class="form-control" type="text" name="importe" value="0" readonly/>
                           </div>
                        </div>
                        <div class="col-lg-1 col-md-2 col-sm-2">
                           <div class="form-group">
                              Descuadre:
                              <input class="form-control" type="text" name="descuadre" value="0" readonly/>
                           </div>
                        </div>
                     </div>
                  </div>
                     <div class="table-responsive">
                        <table class="table table-hover">
                           <thead>
                           <tr>
                              <th class="text-left" width="100">Subcuenta</th>
                              <th class="text-left">Descripción</th>
                              <th class="text-left">Asignar</th>
                              <th class="text-right" width="110">Saldo</th>
                              <th class="text-right" width="110">Debe</th>
                              <th class="text-right" width="110">Haber</th>
                           </tr>
                           </thead>
                           <tbody id="partidas">
                           </tbody>
                        </table>
                     </div>
                     <div class="container-fluid">
                        <div class="row">
                           <div class="col-sm-9">

                           </div>
                           <div class="col-sm-3">
                               Importe Total:<input type="text" class="total_libro form-control" placeholder="Total"/>
                           </div>
                        </div>
                        <hr/>
                        <div class="row">
                           <div class="col-xs-6">
                           </div>
                           <div class="col-xs-6 text-right">
                              <div class="btn-group">
                                 <button id="b_guardar_asiento" class="btn btn-sm btn-primary" type="button" onclick="cent_compra(true)" title="Guardar y volver a empezar">
                                    <span class="glyphicon glyphicon-floppy-disk"></span> &nbsp; Centralisar Libro Compras
                                 </button>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>

               </div>
            </div>
         </div>
      </div>
   </div>
</form>
<div class="container-fluid" style="margin-top: 10px; margin-bottom: 10px;">
   <div class="row">
      <div class="col-sm-8 col-xs-6">
         <div class="btn-group hidden-xs">
            <a class="btn btn-sm btn-default" href="<?php echo $fsc->url();?>" title="Recargar la página">
               <span class="glyphicon glyphicon-refresh"></span>
            </a>
            <?php if( $fsc->page->is_default() ){ ?>

            <a class="btn btn-sm btn-default active" href="<?php echo $fsc->url();?>&amp;default_page=FALSE" title="desmarcar como página de inicio">
               <span class="glyphicon glyphicon-home"></span>
            </a>
            <?php }else{ ?>

            <a class="btn btn-sm btn-default" href="<?php echo $fsc->url();?>&amp;default_page=TRUE" title="marcar como página de inicio">
               <span class="glyphicon glyphicon-home"></span>
            </a>
            <?php } ?>

         </div>
         <div class="btn-group">
            <a class="btn btn-sm btn-success" href="index.php?page=nueva_compra&tipo=factura">
               <span class="glyphicon glyphicon-plus"></span>
               <span class="hidden-xs">&nbsp; Nueva</span>
            </a>
            <a class="btn btn-sm btn-danger" id="centra_compra">
               <span class="glyphicon glyphicon-asterisk"></span>
               <span class="hidden-xs">&nbsp; Centralizar Compras</span>
            </a>
            <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <?php if( $value1->type=='button' ){ ?>

               <a href="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>" class="btn btn-sm btn-default"><?php echo $value1->text;?></a>
               <?php } ?>

            <?php } ?>

         </div>
      </div>
      <div class="col-sm-4 col-xs-6 text-right">
         <a id="b_buscar_lineas" class="btn btn-sm btn-info" title="Buscar en las líneas">
            <span class="glyphicon glyphicon-search"></span> &nbsp; Líneas
         </a>
         <div class="btn-group">
            <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
               <li>
                  <a href="<?php echo $fsc->url(TRUE);?>&order=fecha_desc">
                     <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span>
                     &nbsp; Fecha &nbsp;
                     <?php if( $fsc->order=='fecha DESC' ){ ?><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?php } ?>

                  </a>
               </li>
               <li>
                  <a href="<?php echo $fsc->url(TRUE);?>&order=fecha_asc">
                     <span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span>
                     &nbsp; Fecha &nbsp;
                     <?php if( $fsc->order=='fecha ASC' ){ ?><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?php } ?>

                  </a>
               </li>
               <li>
                  <a href="<?php echo $fsc->url(TRUE);?>&order=codigo_desc">
                     <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span>
                     &nbsp; Código &nbsp;
                     <?php if( $fsc->order=='codigo DESC' ){ ?><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?php } ?>

                  </a>
               </li>
               <li>
                  <a href="<?php echo $fsc->url(TRUE);?>&order=codigo_asc">
                     <span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span>
                     &nbsp; Código &nbsp;
                     <?php if( $fsc->order=='codigo ASC' ){ ?><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?php } ?>

                  </a>
               </li>
               <li>
                  <a href="<?php echo $fsc->url(TRUE);?>&order=total_desc">
                     <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span>
                     &nbsp; Total &nbsp;
                     <?php if( $fsc->order=='total DESC' ){ ?><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?php } ?>

                  </a>
               </li>
            </ul>
         </div>
      </div>
   </div>
</div>

<ul class="nav nav-tabs" role="tablist">
   <li<?php if( $fsc->mostrar=='todo' ){ ?> class="active"<?php } ?>>
      <a href="<?php echo $fsc->url();?>&mostrar=todo">
         <span class="text-capitalize hidden-xs">Facturas (todo)</span>
         <span class="visible-xs">Todo</span>
      </a>
   </li>
   <li<?php if( $fsc->mostrar=='sinpagar' ){ ?> class="active"<?php } ?>>
      <a href="<?php echo $fsc->url();?>&mostrar=sinpagar">
         <span class="glyphicon glyphicon-pushpin"></span>
         <span class="hidden-xs">&nbsp; Sin pagar</span>
         <span class="hidden-xs badge"><?php echo $fsc->total_sinpagar();?></span>
      </a>
   </li>
   <li<?php if( $fsc->mostrar=='buscar' ){ ?> class="active"<?php } ?>>
      <a href="<?php echo $fsc->url();?>&mostrar=buscar" title="Buscar">
         <span class="glyphicon glyphicon-search"></span>
         <?php if( $fsc->num_resultados!=='' ){ ?>

         <span class="hidden-xs badge"><?php echo $fsc->num_resultados;?></span>
         <?php } ?>

      </a>
   </li>
   <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

      <?php if( $value1->type=='tab' ){ ?>

      <li<?php if( $fsc->mostrar=='ext_'.$value1->name ){ ?> class="active"<?php } ?>>
         <a href="<?php echo $fsc->url();?>&mostrar=ext_<?php echo $value1->name;?>"><?php echo $value1->text;?></a>
      </li>
      <?php } ?>

   <?php } ?>

</ul>

<?php if( $fsc->mostrar=='buscar' ){ ?>

<br/>
<form name="f_custom_search" action="<?php echo $fsc->url();?>" method="post" class="form">
   <?php if( $fsc->proveedor ){ ?>

   <input type="hidden" name="codproveedor" value="<?php echo $fsc->proveedor->codproveedor;?>"/>
   <?php }else{ ?>

   <input type="hidden" name="codproveedor"/>
   <?php } ?>

   <div class="container-fluid">
      <div class="row">
         <div class="col-sm-2">
            <div class="form-group">
               <div class="input-group">
                  <input class="form-control" type="text" name="query" value="<?php echo $fsc->query;?>" autocomplete="off" placeholder="Buscar">
                  <span class="input-group-btn">
                     <button class="btn btn-primary hidden-sm" type="submit">
                        <span class="glyphicon glyphicon-search"></span>
                     </button>
                  </span>
               </div>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               <select class="form-control" name="codserie" onchange="this.form.submit()">
                  <option value="">Cualquier <?php  echo FS_SERIE;?></option>
                  <option value="">-----</option>
                  <?php $loop_var1=$fsc->serie->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                     <?php if( $value1->codserie==$fsc->codserie ){ ?>

                     <option value="<?php echo $value1->codserie;?>" selected=""><?php echo $value1->descripcion;?></option>
                     <?php }else{ ?>

                     <option value="<?php echo $value1->codserie;?>"><?php echo $value1->descripcion;?></option>
                     <?php } ?>

                  <?php } ?>

               </select>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               <select name="codagente" class="form-control" onchange="this.form.submit()">
                  <option value="">Cualquier empleado</option>
                  <option value="">------</option>
                  <?php $loop_var1=$fsc->agente->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                     <?php if( $value1->codagente==$fsc->codagente ){ ?>

                     <option value="<?php echo $value1->codagente;?>" selected=""><?php echo $value1->get_fullname();?></option>
                     <?php }else{ ?>

                     <option value="<?php echo $value1->codagente;?>"><?php echo $value1->get_fullname();?></option>
                     <?php } ?>

                  <?php } ?>

               </select>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               <div class="input-group">
                  <?php if( $fsc->proveedor ){ ?>

                  <input class="form-control" type="text" name="ac_proveedor" value="<?php echo $fsc->proveedor->nombre;?>" id="ac_proveedor" placeholder="Cualquier proveedor" autocomplete="off"/>
                  <?php }else{ ?>

                  <input class="form-control" type="text" name="ac_proveedor" id="ac_proveedor" placeholder="Cualquier proveedor" autocomplete="off"/>
                  <?php } ?>

                  <span class="input-group-btn">
                     <button class="btn btn-default" type="button" onclick="clean_proveedor()">
                        <span class="glyphicon glyphicon-remove"></span>
                     </button>
                  </span>
               </div>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               <input type="text" name="desde" value="<?php echo $fsc->desde;?>" class="form-control datepicker" placeholder="Desde" autocomplete="off" onchange="this.form.submit()"/>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               <input type="text" name="hasta" value="<?php echo $fsc->hasta;?>" class="form-control datepicker" placeholder="Hasta" autocomplete="off" onchange="this.form.submit()"/>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-12">
            <div class="radio-inline">
               <label>
                  <?php if( $fsc->estado=='' ){ ?>

                  <input type="radio" name="estado" value="" checked="" onchange="this.form.submit()"/>
                  <?php }else{ ?>

                  <input type="radio" name="estado" value="" onchange="this.form.submit()"/>
                  <?php } ?>

                  Todas
               </label>
            </div>
            <div class="radio-inline">
               <label>
                  <?php if( $fsc->estado=='pagadas' ){ ?>

                  <input type="radio" name="estado" value="pagadas" checked="" onchange="this.form.submit()"/>
                  <?php }else{ ?>

                  <input type="radio" name="estado" value="pagadas" onchange="this.form.submit()"/>
                  <?php } ?>

                  Pagadas
               </label>
            </div>
            <div class="radio-inline">
               <label>
                  <?php if( $fsc->estado=='impagadas' ){ ?>

                  <input type="radio" name="estado" value="impagadas" checked="" onchange="this.form.submit()"/>
                  <?php }else{ ?>

                  <input type="radio" name="estado" value="impagadas" onchange="this.form.submit()"/>
                  <?php } ?>

                  Impagadas
               </label>
            </div>
            <div class="radio-inline">
               <label>
                  <?php if( $fsc->estado=='anuladas' ){ ?>

                  <input type="radio" name="estado" value="anuladas" checked="" onchange="this.form.submit()"/>
                  <?php }else{ ?>

                  <input type="radio" name="estado" value="anuladas" onchange="this.form.submit()"/>
                  <?php } ?>

                  Anuladas
               </label>
            </div>
         </div>
      </div>
   </div>
</form>
<?php } ?>


<?php if( in_array($fsc->mostrar, array('todo','sinpagar','buscar')) ){ ?>

<div class="table-responsive">
   <table class="table table-hover">
      <thead>
         <tr>
            <th></th>
            <th></th>
            <th class="text-left">Código + Num. Proveedor</th>
            <th class="text-left">Proveedor</th>
            <th class="text-left">Observaciones</th>
            <th class="text-right">Total</th>
            <th class="text-right">Fecha</th>
         </tr>
      </thead>
      <?php $loop_var1=$fsc->resultados; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

      <tr class="clickableRow<?php if( $value1->anulada ){ ?> danger<?php }elseif( $value1->pagada ){ ?> success<?php }elseif( $value1->total<=0 ){ ?> warning<?php } ?>" href="<?php echo $value1->url();?>">
         <td class="text-center">
            <?php if( $value1->pagada ){ ?>

            <span class="glyphicon glyphicon-ok" title="La factura está pagada"></span>
            <?php } ?>

            <?php if( $value1->anulada ){ ?>

            <span class="glyphicon glyphicon-remove" title="La factura está anulada"></span>
            <?php } ?>

            <?php if( $value1->idfacturarect ){ ?>

            <span class="glyphicon glyphicon-flag" title="<?php  echo FS_FACTURA_RECTIFICATIVA;?> de <?php echo $value1->codigorect;?>"></span>
            <?php } ?>

         </td>
         <td class="text-center">
            <?php if( $value1->idasiento ){ ?>

            <span class="glyphicon glyphicon-paperclip" title="La factura tiene vinculado un asiento contable"></span>
            <?php } ?>

         </td>
         <td><a href="<?php echo $value1->url();?>"><?php echo $value1->codigo;?></a> <?php echo $value1->numproveedor;?></td>
         <td>
            <?php echo $value1->nombre;?>

            <a href="<?php echo $fsc->url();?>&codproveedor=<?php echo $value1->codproveedor;?>" class="cancel_clickable" title="Ver más facturas de <?php echo $value1->nombre;?>">[+]</a>
         </td>
         <td><?php echo $value1->observaciones_resume();?></td>
         <td class="text-right"><?php echo $fsc->show_precio($value1->total, $value1->coddivisa);?></td>
         <td class="text-right" title="Hora <?php echo $value1->hora;?>">
            <?php if( $value1->fecha==$fsc->today() ){ ?><b><?php echo $value1->fecha;?></b><?php }else{ ?><?php echo $value1->fecha;?><?php } ?>

         </td>
      </tr>
      <?php }else{ ?>

      <tr class="warning">
         <td></td>
         <td></td>
         <td colspan="5">Ninguna factura encontrada. Pulsa el botón <b>Nueva</b> para crear una.</td>
      </tr>
      <?php } ?>

      <?php if( $fsc->total_resultados ){ ?>

      <tr>
         <td></td>
         <td colspan="5" class="text-right">
            <?php echo $fsc->total_resultados_txt;?>

            <?php $loop_var1=$fsc->total_resultados; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

            <br/><b><?php echo $fsc->show_precio($value1['total'],$value1['coddivisa'],FALSE);?></b>
            <?php } ?>

         </td>
         <td></td>
      </tr>
      <?php } ?>

   </table>
</div>

<div class="container-fluid">
   <div class="row">
      <div class="col-sm-12 text-center">
         <ul class="pagination">
            <?php $loop_var1=$fsc->paginas(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

            <li<?php if( $value1['actual'] ){ ?> class="active"<?php } ?>>
               <a href="<?php echo $value1['url'];?>"><?php echo $value1['num'];?></a>
            </li>
            <?php } ?>

         </ul>
      </div>
   </div>
</div>
<?php }else{ ?>

   <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

      <?php if( $value1->type=='tab' AND $fsc->mostrar=='ext_'.$value1->name ){ ?>

      <iframe src="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>" width="100%" height="2000" frameborder="0"></iframe>
      <?php } ?>

   <?php } ?>

<?php } ?>


<form class="form" role="form" id="f_buscar_lineas" name="f_buscar_lineas" action="<?php echo $fsc->url();?>" method="post">
   <div class="modal" id="modal_buscar_lineas">
      <div class="modal-dialog" style="width: 99%; max-width: 950px;">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
               <h4 class="modal-title">Buscar en las líneas</h4>
            </div>
            <div class="modal-body">
               <div class="input-group">
                  <input class="form-control" type="text" name="buscar_lineas" placeholder="Referencia" autocomplete="off"/>
                  <span class="input-group-btn">
                     <button class="btn btn-primary" type="submit">
                        <span class="glyphicon glyphicon-search"></span>
                     </button>
                  </span>
               </div>
            </div>
            <div id="search_results" class="table-responsive"></div>
         </div>
      </div>
   </div>
</form>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>