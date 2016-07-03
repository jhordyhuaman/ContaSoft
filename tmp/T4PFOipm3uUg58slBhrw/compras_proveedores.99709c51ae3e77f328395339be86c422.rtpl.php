<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<script type="text/javascript" src="<?php echo $fsc->get_js_location('provincias.js');?>"></script>
<script type="text/javascript">
   function acreedores_help()
   {
      alert('Los acreedores son todos aquellos proveedores a los que no les compramos mercancias. Por ejemplo: proveedor de internet, teléfono, bancos...');
      return false;
   }
   $(document).ready(function() {
      document.f_custom_search.query.focus();
      if(window.location.hash.substring(1) == 'nuevo')
      {
         $("#modal_nuevo_proveedor").modal('show');
         document.f_nuevo_proveedor.nombre.focus();
      }
      $("#b_nuevo_proveedor").click(function(event) {
         event.preventDefault();
         $("#modal_nuevo_proveedor").modal('show');
         document.f_nuevo_proveedor.nombre.focus();
      });
   });
</script>

<div class="container-fluid" style="margin-top: 10px;">
   <div class="row">
      <div class="col-sm-5 col-xs-6">
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
            <a href="#" id="b_nuevo_proveedor" class="btn btn-sm btn-success">
               <span class="glyphicon glyphicon-plus"></span>
               <span class="hidden-xs">&nbsp; Nuevo</span>
            </a>
            <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <?php if( $value1->type=='button' ){ ?>

               <a href="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>" class="btn btn-sm btn-default"><?php echo $value1->text;?></a>
               <?php } ?>

            <?php } ?>

         </div>
      </div>
      <div class="col-sm-5 col-xs-6 text-right">
         <h2 style="margin-top: 0px;">Proveedores</h2>
      </div>
      <div class="col-sm-2 col-xs-12">
         <form name="f_custom_search" action="<?php echo $fsc->url();?>" method="post" class="form">
            <div class="input-group">
               <input class="form-control" type="text" name="query" value="<?php echo $fsc->query;?>" autocomplete="off" placeholder="Buscar">
               <span class="input-group-btn hidden-sm">
                  <button class="btn btn-primary" type="submit">
                     <span class="glyphicon glyphicon-search"></span>
                  </button>
               </span>
            </div>
         </form>
      </div>
   </div>
</div>

<div class="visible-xs">
   <br/>
</div>

<ul class="nav nav-tabs">
   <li role="presentation"<?php if( $fsc->mostrar=='todo' ){ ?> class="active"<?php } ?>>
      <a href="<?php echo $fsc->url();?>&mostrar=todo">
         <?php if( $fsc->query=='' ){ ?>

         <i class="fa fa-users" aria-hidden="true"></i>&nbsp;
         Todos <span class="badge"><?php echo $fsc->total_proveedores();?></span>
         <?php }else{ ?>

         <span class="glyphicon glyphicon-search"></span>
         <span class="hidden-xs">&nbsp; Resultados de "<?php echo $fsc->query;?>"</span>
         <?php } ?>

      </a>
   </li>
   <li role="presentation"<?php if( $fsc->mostrar=='acreedores' ){ ?> class="active"<?php } ?>>
      <a href="<?php echo $fsc->url();?>&mostrar=acreedores">
         <span class="glyphicon glyphicon-briefcase"></span>
         <span class="hidden-xs">&nbsp; Acreedores</span>
      </a>
   </li>
   <li>
      <a href="<?php echo $fsc->url();?>&mostrar=acreedores" onclick="return acreedores_help()">
         <span class="glyphicon glyphicon-question-sign"></span>
      </a>
   </li>
</ul>

<div class="table-responsive">
   <table class="table table-hover">
      <thead>
         <tr>
            <th class="text-left">Código + Nombre</th>
            <th class="text-left"><?php  echo FS_CIFNIF;?></th>
            <th class="text-left">email</th>
            <th class="text-left">Teléfono</th>
            <th class="text-left">Observaciones</th>
         </tr>
      </thead>
      <?php $loop_var1=$fsc->resultados; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

      <tr class="clickableRow" href="<?php echo $value1->url();?>">
         <td>
            <a href="<?php echo $value1->url();?>"><?php echo $value1->codproveedor;?></a>
            <?php echo $value1->nombre;?>

            <?php if( $value1->acreedor ){ ?>

            &nbsp; <span class="glyphicon glyphicon-briefcase" title="Es un acreedor"></span>
            <?php } ?>

         </td>
         <td><?php echo $value1->cifnif;?></td>
         <td><?php echo $value1->email;?></td>
         <td><?php echo $value1->telefono1;?></td>
         <td><?php echo $value1->observaciones_resume();?></td>
      </tr>
      <?php }else{ ?>

      <tr class="warning">
         <td colspan="5">
            Ningún <?php if( $fsc->mostrar=='acreedores' ){ ?>acreedor<?php }else{ ?>proveedor<?php } ?> encontrado. Pulsa el botón <b>Nuevo</b> para crear uno.
         </td>
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

<form class="form-horizontal" role="form" name="f_nuevo_proveedor" action="<?php echo $fsc->url();?>" method="post">
   <div class="modal" id="modal_nuevo_proveedor">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
               <h4 class="modal-title">Nuevo proveedor / acreedor</h4>
               <p class="help-block">
                  Los acreedores son todos aquellos proveedores a los que no les compramos mercancias.
                  Por ejemplo: proveedor de internet, teléfono, bancos...
               </p>
            </div>
            <div class="modal-body">
               <div class="form-group">
                  <label class="col-sm-2 control-label">Nombre</label>
                  <div class="col-sm-10">
                     <input type="text" name="nombre" class="form-control" autocomplete="off" required=""/>
                  </div>
               </div>
               <div class="form-group">
                  <label class="col-sm-2 control-label"><?php  echo FS_CIFNIF;?></label>
                  <div class="col-sm-3">
                     <select name="tipoidfiscal" class="form-control">
                        <?php $tiposid=$this->var['tiposid']=fs_tipos_id_fiscal();?>

                        <?php $loop_var1=$tiposid; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1;?>"><?php echo $value1;?></option>
                        <?php } ?>

                     </select>
                  </div>
                  <div class="col-sm-7">
                     <input type="text" name="cifnif" class="form-control" autocomplete="off"/>
                  </div>
               </div>
               <div class="form-group">
                  <label class="col-sm-2 control-label">
                     <a href="<?php echo $fsc->pais->url();?>">País</a>
                  </label>
                  <div class="col-sm-10">
                     <select name="pais" class="form-control">
                        <?php $loop_var1=$fsc->pais->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1->codpais;?>"<?php if( $value1->is_default() ){ ?> selected=""<?php } ?>><?php echo $value1->nombre;?></option>
                        <?php } ?>

                     </select>
                  </div>
               </div>
               <div class="form-group">
                  <label class="col-sm-2 control-label text-capitalize"><?php  echo FS_PROVINCIA;?></label>
                  <div class="col-sm-10">
                     <input type="text" name="provincia" id="ac_provincia" class="form-control" autocomplete="off"/>
                  </div>
               </div>
               <div class="form-group">
                  <label class="col-sm-2 control-label">Ciudad</label>
                  <div class="col-sm-10">
                     <input type="text" name="ciudad" class="form-control"/>
                  </div>
               </div>
               <div class="form-group">
                  <label class="col-sm-2 control-label">Cód. Postal</label>
                  <div class="col-sm-10">
                     <input type="text" name="codpostal" class="form-control"/>
                  </div>
               </div>
               <div class="form-group">
                  <label class="col-sm-2 control-label">Dirección</label>
                  <div class="col-sm-10">
                     <input type="text" name="direccion" class="form-control" autocomplete="off"/>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <div class="checkbox pull-left">
                  <label>
                     <input type="checkbox" name="acreedor" value="TRUE"/> es un <b>acreedor</b>
                  </label>
               </div>
               <button class="btn btn-sm btn-primary" type="submit">
                   <span class="glyphicon glyphicon-floppy-disk"></span> &nbsp; Guardar
                </button>
            </div>
         </div>
      </div>
   </div>
</form>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>