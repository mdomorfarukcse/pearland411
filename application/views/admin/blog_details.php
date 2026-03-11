<!-- START -->

<section class=" eve-deta-pg">

	<div class="container">

		<div class="eve-deta-pg-main">

			<div class="lhs" style="width: 100%;">

				<div class="img">

					<img src="<?= base_url($blog->blog_image); ?>" alt="">

				</div>

				<div class="head"> <span class="dat"><?= date('M d', strtotime($blog->created_at)); ?></span>

					<h1><?= $blog->blog_name; ?></h1>

				</div>

			</div>

			<!-- <div class="rhs">

					<div class="list-rhs-form pglist-bg pglist-p-com">

						<div class="quote-pop">

							<h3>Register Now</h3>

							<div id="event_detail_enq_success" class="log" style="display: none;">

								<p>Your Enquiry Is Submitted Successfully</p>

							</div>

							<div id="event_detail_enq_same" class="log" style="display: none;">

								<p>You cannot make enquiry on your own event</p>

							</div>

							<div id="event_detail_enq_fail" class="log" style="display: none;">

								<p>Something Went Wrong!!!</p>

							</div>

							<form method="post" name="event_detail_enquiry_form" id="event_detail_enquiry_form">

								<input type="hidden" class="form-control" name="event_id" value="18" placeholder="" required>

								<input type="hidden" class="form-control" name="listing_user_id" value="41" placeholder="" required>

								<input type="hidden" class="form-control" name="enquiry_sender_id" value="37" placeholder="" required>

								<input type="hidden" class="form-control" name="enquiry_source" value="Website" placeholder="" required>

								<div class="form-group ic-user">

									<input type="text" name="enquiry_name" value="" required="required" class="form-control" placeholder="Enter name*">

								</div>

								<div class="form-group ic-eml">

									<input type="email" class="form-control" placeholder="Enter email*" required="required" value="" name="enquiry_email" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address">

								</div>

								<div class="form-group ic-pho">

									<input type="text" class="form-control" value="" name="enquiry_mobile" placeholder="Enter mobile number *" pattern="[7-9]{1}[0-9]{9}" title="Phone number starting with 7-9 and remaing 9 digit with 0-9" required>

								</div>

								<div class="form-group">

									<textarea class="form-control" rows="3" name="enquiry_message" placeholder="Enter your query or message"></textarea>

								</div>

								<input type="hidden" id="source">

								<button type="submit" name="enquiry_submit" class="btn btn-primary">Submit</button>

							</form>

						</div>

					</div>

				</div> -->

		</div>

	</div>

</section>

<!--END-->

<!-- START -->

<section class=" eve-deta-body">

	<div class="container">

		<div class="eve-deta-body-main">

			<div class="lhs">
				<p><?= strip_tags(html_entity_decode($blog->blog_description)); ?></p>
			</div>

			<div class="rhs">

				<div class="sec-1">

					<!--<h4>Event information:</h4>-->

					<!--<ul>-->

					<!--	<li><b>Name</b>: <? //= $event->event_name;
												?></li>-->

					<!--	<li><b>Date</b>: <? //= date('d, M Y',strtotime($event->event_start_date));
												?></li>-->

					<!--	<li><b>Time</b>: <? //= $event->event_time;
												?></li>-->

					<!--	<li><b>Address</b>: <? //= $event->event_address;
												?></li>-->

					<!--	<li><b>Contact Person</b>: <? //= $event->event_contact_name;
														?></li>-->

					<!--	<li><b>Phone</b>: <? //= $event->event_mobile;
												?></li>-->

					<!--	<li><b>Email</b>: <? //= $event->event_email;
												?></li>-->

					<!--	<li><b>Website</b>: <? //= $event->event_website;
												?></li>-->

					<!--</ul>-->

				</div>

				<div class="sec-2">

					<!--<h4>Location</h4>-->

					<!--                        <iframe src="-->

					<!--" allowfullscreen></iframe>-->

					<? //=$event->event_map;
					?>

				</div>

				<!--<div class="sec-3">-->

				<!--	<div class="ud-lhs-s1">-->

				<!--		<img src="images/user/7.jpg" alt="">-->

				<!--		<h4>Claude D Dial</h4>-->

				<!--		<b>Joined on 07, Jan 2020</b>-->

				<!--		<a target="_blank" href="profile.html" class="fclick">&nbsp;</a>-->

				<!--	</div>-->

				<!--</div>-->

			</div>

		</div>

		<div class="pro-bot-shar">

			<h4>Share this Blog</h4>

			<ul>

				<li>

					<div class="sh-pro-shar sh-pro-face"> <a target="_blank" href="https://www.facebook.com/sharer/sharer.html?u=event/lunar-new-year-2020?src=facebook&quote=Lunar New Year 2020">Facebook</a>

					</div>

				</li>

				<li>

					<div class="sh-pro-shar sh-pro-twi"> <a target="_blank" href="http://twitter.com/share?text=Lunar New Year 2020&url=http%3A%2F%2Flocalhost%2Fdirectory%2Fbizbook%2Fevent%2Flunar-new-year-2020%3Fsrc%3Dtwitter">Twitter</a>

					</div>

				</li>

				<li>

					<div class="sh-pro-shar sh-pro-what"> <a target="_blank" href="whatsapp://send?text=http%3A%2F%2Flocalhost%2Fdirectory%2Fbizbook%2Fevent%2Flunar-new-year-2020%3Fsrc%3Dwhatsapp" data-action="share/whatsapp/share">WhatsApp</a>

					</div>

				</li>

				<li>

					<div class="sh-pro-shar sh-pro-link"> <a target="_blank" href="https://www.linkedin.com/sharing/share-offsite/?url=http%3A%2F%2Flocalhost%2Fdirectory%2Fbizbook%2Fevent%2Flunar-new-year-2020%26%26src%3Dlinkedin">Linkedin</a>

					</div>

				</li>

				<li>

					<div style="background-color: #da271a" class="sh-pro-shar sh-pro-pin"> <a target="_blank" href="https://www.pinterest.com/pin/create/bookmarklet/?media=images/events/18945man-with-fireworks-769525.jpg&url=http%3A%2F%2Flocalhost%2Fdirectory%2Fbizbook%2Fevent%2Flunar-new-year-2020%26%26src%3Dpinterest&description=Lunar New Year 2020">Pinterest</a>

					</div>

				</li>

			</ul>

		</div>

		<div class="pro-rel-events">

			<h4>Related Blogs</h4>

			<div class="event-body">

				<div class="us-ppg-com">

					<ul>

						<li>

							<div class="pro-eve-box">

								<div>

									<img src="images/blogs/blog5.jpg">

								</div>

								<div> <span>18 <b>Mar</b></span>

									<h2>Surfing Competition Hawaii</h2>

									<p>4754 Grove Avenue, Hawaii</p>

								</div> <a href="events.html" class="fclick">&nbsp;</a>

							</div>

						</li>

						<li>

							<div class="pro-eve-box">

								<div>

									<img src="images/services/10.jpg">

								</div>

								<div> <span>18 <b>Jan</b></span>

									<h2>Food eating challenge</h2>

									<p>1297 Stuart Street, Pennsylvania</p>

								</div> <a href="events.html" class="fclick">&nbsp;</a>

							</div>

						</li>

						<li>

							<div class="pro-eve-box">

								<div>

									<img src="images/services/11.jpg">

								</div>

								<div> <span>18 <b>Jan</b></span>

									<h2>College Volley Ball Tournaments 2021</h2>

									<p>Lynn B Morgan, Garden City, New York</p>

								</div> <a href="events.html" class="fclick">&nbsp;</a>

							</div>

						</li>

						<li>

							<div class="pro-eve-box">

								<div>

									<img src="images/services/11.jpg">

								</div>

								<div> <span>25 <b>Jan</b></span>

									<h2>States Soccer World Cup 2022</h2>

									<p>2826 Lamberts Branch Road, Miami, Florida</p>

								</div><a href="events.html" class="fclick">&nbsp;</a>

							</div>

						</li>

					</ul>

				</div>

			</div>

		</div>

	</div>

</section>

<!--END-->