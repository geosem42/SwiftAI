<template>
	<app-layout title="Image">
		<nav class="secondary_nav sticky_horizontal_2">
				<div class="container">
					<ul class="clearfix">
						<li><a href="#description" class="active">Description</a></li>
						<li><a href="#reviews">Reviews</a></li>
						<li><a href="#sidebar">Booking</a></li>
					</ul>
				</div>
			</nav>

			<div class="container margin_60_35">
				<div class="row">
					<div class="col-lg-8">
						<div class="add_bottom_30">
							<div class="item text-center">
								<img :src="fetchImage" class="img-fluid" alt="">
							</div>
						</div>
						<section id="description">
							<div class="detail_title_1">
								<h1>Hotel Mariott</h1>
							</div>
							<p>Per consequat adolescens ex, cu nibh commune <strong>temporibus vim</strong>, ad sumo viris eloquentiam
								sed. Mea appareat omittantur eloquentiam ad, nam ei quas oportere democritum. Prima causae admodum id est,
								ei timeam inimicus sed. Sit an meis aliquam, cetero inermis vel ut. An sit illum euismod facilisis,
								tamquam vulputate pertinacia eum at.</p>
							<p>Cum et probo menandri. Officiis consulatu pro et, ne sea sale invidunt, sed ut sint
								<strong>blandit</strong> efficiendi. Atomorum explicari eu qui, est enim quaerendum te. Quo harum viris
								id. Per ne quando dolore evertitur, pro ad cibo commune.</p>
								<p>Per consequat adolescens ex, cu nibh commune <strong>temporibus vim</strong>, ad sumo viris eloquentiam
								sed. Mea appareat omittantur eloquentiam ad, nam ei quas oportere democritum. Prima causae admodum id est,
								ei timeam inimicus sed. Sit an meis aliquam, cetero inermis vel ut. An sit illum euismod facilisis,
								tamquam vulputate pertinacia eum at.</p>
								<p>Per consequat adolescens ex, cu nibh commune <strong>temporibus vim</strong>, ad sumo viris eloquentiam
								sed. Mea appareat omittantur eloquentiam ad, nam ei quas oportere democritum. Prima causae admodum id est,
								ei timeam inimicus sed. Sit an meis aliquam, cetero inermis vel ut. An sit illum euismod facilisis,
								tamquam vulputate pertinacia eum at.</p>
								<p>Per consequat adolescens ex, cu nibh commune <strong>temporibus vim</strong>, ad sumo viris eloquentiam
								sed. Mea appareat omittantur eloquentiam ad, nam ei quas oportere democritum. Prima causae admodum id est,
								ei timeam inimicus sed. Sit an meis aliquam, cetero inermis vel ut. An sit illum euismod facilisis,
								tamquam vulputate pertinacia eum at.</p>
						</section>
						<!-- /section -->
					</div>
					<!-- /col -->

					<aside class="col-lg-4" id="sidebar">
						<div class="box_detail booking">
							<div class="price">
								<span>45$ <small>person</small></span>
								<div class="score"><span>Good<em>350 Reviews</em></span><strong>7.0</strong></div>
							</div>

							<div class="form-group" id="input-dates">
								<input class="form-control" type="text" name="dates" placeholder="When..">
								<i class="icon_calendar"></i>
							</div>

							<div class="dropdown">
								<a href="#" data-bs-toggle="dropdown">Guests <span id="qty_total">0</span></a>
								<div class="dropdown-menu dropdown-menu-right">
									<div class="dropdown-menu-content">
										<label>Adults</label>
										<div class="qty-buttons">
											<input type="button" value="+" class="qtyplus" name="adults">
											<input type="text" name="adults" id="adults" value="0" class="qty">
											<input type="button" value="-" class="qtyminus" name="adults">
										</div>
									</div>
									<div class="dropdown-menu-content">
										<label>Childrens</label>
										<div class="qty-buttons">
											<input type="button" value="+" class="qtyplus" name="child">
											<input type="text" name="child" id="child" value="0" class="qty">
											<input type="button" value="-" class="qtyminus" name="child">
										</div>
									</div>
								</div>
							</div>
							<!-- /dropdown -->

							<div class="form-group clearfix">
								<div class="custom-select-form">
									<select class="wide">
										<option>Room Type</option>
										<option>Single Room</option>
										<option>Double Room</option>
										<option>Suite Room</option>
									</select>
								</div>
							</div>
							<a :href="signedURL" class=" add_top_30 btn_1 full-width purchase" download>Download</a>
							<a href="wishlist.html" class="btn_1 full-width outline wishlist"><i class="icon_heart"></i> Add to
								wishlist</a>
							
							<div class="text-center"><small>No money charged in this step</small></div>
						</div>
						<ul class="share-buttons">
							<li><a class="fb-share" href="#0"><i class="social_facebook"></i> Share</a></li>
							<li><a class="twitter-share" href="#0"><i class="social_twitter"></i> Share</a></li>
							<li><a class="gplus-share" href="#0"><i class="social_googleplus"></i> Share</a></li>
						</ul>
					</aside>
			</div>
			<!-- /row -->
		</div>
</app-layout></template>

<script>
import { defineComponent } from "vue"
import AppLayout from "@/Layouts/AppLayout.vue"
import axios from 'axios'

export default defineComponent({
	props: {
		image: Object,
		signedURL: String
	},
	components: {
		AppLayout
	},
	data() {
		return {

		}
	},
	methods: {

	},
	mounted() {

	},
	computed: {
		fetchImage() {
			const imageUrl = '/storage/' + this.image.original;

      		return imageUrl;
		},
	},
});
</script>