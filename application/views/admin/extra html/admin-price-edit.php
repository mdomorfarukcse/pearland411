 
<!-- START -->
<section>
    <div class="ad-com">
        <div class="ad-dash leftpadd">
            <div class="ud-cen">
                <div class="log-bor">&nbsp;</div>
                <span class="udb-inst">pricing details</span>
                <div class="ud-cen-s2 ud-pro-edit">
                    <h2>Edit pricing details</h2>
                        <table class="responsive-table bordered">
                            <form id="plan_type_edit" name="plan_type_edit" method="post" action="update_plan_type.html">
                            <input type="hidden" class="validate" id="plan_type_id" name="plan_type_id"
                                   value="1" required="required">
                            <tbody>
                            <tr>
                                <td>Plan name</td>
                                <td>
                                    <div class="form-group">
                                        <input type="text" id="plan_type_name"
                                               name="plan_type_name"
                                               value="Free" required="required"
                                               class="form-control">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Price</td>
                                <td>
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="plan_type_price"
                                               name="plan_type_price"
                                               value="0"
                                               onkeypress="return isNumber(event)"
                                               required="required">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Duration</td>
                                <td>
                                    <div class="form-group">
                                        <select id="plan_type_duration" name="plan_type_duration"
                                                required="required" class="form-control">
                                            <option  value="1">1 month</option>
                                            <option  value="2">2 months</option>
                                            <option  value="3">3 months</option>
                                            <option  value="6">6 months</option>
                                            <option selected value="12">1 year</option>
                                            <option  value="24">2 years</option>
                                            <option  value="36">3 years</option>
                                            <option  value="48">4 years</option>
                                            <option  value="60">5 years</option>
                                            <option  value="72">6 years</option>
                                            <option  value="84">7 years</option>
                                            <option  value="96">8 years</option>
                                            <option  value="108">9 years</option>
                                            <option  value="120">10 years</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>No of listings</td>
                                <td>
                                    <div class="form-group">
                                        <select id="plan_type_listing_count" name="plan_type_listing_count"
                                                required="required" class="form-control">
                                            <option selected value="1">1</option>
                                            <option  value="5">5</option>
                                            <option  value="10">10</option>
                                            <option  value="15">15</option>
                                            <option  value="20">20</option>
                                            <option  value="25">25</option>
                                            <option  value="30">30</option>
                                            <option  value="50">50</option>
                                            <option  value="70">70</option>
                                            <option  value="100">100</option>
                                            <option  value="200">200</option>
                                            <option  value="1000">Unlimited</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>No of products</td>
                                <td>
                                    <div class="form-group">
                                        <select id="plan_type_product_count" name="plan_type_product_count"
                                                required="required" class="form-control">
                                            <option selected value="1">1</option>
                                            <option  value="5">5</option>
                                            <option  value="10">10</option>
                                            <option  value="15">15</option>
                                            <option  value="20">20</option>
                                            <option  value="25">25</option>
                                            <option  value="30">30</option>
                                            <option  value="50">50</option>
                                            <option  value="70">70</option>
                                            <option  value="100">100</option>
                                            <option  value="200">200</option>
                                            <option  value="1000">Unlimited</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>No of events</td>
                                <td>
                                    <div class="form-group">
                                        <select id="plan_type_event_count" name="plan_type_event_count"
                                                required="required" class="form-control">
                                            <option selected value="1">1</option>
                                            <option  value="5">5</option>
                                            <option  value="10">10</option>
                                            <option  value="15">15</option>
                                            <option  value="20">20</option>
                                            <option  value="25">25</option>
                                            <option  value="30">30</option>
                                            <option  value="50">50</option>
                                            <option  value="70">70</option>
                                            <option  value="100">100</option>
                                            <option  value="200">200</option>
                                            <option  value="1000">Unlimited</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>No of blogs</td>
                                <td>
                                    <div class="form-group">
                                        <select id="plan_type_blog_count" name="plan_type_blog_count"
                                                required="required" class="form-control">
                                            <option selected value="1">1</option>
                                            <option  value="5">5</option>
                                            <option  value="10">10</option>
                                            <option  value="15">15</option>
                                            <option  value="20">20</option>
                                            <option  value="25">25</option>
                                            <option  value="30">30</option>
                                            <option  value="50">50</option>
                                            <option  value="70">70</option>
                                            <option  value="100">100</option>
                                            <option  value="200">200</option>
                                            <option  value="1000">Unlimited</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>User Points</td>
                                <td>
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="plan_type_points"
                                               name="plan_type_points"
                                               value="0"
                                               onkeypress="return isNumber(event)"
                                               required="required">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Get direct leads</td>
                                <td>
                                    <div class="form-group">
                                        <select id="plan_type_direct_lead" name="plan_type_direct_lead" class="form-control">
                                            <option  value="1">Yes</option>
                                            <option selected value="0">No</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Email notification(leads)</td>
                                <td>
                                    <div class="form-group">
                                        <select id="plan_type_email_notification" name="plan_type_email_notification" class="form-control">
                                            <option  value="1">Yes</option>
                                            <option selected value="0">No</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Verified listing</td>
                                <td>
                                    <div class="form-group">
                                        <select id="plan_type_verified" name="plan_type_verified" class="form-control">
                                            <option  value="1">Yes</option>
                                            <option selected value="0">No</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Trusted listing</td>
                                <td>
                                    <div class="form-group">
                                        <select id="plan_type_trusted" name="plan_type_trusted" class="form-control">
                                            <option  value="1">Yes</option>
                                            <option selected value="0">No</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Special offers</td>
                                <td>
                                    <div class="form-group">
                                        <select id="plan_type_offers" name="plan_type_offers" class="form-control">
                                            <option  value="1">Yes</option>
                                            <option selected value="0">No</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Photos</td>
                                <td>
                                    <div class="form-group">
                                        <select type="number" id="plan_type_photos_count"
                                                name="plan_type_photos_count" class="form-control">
                                            <option selected value="1">1</option>
                                            <option  value="2">2</option>
                                            <option  value="5">5</option>
                                            <option  value="10">10</option>
<!--                                            <option --><!-- value="15">15</option>-->
<!--                                            <option --><!-- value="20">20</option>-->
<!--                                            <option --><!-- value="1000">Unlimited</option>-->
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Videos</td>
                                <td>
                                    <div class="form-group">
                                        <select type="number" id="plan_type_videos_count"
                                                name="plan_type_videos_count" class="form-control">
                                            <option selected value="1">1</option>
                                            <option  value="2">2</option>
                                            <option  value="5">5</option>
                                            <option  value="10">10</option>
<!--                                            <option --><!-- value="15">15</option>-->
<!--                                            <option --><!-- value="20">20</option>-->
<!--                                            <option --><!-- value="1000">Unlimited</option>-->
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Review control</td>
                                <td>
                                    <div class="form-group">
                                        <select  id="plan_type_ratings" name="plan_type_ratings" class="form-control">
                                            <option  value="1">Yes</option>
                                            <option selected value="0">No</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Social media share</td>
                                <td>
                                    <div class="form-group">
                                        <select id="plan_type_social" name="plan_type_social" class="form-control">
                                            <option selected value="1">Yes</option>
                                            <option  value="0">No</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="submit" name="plan_type_submit" class="db-pro-bot-btn">Submit Changes</button>
                                </td>
                                <td></td>
                            </tr>
                            </tbody>
                        </form>
                    </table>
                </div>


            </div>

        </div>
    </div>
</section>
<!-- END -->
