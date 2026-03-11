
<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
     <section class="login-reg">
      <div class="container">
       <div class="row">
        <div class="login-main add-list posr">
         <div class="log-bor">&nbsp;</div>
         <span class="udb-inst">Duplicate listing</span>
         <div class="log log-1">
            <?php if($this->session->flashdata('message')){ ?>
                <div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
            <?php } ?>
          <div class="login">
            <h4>Create New Duplicate Listings</h4>
            <form name="duplicate_listing_form" action="" id="duplicate_listing_form" method="post" class="cre-dup-form cre-dup-form-show">
              <!--FILED START-->
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <select name="listing_id" id="listing_id" class="form-control"
                    required="required">
						<option value="" disabled selected>Select Listing Name</option>
						<?php
							if (count($listings) > 0) {
								foreach ($listings as $value) {
						?>
						<option value="<?= $value['listing_id']; ?>"><?= $value['listing_name'] ;?></option>
						<?php	
								}
							}
						?>

                  </select>
                </div>
              </div>
            </div>
            <!--FILED END-->
            <!--FILED START-->
            <div class="row">
             <div class="col-md-12">
               <div class="form-group">
                 <select name="user_id" id="user_code" class="form-control"
                 required="required">
                 <option value="" disabled selected>Choose User</option>
				<?php
					if(count($users) > 0){
						foreach ($users as $value) {
				?>
				<option value="<?= $value->user_id;?>"><?= $value->name;?></option>
				<?php	
						}
					}
				?>
                 
               </select>
             </div>
           </div>
         </div>
         <!--FILED END-->
         <!--FILED START-->
         <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <input type="text" name="listing_name" required="required" class="form-control" placeholder="Listing name *">
            </div>
          </div>
        </div>
        <!--FILED END-->
        <button type="submit" name="listing_submit" class="btn btn-primary">Create Now</button>
      </form>
      <div class="col-md-12">
        <a href="<?= base_url('admin/all-listings');?>" class="skip">Go to Listing dashboard >></a>
      </div>

    </div>
  </div>
</div>
</div>
</div>
</section>

</div>
</div>
</section>
<!-- END -->    
