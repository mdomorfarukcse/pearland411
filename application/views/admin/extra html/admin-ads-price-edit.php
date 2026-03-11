
<!-- START -->
<section>
    <div class="ad-com">
        <div class="ad-dash leftpadd">
            <section class="login-reg">
                <div class="container">
                    <div class="row">
                        <div class="login-main add-list posr">
                            <div class="log-bor">&nbsp;</div>
                            <span class="udb-inst">Ad details</span>
                            <div class="log log-1">
                                <div class="login">
                                    <h4>Ad Pricing and other details</h4>                                                                        <form name="ads_price_form" id="ads_price_form" method="post" action="update_ads_price.html" enctype="multipart/form-data">
                                        <input type="hidden" class="validate" id="all_ads_price_id" name="all_ads_price_id" value="1" required="required">
                                        <input type="hidden" class="validate" id="ad_price_photo_old" name="ad_price_photo_old" value="ad-size.png" required="required">
                                        <ul>
                                            <li>
                                                <!--FILED START-->
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="ad_price_name"
                                                                   value="Home page Bottom" placeholder="Ad position name" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--FILED END-->
                                                <!--FILED START-->
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="ad_price_size"
                                                                   value="728 X 90 px"
                                                                   placeholder="Ad image size" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--FILED END-->
                                                <!--FILED START-->
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="ad_price_cost"
                                                                   value="70"
                                                                   onkeypress="return isNumber(event)"
                                                                   placeholder="Ad cost" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--FILED END-->
                                                <!--FILED START-->
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label>Choose Ad preview image</label>
                                                            <input type="file" name="ad_price_photo" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--FILED END-->
                                                <!--FILED START-->
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <select name="ad_price_status" class="form-control">
                                                                <option selected  value="Active">Active</option>
                                                                <option  value="Inactive">InActive</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--FILED END-->
                                            </li>
                                        </ul>
                                        <!--FILED START-->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <button type="submit" name="ad_price_submit" class="btn btn-primary">update</button>
                                            </div>
                                            <div class="col-md-12">
                                                <a href="admin-current-ads.html" class="skip">Go to Current Ads >></a>
                                            </div>
                                        </div>
                                        <!--FILED END-->
                                    </form>
<!--                                    <div class="ud-notes">-->
<!--                                        <p><b>Notes:</b> Hi, Before submit your <b>Ads</b> please check the <b>available-->
<!--                                                date</b> because previous Ads running in same date. Kindly check this-->
<!--                                            manually</p>-->
<!--                                    </div>-->
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

