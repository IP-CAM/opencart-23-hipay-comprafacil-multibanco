<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-comprafacil" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-body">


        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-comprafacil" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_cf_username; ?></label>
            <div class="col-sm-10">
              <input type="text" name="comprafacil_username" value="<?php echo $comprafacil_username; ?>" id="comprafacil_username" class="form-control" />
            </div>
          </div>



          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_cf_password; ?></label>
            <div class="col-sm-10"><input type="text" name="comprafacil_password" id="comprafacil_password" value="<?php echo $comprafacil_password; ?>" class="form-control" /></div>
          </div>



          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_cf_entity; ?></label>
            <div class="col-sm-10">
                <select name="comprafacil_entity" id="comprafacil_entity" class="form-control">
                    <option value="10241" <?php echo ($comprafacil_entity=="10241")?'selected="selected"':''; ?> >10241</option>
                    <option value="11249" <?php echo ($comprafacil_entity=="11249")?'selected="selected"':''; ?> >11249</option>
                </select>
            </div>
          </div>


          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_cf_mode; ?></label>
            <div class="col-sm-10">
                <select name="comprafacil_mode" id="comprafacil_mode" class="form-control">
                    <option value="1" <?php echo ($comprafacil_mode)?'selected="selected"':''; ?> ><?php echo $entry_cf_yes; ?></option>
                    <option value="0" <?php echo (!$comprafacil_mode)?'selected="selected"':''; ?> ><?php echo $entry_cf_no; ?></option>
                </select>
           </div>
          </div>
          

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_status; ?></label>
            <div class="col-sm-10"><select name="comprafacil_status" id="comprafacil_status" class="form-control">
                <?php if ($comprafacil_status) { ?>
                <option value="1" selected="selected"><?php echo $entry_cf_active; ?></option>
                <option value="0"><?php echo $entry_cf_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $entry_cf_active; ?></option>
                <option value="0" selected="selected"><?php echo $entry_cf_disabled; ?></option>
                <?php } ?>
              </select></div>
          </div>
          

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_sort_order; ?></label>
            <div class="col-sm-10"><input type="text" class="form-control" name="comprafacil_sort_order" id="comprafacil_sort_order" value="<?php echo $comprafacil_sort_order; ?>" size="1" /></div>
          </div>


      </form>



      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?> 