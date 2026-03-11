
<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">All Slider</span>
        <div class="ud-cen-s2 ad-table-inn">
          <h2>All Slider Photos</h2>
          <?php if($this->session->flashdata('message')){ ?>
            <div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
          <?php } ?>
          <table class="responsive-table bordered">
            <thead>
              <tr>
                <th>No</th>
                <th>Slider</th>
                <th>Link</th>
                <th>Edit</th>
                <th>Delete</th>
              </tr>
            </thead>
            <tbody>
              <?php 

              if($all_slider){
                $i = 0;
                foreach($all_slider as $slider){
                  $i++;

              ?>
              <tr>
                <td><?= $i ?></td>
                <td>
                  <img src="<?= base_url((!empty($slider->slider_photo)) ? $slider->slider_photo : 'assets/images/no_photo.jpg');?>" alt="">
                </td>
                <td><?= base_url($slider->slider_link);?></td>
                <td><a href="<?= base_url('admin/slider_edit/'.$slider->slider_id);?>" class="db-list-edit">Edit</a></td>
                <td><a class="db-list-edit" onclick="javascript:deleteConfirm('<?php echo base_url().'admin/slider_delete/'.$slider->slider_id;?>');"  href="#">Delete</a></td>
              </tr>
              <?php } } ?>
              
              
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</section>
<!-- END -->

