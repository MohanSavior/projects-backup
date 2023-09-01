<?php
/*
* Template Name: Surf Skate V2
*/ 
get_header();
?>
<style>
	.et-l.et-l--header { 
		display: none;
	}
</style>
<!-- .Header Starts Here -->
<header class="header" id="DesktopHeaderOnly">
	<div class="container">
		<!-- .Main Nav Starts Here -->
		<ul class="main-nav">
			<li>
				<a href="<?=site_url()?>"><i class="bi bi-chevron-left"></i> Back To Home</a>
			</li>
			<li>
				<a class="navbar-brand" href="<?=site_url()?>">
					<img src="<?=site_url('/wp-content/uploads/2022/03/Surfskate_Love_horizontal-03-copy-1.png')?>" alt="Site Logo1">
				</a>
			</li>
			<li>
				<a href="<?=site_url('my-account')?>"><i class="bi bi-person-circle"></i> My Account</a>
			</li>
		</ul><!-- /.Main Nav Ends Here -->
	</div>
</header><!-- /.Header Ends Here -->
<!-- .Header Starts Here -->
<header class="header" id="MobileHeaderOnly">
	<div class="container">
		<div class="logo-block">
			<a class="navbar-brand" href="<?=site_url()?>">
				<img src="<?=site_url('/wp-content/uploads/2022/03/Surfskate_Love_horizontal-03-copy-1.png')?>" alt="Site Logo1">
			</a>
		</div>
		<!-- .Main Nav Starts Here -->
		<ul class="main-nav">
			<li>
				<a href="<?=site_url()?>"><i class="bi bi-chevron-left"></i> Back To Home</a>
			</li>
			<li>
				<a href="<?=site_url('my-account')?>"><i class="bi bi-person-circle"></i> My Account</a>
			</li>
		</ul><!-- /.Main Nav Ends Here -->
	</div>
</header><!-- /.Header Ends Here -->
<!-- .Step Form Starts Here -->
<div class="card stepwizard" id="mainWizard">
	<!-- .Main Form Starts Here -->
	<form id="msform">
		<!-- .Number Progress Bar Starts Here -->
		<div class="numberCircleProgressbar">
			<ul id="progressbar">
				<li class="showOne active">
					<a href="JavaScript:void(0);" type="button" class="btn-circle" data-id="surf-step-01">1</a>
					<h5>Stance WIdth <span class="daynamic-val"></span></h5>
				</li>
				<li>
					<a href="JavaScript:void(0);" type="button" class="btn-circle" data-id="surf-step-02">2</a>
					<h5>Skill Level <span class="daynamic-val"></span></h5>
				</li>
				<li>
					<a href="JavaScript:void(0);" type="button" class="btn-circle" data-id="surf-step-03">3</a>
					<h5>Budget <span class="daynamic-val"></span></h5>
				</li>
				<li>
					<a href="JavaScript:void(0);" type="button" class="btn-circle" data-id="surf-step-04">4</a>
					<h5>Primary Purpose <span class="daynamic-val"></span></h5>
				</li>
				<li>
					<a href="JavaScript:void(0);" type="button" class="btn-circle" data-id="surf-step-05">5</a>
					<h5>Board Type <span class="daynamic-val"></span></h5>
				</li>
				<li>
					<a href="JavaScript:void(0);" type="button" class="btn-circle" data-id="surf-step-06">6</a>
					<h5>Riding Style <span class="daynamic-val"></span></h5>
				</li>
				<li>
					<a href="JavaScript:void(0);" type="button" class="btn-circle" data-id="surf-step-07">7</a>
					<h5>Truck Feel <span class="daynamic-val"></span></h5>
				</li>
				<li>
					<a href="JavaScript:void(0);" type="button" class="btn-circle" data-id="surf-step-08">8</a>
					<h5>Distance <span class="daynamic-val"></span></h5>
				</li>
				<li>
					<a href="JavaScript:void(0);" type="button" class="btn-circle" data-id="surf-step-09">9</a>
					<h5>Information<span class="daynamic-val"></span></h5>
				</li>
				<li>
					<a href="JavaScript:void(0);" type="button" class="btn-circle" data-id="surf-step-10">10</a>
					<h5>Results <span class="daynamic-val"></span></h5>
				</li>
			</ul><!-- /.Number Progress Bar Ends Here -->
			<!-- .Progress Bar Starts Here -->
			<div class="progress">
				<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"><span class="count"></span></div>
			</div><!-- .Progress Bar Ends Here -->
		</div>
		<div class="container">
			<!-- .Step 1 Starts Here -->
			<fieldset class="step-fieldset step-01" id="surf-step-01">
				<!-- .Step Head Starts Here -->
				<div class="step-head">
					<h1>Question #1</h1>
					<h2>What is your <span>stance width</span>?</h2>
					<p>If you don't know how to determine this, watch this video.</p>
				</div>
				<!-- /.Step Head Ends Here -->
				<!-- .Step Video Starts Here --->
				<div class="step-video">
					<script src="https://fast.wistia.com/embed/medias/ik1zerfucj.jsonp" async></script><script src="https://fast.wistia.com/assets/external/E-v1.js" async></script><div class="wistia_responsive_padding" style="padding:56.25% 0 0 0;position:relative;"><div class="wistia_responsive_wrapper" style="height:100%;left:0;position:absolute;top:0;width:100%;"><div class="wistia_embed wistia_async_ik1zerfucj videoFoam=true" style="height:100%;position:relative;width:100%"><div class="wistia_swatch" style="height:100%;left:0;opacity:0;overflow:hidden;position:absolute;top:0;transition:opacity 200ms;width:100%;"><img src="https://fast.wistia.com/embed/medias/ik1zerfucj/swatch" style="filter:blur(5px);height:100%;object-fit:contain;width:100%;" alt="" aria-hidden="true" onload="this.parentNode.style.opacity=1;" /></div></div></div></div>
				</div><!-- /.Step Video Ends Here --->
				<!-- .Step Input Fields Start Here -->
				<div class="form-card">
					<h4 for="stance_width">Select your <span>stance width:</span></h4>
					<div class="custom-select">
						<select class="selectPicker" name="stance_width" id="stance_width" title="Choose Stance Width" data-size="6">
							<option value="11">11</option>
							<option value="12">12</option>
							<option value="13">13</option>
							<option value="14">14</option>
							<option value="15">15</option>
							<option value="16">16</option>
							<option value="17">17</option>
							<option value="18">18</option>
							<option value="19">19</option>
							<option value="20">20</option>
							<option value="21">21</option>
						</select>
						<p>in</p>
					</div>
				</div><!-- /.Step Input Fields Ends Here -->
				<!-- .Button Next Starts Here -->
				<button type="button" name="next" class="btn next action-button">Next step</button>
				<!-- /.Button Next Ends Here -->
				<!-- .Button Previous Starts Here -->
				<!-- <button type="button" name="previous" class="btn previous action-button-previous"><i class="icon-arrow-left"></i> Back</button>/.Button Previous Ends Here -->
			</fieldset>
			<!-- /.Step 1 Ends Here -->
			<!------------------------------------------------------------------------------>
			<!-- .Step 2 Starts Here -->
			<fieldset class="step-fieldset step-02 step-002" id="surf-step-02">
				<!-- .Step Head Starts Here -->
				<div class="step-head">
					<h1>Question #2</h1>
					<h2>How would you rank your <span>experience and skill level</span> with <br> skateboarding, longboarding, or other board sports?</h2>
				</div>
				<!-- /.Step Head Ends Here -->
				<!-- .Step Input Fields Starts Here -->
				<div class="form-card">
					<h4 for="required_skill_level">Select your <span>experience:</span></h4>
					<label for="advanced" class="control control--radio">Advanced
						<input data-subtitle="Advanced" id="advanced" type="radio" name="required_skill_level" value="advanced"/>
						<div class="control__indicator"></div>
					</label>
					<label for="intermediate" class="control control--radio">Intermediate
						<input data-subtitle="Intermediate" id="intermediate" type="radio" name="required_skill_level" value="intermediate"/>
						<div class="control__indicator"></div>
					</label>
					<label for="beginner" class="control control--radio">Beginner
						<input data-subtitle="Beginner" id="beginner" type="radio" name="required_skill_level" value="beginner"/>
						<div class="control__indicator"></div>
					</label>
				</div><!-- /.Step Input Fields Ends Here -->
				<!-- .Button Next Starts Here -->
				<button type="button" name="next" class="btn next action-button">Next step</button><!-- /.Button Next Ends Here -->
				<!-- .Button Previous Starts Here -->
				<button type="button" name="previous" class="btn previous action-button-previous"><i class="icon-arrow-left"></i> Back</button><!-- /.Button Previous Ends Here -->
			</fieldset><!-- /.Step 2 Ends Here -->
			<!------------------------------------------------------------------------------>
			<!-- .Step 3 Starts Here -->
			<fieldset class="step-fieldset step-02 step-03 step-003" id="surf-step-03">
				<!-- .Step Head Starts Here -->
				<div class="step-head">
					<h1>Question #3</h1>
					<h2>What is your <span>budget?</span></h2>
				</div>
				<!-- /.Step Head Ends Here -->
				<!-- .Step Input Fields Starts Here -->
				<div class="form-card"> 
					<h4 for="price">Select your <span>budget:</span></h4>
					<?php echo do_shortcode('[surfskate_budget]'); ?>
					<!-- <label for="price_520" class="control control--radio">Up to $520
						<input data-subtitle="Up to $520" id="price_520" type="radio" name="price" value="520"/>
						<div class="control__indicator"></div>
					</label> -->
				</div>
				<!-- /.Step Input Fields Ends Here -->
				<!-- .Button Next Starts Here -->
				<button type="button" name="next" class="btn next action-button">Next step</button><!-- /.Button Next Ends Here -->
				<!-- .Button Previous Starts Here -->
				<button type="button" name="previous" class="btn previous action-button-previous"><i class="icon-arrow-left"></i> Back</button><!-- /.Button Previous Ends Here -->
			</fieldset><!-- /.Step 3 Ends Here -->
			<!------------------------------------------------------------------------------>
			<!-- .Step 4 Starts Here -->
			<fieldset class="step-fieldset step-04" id="surf-step-04">
				<!-- .Step Head Starts Here -->
				<div class="step-head">
					<h1>Question #4</h1>
					<h2 for="purpose">What is your <span>primary purpose</span> for using a surfskate?</h2>
				</div><!-- /.Step Head Ends Here -->
				<!-- .Step Input Fields Starts Here -->
				<div class="form-card">
					<div class="row">
						<div class="col-sm-3">
							<label for="surf_trainer" class="control control--radio label-vertical-card without-image">
								<!--span class="round-img">
									<img src="<!-?=site_url('wp-content/uploads/2022/04/step-04-op-01.png')?>" alt="Surf Trainer">
								</span-->
								<span class="head">Surf Trainer</span>
								<span class="text">I'm a surfer and I want a <br> surfskate for technical surf <br> training.</span>
								<span class="btn btn-choose">Choose</span>
								<input data-subtitle="Surf Trainer" id="surf_trainer" type="radio" name="purpose" value="surf_trainer"/>
								<div class="control__indicator"></div>
							</label>
						</div>
						<div class="col-sm-3">
							<label for="both_more_surf_training" class="control control--radio label-vertical-card without-image">
								<!--span class="round-img">
									<img src="<!-?=site_url('wp-content/uploads/2022/04/step-04-op-04.png')?>" alt="Surf Trainer">
								</span-->
								<span class="head mb-0">Surf Trainer</span>
								<span class="sub-head"><i>+</i> Street Cruiser</span>
								<span class="text">Some of both, but more surf <br> training than street cruising.</span>
								<span class="btn btn-choose">Choose</span>
								<input data-subtitle="Surf Trainer<span><i>+</i> Street Cruiser</span>" id="both_more_surf_training" type="radio" name="purpose" value="both_more_surf_training"/>
								<div class="control__indicator"></div>
							</label>
						</div>
						<div class="col-sm-3">
							<label for="both_more_street_cruising" class="control control--radio label-vertical-card without-image">
								<!--span class="round-img">
									<img src="<!-?=site_url('wp-content/uploads/2022/04/step-04-op-03.png')?>" alt="Street Cruiser">
								</span-->
								<span class="head mb-0">Street Cruiser</span>
								<span class="sub-head"><i>+</i> Surf Trainer</span>
								<span class="text">Some of both, but more <br> street cruising than surf <br> training.</span>
								<span class="btn btn-choose">Choose</span>
								<input data-subtitle="Street Cruiser<span><i>+</i> Surf Trainer</span>" id="both_more_street_cruising" type="radio" name="purpose" value="both_more_street_cruising"/>
								<div class="control__indicator"></div>
							</label>
						</div>
						<div class="col-sm-3">
							<label for="street_cruiser" class="control control--radio label-vertical-card without-image">
								<!--span class="round-img">
									<img src="<!-?=site_url('wp-content/uploads/2022/04/step-04-op-02.png')?>" alt="Street Cruiser">
								</span-->
								<span class="head">Street Cruiser</span>
								<span class="text">I'm not a surfer. I just want to <br> pump, carve, and cruise the <br> streets on a surfskate and <br> have fun.</span>
								<span class="btn btn-choose">Choose</span>
								<input data-subtitle="Street Cruiser" id="street_cruiser" type="radio" name="purpose" value="street_cruiser"/>
								<div class="control__indicator"></div>
							</label>
						</div>
					</div>
				</div><!-- /.Step Input Fields Ends Here -->
				<!-- .Button Next Starts Here -->
				<button type="button" name="next" class="btn next action-button">Next step</button><!-- /.Button Next Ends Here -->
				<!-- .Button Previous Starts Here -->
				<button type="button" name="previous" class="btn previous action-button-previous"><i class="icon-arrow-left"></i> Back</button><!-- /.Button Previous Ends Here -->
			</fieldset><!-- /.Step 4 Ends Here -->
			<!------------------------------------------------------------------------------>
			<!-- .Step 5 Starts Here -->
			<fieldset class="step-fieldset step-04 step-06" id="surf-step-05">
				<!-- .Step Head Starts Here -->
				<div class="step-head">
					<h1>Question #5</h1>
					<h2 for="riding_style">What <span>type of board</span> are you looking for?</h2>
				</div><!-- /.Step Head Ends Here -->
				<!-- .Step Input Fields Starts Here -->
				<div class="form-card">
					<div class="row">
						<div class="col-sm-4">
							<label for="performance_sportcar" class="control control--radio label-vertical-card">
								<span class="round-img">
									<img src="<?=site_url('wp-content/uploads/2022/04/step-06-op-01.png')?>" alt="Performance Sports Car">
								</span>
								<span class="head">Performance "Sports Car"</span>
								<span class="text">Tighter turning radius, sharper <br> lines, more "locked-in" feel on the <br> deck, for shorter distances and <br> tight, technical maneuvers such as <br> snaps and slides, better for park <br> and bowl riding.</span>
								<span class="btn btn-choose">Choose</span>
								<input data-subtitle="Performance 'Sports Car'" id="performance_sportcar" type="radio" name="surfskate_to_feel" value="performance_sportcar" />
								<div class="control__indicator"></div>
							</label>
						</div>
						<div class="col-sm-4">
							<label for="cruising_sedan" class="control control--radio label-vertical-card">
								<span class="round-img">
									<img src="<?=site_url('wp-content/uploads/2022/04/step-06-op-02.png')?>" alt="Cruising Sedan"-->
								</span>
								<span class="head">Cruising "Sedan"</span>
								<span class="text">Wider turning radius, flowier lines, <br> more room to move around on the <br> deck, for longer distances, more <br> forward momentum with each <br> pump, better for street cruising.</span>
								<span class="btn btn-choose">Choose</span>
								<input data-subtitle="Cruising 'Sedan'" id="cruising_sedan" type="radio" name="surfskate_to_feel" value="cruising_sedan"/>
								<div class="control__indicator"></div>
							</label>
						</div>
						<div class="col-sm-4">
							<label for="novelty_longboard" class="control control--radio label-vertical-card">
								<span class="round-img">
									<img src="<?=site_url('wp-content/uploads/2022/04/step-06-op-03.png')?>" alt="Surfing Longboard">
								</span>
								<span class="head">Surfing Longboard</span>
								<span class="text">Much wider wheelbase than your <br> stance, very wide turning radius <br> and lines, used for long-distance <br> cruising, cross-stepping, practicing <br> longboard surfing.</span>
								<span class="btn btn-choose">Choose</span>
								<input data-subtitle="Surfing Longboard" id="novelty_longboard" type="radio" name="surfskate_to_feel" value="novelty_longboard"/>
								<div class="control__indicator"></div>
							</label>
						</div>
					</div>
				</div><!-- /.Step Input Fields Ends Here -->
				<!-- .Button Next Starts Here -->
				<button type="button" name="next" class="btn next action-button">Next step</button><!-- /.Button Next Ends Here -->
				<!-- .Button Previous Starts Here -->
				<button type="button" name="previous" class="btn previous action-button-previous"><i class="icon-arrow-left"></i> Back</button><!-- /.Button Previous Ends Here -->
			</fieldset><!-- /.Step 5 Ends Here -->
			<!------------------------------------------------------------------------------>
			<!-- .Step 6 Starts Here -->
			<fieldset class="step-fieldset step-05" id="surf-step-06">
				<!-- .Step Head Starts Here -->
				<div class="step-head">
					<h1>Question #6</h1>
					<h2 for="riding_describe">Which <span>riding style</span> best describes you?</h2>
				</div><!-- /.Step Head Ends Here -->
				<!-- .Step Input Fields Starts Here -->
				<div class="form-card">
					<label for="technical_surf_training_riding" class="control control--radio label-horizontal-card without-image">
						<!--span class="round-img">
							<img src="<!-?=site_url('wp-content/uploads/2022/04/step-05-op-01.png')?>" alt="Technical Surf Trainer">
						</span-->
						<span class="head">Technical Surf Trainer</span>
						<span class="text">I want to stay in small areas and practice technical <br> surf maneuvers to improve my surfing. I want my <br> surfskate to be very responsive to upper body...</span>
						<span class="btn btn-show" type="button" data-bs-toggle="modal" data-bs-target="#board-type-01">Show More</span>
						<span class="btn btn-choose">Choose</span>
						<input data-subtitle="Technical Surf Trainer" id="technical_surf_training_riding" type="radio" name="riding_style" value="technical_surf_training_riding"/>
						<div class="control__indicator"></div>
					</label>
					<label for="mellow_cruiser" class="control control--radio label-horizontal-card without-image">
						<!--span class="round-img">
							<img src="<!-?=site_url('wp-content/uploads/2022/04/step-05-op-02.png')?>" alt="Mellow Street Cruiser">
						</span-->
						<span class="head">Mellow Street Cruiser</span>
						<span class="text">I'm a smooth, mellow, cruising rider. I want my ride <br> to feel smooth, soft, and glidey. I want the feeling <br> of relaxed freedom. I like taking my time and...</span>
						<span class="btn btn-show" type="button" data-bs-toggle="modal" data-bs-target="#board-type-02">Show More</span>
						<span class="btn btn-choose">Choose</span>
						<input data-subtitle="Mellow Street Cruiser" id="mellow_cruiser" type="radio" name="riding_style" value="mellow_cruiser"/>
						<div class="control__indicator"></div>
					</label>
					<label for="aggressive_carver" class="control control--radio label-horizontal-card without-image">
						<!--span class="round-img">
							<img src="<!-?=site_url('wp-content/uploads/2022/04/step-05-op-03.png')?>" alt="Aggressive Street Carver">
						</span-->
						<span class="head">Aggressive Street Carver</span>
						<span class="text">I'm a snappy, aggressive, energetic rider. I'm all <br> about those sharp carves and slides. I want my ride <br> to feel tight, sharp, and agile. I like carving in...</span>
						<span class="btn btn-show" type="button" data-bs-toggle="modal" data-bs-target="#board-type-03">Show More</span>
						<span class="btn btn-choose">Choose</span>
						<input data-subtitle="Aggressive Street Carver" id="aggressive_carver" type="radio" name="riding_style" value="aggressive_carver"/>
						<div class="control__indicator"></div>
					</label>
					<label for="hybrid" class="control control--radio label-horizontal-card without-image">
						<!--span class="round-img">
							<img src="<!-?=site_url('wp-content/uploads/2022/04/step-05-op-04.png')?>" alt="Street Skating Hybrid">
						</span-->
						<span class="head">Street Skating Hybrid</span>
						<span class="text">I like something in between. I lean toward the <br> smooth and mellow ride, but I want that sharp <br> carvey feel, too. I want to be able to do it all...</span>
						<span class="btn btn-show" type="button" data-bs-toggle="modal" data-bs-target="#board-type-04">Show More</span>
						<span class="btn btn-choose">Choose</span>
						<input data-subtitle="Street Skating Hybrid" id="hybrid" type="radio" name="riding_style" value="hybrid"/>
						<div class="control__indicator"></div>
					</label>
				</div><!-- /.Step Input Fields Ends Here -->
				<!-- .Button Next Starts Here -->
				<button type="button" name="next" class="btn next action-button">Next step</button><!-- /.Button Next Ends Here -->
				<!-- .Button Previous Starts Here -->
				<button type="button" name="previous" class="btn previous action-button-previous"><i class="icon-arrow-left"></i> Back</button><!-- /.Button Previous Ends Here -->
			</fieldset><!-- /.Step 6 Ends Here -->
			<!------------------------------------------------------------------------------>
			<!-- .Step 7 Starts Here -->
			<fieldset class="step-fieldset step-02 step-03 step-07 step-007" id="surf-step-07">
				<!-- .Step Head Starts Here -->
				<div class="step-head">
					<h1>Question #7</h1>
					<h2>I want my <span>surfskate truck</span> to feel more:</h2>
				</div><!-- /.Step Head Ends Here -->
				<!-- .Step Input Fields Starts Here -->
				<div class="form-card">
					<div class="row">
						<div class="col-sm-6">
							<!-- .Step Video Starts Here --->
							<div class="step-video">
								<script src=" https://fast.wistia.com/embed/medias/91kbdfjash.jsonp" async></script><script src=" https://fast.wistia.com/assets/external/E-v1.js" async></script><div class="wistia_responsive_ padding" style="padding:56.25% 0 0 0;position:relative;"><div class="wistia_responsive_ wrapper" style="height:100%;left:0; position:absolute;top:0;width: 100%;"><div class="wistia_embed wistia_async_91kbdfjash videoFoam=true" style="height:100%;position: relative;width:100%"><div class="wistia_swatch" style="height:100%;left:0; opacity:0;overflow:hidden; position:absolute;top:0; transition:opacity 200ms;width:100%;"><img src=" https://fast.wistia.com/embed/medias/91kbdfjash/swatch " style="filter:blur(5px); height:100%;object-fit: contain;width:100%;" alt="" aria-hidden="true" onload="this.parentNode.style. opacity=1;" /></div></div></div></div>
							</div>
							<!-- /.Step Video Ends Here --->
						</div>
						<div class="col-sm-6 pl-115px">
							<h4 for="truck_feel">Please select:</h4>
							<label for="loose_flowy_smooth" class="control control--radio">Loose/Flowy/Smooth
								<input data-subtitle="Loose/Flowy/Smooth" id="loose_flowy_smooth" type="radio" name="truck_feel" value="loose_flowy_smooth"/>
								<div class="control__indicator"></div>
							</label>
							<label for="tight_snappy_responsive" class="control control--radio">Tight/Snappy/Responsive
								<input data-subtitle="Tight/Snappy/Responsive" id="tight_snappy_responsive" type="radio" name="truck_feel" value="tight_snappy_responsive" />
								<div class="control__indicator"></div>
							</label>
							<label for="adjustable" class="control control--radio">Adjustable
								<input data-subtitle="Adjustable" id="adjustable" type="radio" name="truck_feel" value="adjustable" />
								<div class="control__indicator"></div>
							</label>
						</div>
					</div>
				</div><!-- /.Step Input Fields Ends Here -->
				<!-- .Button Next Starts Here -->
				<button type="button" name="next" class="btn next action-button">Next step</button><!-- /.Button Next Ends Here -->
				<!-- .Button Previous Starts Here -->
				<button type="button" name="previous" class="btn previous action-button-previous"><i class="icon-arrow-left"></i> Back</button><!-- /.Button Previous Ends Here -->
			</fieldset><!-- /.Step 7 Ends Here -->
			<!------------------------------------------------------------------------------>
			<!-- .Step 8 Starts Here -->
			<fieldset class="step-fieldset step-05 step-08" id="surf-step-08">
				<!-- .Step Head Starts Here -->
				<div class="step-head">
					<h1>Question #8</h1>
					<h2 for="riding_distance">When you surfskate, <span>how far</span> do you like to go?</h2>
				</div><!-- /.Step Head Ends Here -->
				<!-- .Step Input Fields Starts Here -->
				<div class="form-card">
					<label for="short_distance_trainer" class="control control--radio label-horizontal-card without-image">
						<!--span class="round-img">
							<img src="<!-?=site_url('wp-content/uploads/2022/04/step-08-op-01.png')?>" alt="Short Distance Trainer">
						</span-->
						<span class="head">Short Distance Trainer</span>
						<span class="text">I stay in small areas and practice tight maneuvers. <br> Therefore, it's not very important to me that my <br> surfskate truck have a lot of forward momentum...</span>
						<span class="btn btn-show" type="button" data-bs-toggle="modal" data-bs-target="#how-far-go-01">Show More</span>
						<span class="btn btn-choose">Choose</span>
						<input data-subtitle="Short Distance Trainer" id="short_distance_trainer" type="radio" name="riding_distance" value="short_distance_trainer" />
						<div class="control__indicator"></div>
					</label>
					<label for="medium_distance_cruiser" class="control control--radio label-horizontal-card without-image">
						<!--span class="round-img">
							<img src="<!-?=site_url('wp-content/uploads/2022/04/step-08-op-02.png')?>" alt="Medium Distance Cruiser">
						</span-->
						<span class="head">Medium Distance Cruiser</span>
						<span class="text">I like to cruise for medium distances, usually for 30 <br> minutes to an hour. Therefore, it's moderately <br> important to me that my surfskate truck have a...</span>
						<span class="btn btn-show" type="button" data-bs-toggle="modal" data-bs-target="#how-far-go-02">Show More</span>
						<span class="btn btn-choose">Choose</span>
						<input data-subtitle="Medium Distance Cruiser" id="medium_distance_cruiser" type="radio" name="riding_distance" value="medium_distance_cruiser" />
						<div class="control__indicator"></div>
					</label>
					<label for="long_distance_cruiser" class="control control--radio label-horizontal-card without-image">
						<!--span class="round-img">
							<img src="<!-?=site_url('wp-content/uploads/2022/04/step-08-op-03.png')?>" alt="Long Distance Cruiser">
						</span-->
						<span class="head">Long Distance Cruiser</span>
						<span class="text">I like to cruise for long distances, often for an hour <br> or longer. Therefore, it's very important to me that <br> my surfskate truck have good forward...</span>
						<span class="btn btn-show" type="button" data-bs-toggle="modal" data-bs-target="#how-far-go-03">Show More</span>
						<span class="btn btn-choose">Choose</span>
						<input data-subtitle="Long Distance Cruiser" id="long_distance_cruiser" type="radio" name="riding_distance" value="long_distance_cruiser" />
						<div class="control__indicator"></div>
					</label>
				</div><!-- /.Step Input Fields Ends Here -->
				<!-- .Button Next Starts Here -->
				<button type="button" name="next" class="btn next action-button">Next step</button>				
				<!-- .Button Previous Starts Here -->
				<button type="button" name="previous" class="btn previous action-button-previous"><i class="icon-arrow-left"></i> Back</button><!-- /.Button Previous Ends Here -->
			</fieldset>
			<!-- /.Step 8 Ends Here -->
			<!-- .Step 9 Starts Here -->
			<fieldset class="step-fieldset step-05" id="surf-step-09">
				<div class="step-head">
					<h1>Enter information to see your results </h1>
					<!-- <h2>What is your <span>stance width</span>?</h2>
					<p>If you don't know how to determine this, watch this video.</p> -->
				</div>
				<!-- /.Step Head Ends Here -->
				<div class="form-card">					
					<div class="form-group">
						<input type="text" class="form-control" id="firstname" placeholder="Enter First Name">
					</div>
					<div class="form-group">
						<input type="text" class="form-control" id="lastname" placeholder="Enter Last Name">
					</div>
					<div class="form-group">
						<!-- <label for="useremail">Email address</label> -->
						<input type="email" class="form-control" id="useremail" placeholder="Enter Email">
						<span class="email-txt-cls">Provide a valid email address to receive a coupon code for 15% off everything in the Surfskate Love shop.</span>
						<input type="hidden" name="entry_id" id="entry_id">
					</div>
					<div class="form-group" id="errors-meg"></div>
				</div><!-- /.Step Input Fields Ends Here -->
				<button type="button" name="next" id="information" class="btn next action-button">See Results</button><!-- /.Button Next Ends Here -->
				<!-- .Button Next Starts Here -->
				<button type="button" name="previous" class="btn previous action-button-previous"><i class="icon-arrow-left"></i> Back</button><!-- /.Button Previous Ends Here -->
				<!-- /.Button Next Ends Here -->
			</fieldset>
			<!-- /.Step 8 Ends Here -->
			<!-- .Final Results Starts Here -->
			<section class="step-fieldset final-results" style="height: 0;">
				<!-- .Step Head Starts Here -->
				<div class="step-head">
					<h1>Results</h1>
					<h2><span>Great job!</span> You've completed the Surfskate Selector.</h2>
					<p>See the <span>best models</span> that meet your specifications below.</p>
				</div><!-- /.Step Head Ends Here -->
				<!-- .Model List Starts Here -->
				<!-- <div class="model-list"></div> -->
				<div class="surfskate-models">
					<div class="container">
						<h3>Found: <span class="total-cout">5 models</span></h3>
						<span class="found-tagline">Click on individual models below for more details.</span>
					</div>
					<div class="container">
						<div class="loding-result">
							<i class="fa fa-refresh fa-spin" style="font-size:50px;"></i>
						</div>
						<div class="surfskating-filters-btns-box filtering-actions"></div>
						<div class="surfskating-content filtering-items-container"></div>
					</div>
				</div><!-- /.Model List Ends Here -->
				<!-- .Back To Home Button Starts Here -->
			</section><!-- /.Final Results Ends Here -->              
		</div>
		<!-- .Filtering Starts Here -->
		<div class="surfskate-models surfskating-filtering">
			<div class="container">
				<h4>Found: <span class="total-cout">1234 models</span></h4>
				<!-- .Filter Buttons Starts Here -->
				<div class="surfskating-filters-btns-box filtering-actions">
					<?php 
					$get_brands = get_terms( array(
						'taxonomy' => 'brands',
						'hide_empty' => true,
						'order'   => 'ASC'
					) );
					foreach ($get_brands as $key => $brand) {
						$term_image = get_field('select_image', $brand->taxonomy.'_' . $brand->term_id);
						printf('<button data-count="%s" type="button" class="btn surfskating-filter-btn filtering-action">
                                        <figure class="figure">
                                            <img src="%s" alt="SwellTech">
                                        </figure>
                                        <p>%s</p>
                                    </button>',$brand->count, $term_image, $brand->name);
					}
					?>
				</div>
				<!-- .Filter Buttons Ends Here -->
				<!-- .Filter Content Items Starts Here -->
				<div class="surfskating-content filtering-items-container">
					<!-- .Items for Swelltech Boards Starts Here -->
					<?php
					foreach ($get_brands as $key => $brands) {
						$surfsket_args = array(
							'post_type'     => 'surfskate',
							'post_status'   => 'publish',
							'posts_per_page' => -1,
							'meta_query' => [
								'relation' => 'AND',
								'wheelbase_asc' => [
									'key' => 'wheelbase'
								],
								'length_asc' => [
									'key' => 'length'
								],
							],
							'orderby'       => [
								'wheelbase_asc' => 'ASC',
								'length_asc'    => 'ASC',
								'title'         => 'ASC'
							],
							'tax_query' => array(
								array(
									'taxonomy'  => $brands->taxonomy,
									'field'     => 'term_id',
									'terms'     => $brands->term_id
								)
							)
						);
						$query_surfsket = new WP_Query( $surfsket_args );
						if ( $query_surfsket->have_posts() ) {
							echo'<ul class="surfskating-item filtering-item data-swelltech">';
							while ( $query_surfsket->have_posts() ) {
								$query_surfsket->the_post(); 
								$post_image = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
								printf('<li>
                                                        <div class="cat-item-details">
                                                            <figure class="figure">
                                                                <img src="%s" alt="%s">
                                                            </figure>
                                                            <p>%s</p>
                                                        </div>
                                                    </li>',$post_image[0], get_the_title(),get_the_title() );
							}
							echo'</ul>';
						}
						wp_reset_postdata();
					}
					?>
					<!-- .Button Show More Items Starts Here -->
					<!-- <button type="button" class="btn btn-more-items">show more</button> -->
					<!-- .Button Show More Items Ends Here -->                      
				</div>
				<!-- .Filter Content Items Ends Here -->
				<!-- .Button Next Starts Here -->
				<!-- <button type="button" name="item-next" class="btn item-next action-button">Next step</button> -->
				<!-- .Button Next Ends Here -->
				<!-- .Button Previous Starts Here -->
				<button type="button" name="item-previous" class="btn item-previous action-button-previous" style="display: none;"><i class="icon-arrow-left"></i> Back</button>
				<!-- .Button Previous Ends Here -->
				<div id="loader" class="lds-dual-ring display-none overlay"></div> 
			</div>
		</div>
		<!-- /.Filtering Ends Here -->
	</form>
	<!-- /.Main Form Ends Here -->
</div>
<!-- /.Step Form Ends Here -->
<!-------------------------------------------------------------------------------->

<!-- .Site Footer Starts Here -->
<footer class="site-footer">
	<div class="container">
		<div class="row">
			<div class="col-sm-3">
				<figure class="footer-site-logo">
					<a href="<?=site_url()?>">
						<img src="<?=site_url('wp-content/uploads/2022/03/whitelogohorizontal-copy-1.png')?>" alt="Footer Logo">
					</a>
				</figure>
			</div>
			<div class="col-sm-7">
				<div class="copyright">
					<p>Made w/<i class="bi bi-heart-fill"></i> by Savior © 2020 – 2022 by Surfskate Love LLC. All rights reserved.</p>
				</div>
			</div>
			<div class="col-sm-2">
				<ul class="footer-social-icons">
					<li>
						<a href="https://www.facebook.com/surfskatelovefb" target="_blank"><span class="icon-surf icon-surf-surf-ico-fb"></span></a>
					</li>
					<li>
						<a href="https://www.youtube.com/channel/UCQg_K_HvcBBQHD1hif-aVSw" target="_blank"><span class="icon-surf icon-surf-surf-ico-yt"></span></a>
					</li>
					<li>
						<a href="https://www.instagram.com/surfskateloveig" target="_blank"><span class="icon-surf icon-surf-surf-ico-insta"></span></a>
					</li>
					<li>
						<a href="https://www.tiktok.com/@surfskatelovetk" target="_blank"><span class="icon-surf icon-surf-surf-ico-tiktok"></span></a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</footer>
<!-- .Site Footer Ends Here -->
<!-------------------------------------------------------------------------------->
<!-- .Modal Starts Here -->
<div class="modal prod-modal fade" id="fullDetails" tabindex="-1" aria-labelledby="specsModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<!-- .Product Starts Here -->
				<div class="prod-item">
					<!-- .Product Slider Starts Here -->
					<div class="prod-slider">
						<div class="prod-slider-item">
							<div id="prodSlideIndicator" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
								<div class="carousel-inner">
									<div class="carousel-item active">
										<div class="slide-item">
											<img class="prod-img" src="<?=site_url('wp-content/uploads/2022/04/1.Aquilo30_Model.png')?>" alt="First slide">
										</div>
									</div>
									<div class="carousel-item">
										<div class="slide-item">
											<img class="prod-img" src="<?=site_url('wp-content/uploads/2022/04/2.Aquilo32_Model.png')?>" alt="Second slide">
										</div>
									</div>
									<div class="carousel-item">
										<div class="slide-item">
											<img class="prod-img" src="<?=site_url('wp-content/uploads/2022/04/1.Aquilo30_Model.png')?>" alt="Third slide">
										</div>
									</div>
								</div>
								<div class="carousel-indicators">
									<button type="button" data-bs-target="#prodSlideIndicator" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1">
										<img class="slider-thumb" src="<?=site_url('wp-content/uploads/2022/04/1.Aquilo30_Model.png')?>" alt="First slide">
									</button>
									<button type="button" data-bs-target="#prodSlideIndicator" data-bs-slide-to="1" aria-label="Slide 2">
										<img class="slider-thumb" src="<?=site_url('wp-content/uploads/2022/04/2.Aquilo32_Model.png')?>" alt="Second slide">
									</button>
									<button type="button" data-bs-target="#prodSlideIndicator" data-bs-slide-to="2" aria-label="Slide 3">
										<img class="slider-thumb" src="<?=site_url('wp-content/uploads/2022/04/1.Aquilo30_Model.png')?>" alt="Third slide">
									</button>
								</div>
							</div>
						</div>
					</div>
					<!-- /.Product Slider Ends Here -->
					<!-- .Product Description Starts Here -->
					<div class="prod-descp">
						<h2>31" CMC Performance Model</h2>
						<h3>Brand: <span class="brand-name">Carver C7</span> <span class="brand-price">$250</span></h3>
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam imperdiet dolor id suscipit tempus. Ut a leo quis elit varius faucibus. In laoreet vel turpis vel rhoncus. Mauris et ligula a tortor hendrerit malesuada.</p>
						<ul>
							<li>
								<span class="feat-name">Length</span>
								<span class="feat-value">34"</span>
							</li>
							<li>
								<span class="feat-name">Wheelbase</span>
								<span class="feat-value">18"</span>
							</li>
							<li>
								<span class="feat-name">Stance Width Range</span>
								<span class="feat-value">17"-19"</span>
							</li>
							<li>
								<span class="feat-name">Width</span>
								<span class="feat-value">10.25"</span>
							</li>
							<li>
								<span class="feat-name">Concave</span>
								<span class="feat-value">High</span>
							</li>
						</ul>
						<button type="button" class="btn share-btn" data-bs-dismiss="modal"><i class="bi bi-x"></i></button>
					</div>
					<!-- /.Product Description Ends Here -->
				</div>
				<!-- /.Product Ends Here -->
			</div>
		</div>
	</div>
</div><!-- .Modal Ends Here -->

<!-------------------------------------------------------------------------------->
<!-- .Modal For Board Type 1 Starts Here -->
<div class="modal prod-modal step-modal fade" id="board-type-01" tabindex="-1" aria-labelledby="stepModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<button type="button" class="btn share-btn" data-bs-dismiss="modal"><i class="bi bi-x"></i></button>
				<div class="step-modal-content">
					<h4>Technical Surf Trainer</h4>
					<p>I want to stay in small areas and practice technical surf maneuvers to improve my surfing. I want my surfskate to be very responsive to upper body movements and weight shifts. I won't be going long distances, and I don't need a lot of forward momentum with each pump.</p>
				</div>
			</div>
		</div>
	</div>
</div><!-- .Modal For Board Type 1 Ends Here -->
<!-------------------------------------------------------------------------------->
<!-- .Modal For Board Type 2 Starts Here -->
<div class="modal prod-modal step-modal fade" id="board-type-02" tabindex="-1" aria-labelledby="stepModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<button type="button" class="btn share-btn" data-bs-dismiss="modal"><i class="bi bi-x"></i></button>
				<div class="step-modal-content">
					<h4>Mellow Street Cruiser</h4>
					<p>I’m a smooth, mellow, cruising rider. I want my ride to feel smooth, soft, and glidey. I want the feeling of relaxed freedom. I like taking my time and making the ride last. I want to feel like I’m floating gently back and forth down a wave, simply enjoying the ride without a care in the world.</p>
				</div>
			</div>
		</div>
	</div>
</div><!-- .Modal For Board Type 2 Ends Here -->
<!-------------------------------------------------------------------------------->
<!-- .Modal For Board Type 3 Starts Here -->
<div class="modal prod-modal step-modal fade" id="board-type-03" tabindex="-1" aria-labelledby="stepModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<button type="button" class="btn share-btn" data-bs-dismiss="modal"><i class="bi bi-x"></i></button>
				<div class="step-modal-content">
					<h4>Aggressive Street Carver</h4>
					<p>I’m a snappy, aggressive, energetic rider. I’m all about those sharp carves and slides. I want my ride to feel tight, sharp, and agile. I like carving in, through, and around urban obstacles and environments. I want to feel like I’m shredding a wave hard and aggressive and sucking every last bit of life from it.</p>
				</div>
			</div>
		</div>
	</div>
</div><!-- .Modal For Board Type 3 Ends Here -->
<!-------------------------------------------------------------------------------->
<!-- .Modal For Board Type 4 Starts Here -->
<div class="modal prod-modal step-modal fade" id="board-type-04" tabindex="-1" aria-labelledby="stepModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<button type="button" class="btn share-btn" data-bs-dismiss="modal"><i class="bi bi-x"></i></button>
				<div class="step-modal-content">
					<h4>Street Skating Hybrid</h4>
					<p>I like something in between. I lean toward the smooth and mellow ride, but I want that sharp carvey feel, too. I want to be able to do it all: cruise chill when I want, carve hard and slide when I want.</p>
				</div>
			</div>
		</div>
	</div>
</div><!-- .Modal For Board Type 4 Ends Here -->
<!-------------------------------------------------------------------------------->
<!-- .Modal How Far You Go 1 Starts Here -->
<div class="modal prod-modal step-modal fade" id="how-far-go-01" tabindex="-1" aria-labelledby="stepModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<button type="button" class="btn share-btn" data-bs-dismiss="modal"><i class="bi bi-x"></i></button>
				<div class="step-modal-content">
					<h4>Short Distance Trainer</h4>
					<p>I stay in small areas and practice tight maneuvers. Therefore, it's not very important to me that my surfskate truck have a lot of forward momentum. I want my surfskate truck to feel like a technical surf trainer, with easy lateral side-to-side movement to make upper body weight shifts more effortless.</p>
				</div>
			</div>
		</div>
	</div>
</div><!-- .Modal How Far You Go 1 Ends Here -->
<!-------------------------------------------------------------------------------->
<!-- .Modal How Far You Go 2 Starts Here -->
<div class="modal prod-modal step-modal fade" id="how-far-go-02" tabindex="-1" aria-labelledby="stepModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<button type="button" class="btn share-btn" data-bs-dismiss="modal"><i class="bi bi-x"></i></button>
				<div class="step-modal-content">
					<h4>Medium Distance Cruiser</h4>
					<p>I like to cruise for medium distances, usually for 30 minutes to an hour. Therefore, it's moderately important to me that my surfskate truck have a lot of forward momentum. I want it to feel like a good all-around surfskate truck for both street cruising and technical surf training. I want it to have both good forward momentum, and also easy lateral side-to-side motion for smooth carving.</p>
				</div>
			</div>
		</div>
	</div>
</div><!-- .Modal How Far You Go 2 Ends Here -->
<!-------------------------------------------------------------------------------->
<!-- .Modal How Far You Go 3 Starts Here -->
<div class="modal prod-modal step-modal fade" id="how-far-go-03" tabindex="-1" aria-labelledby="stepModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<button type="button" class="btn share-btn" data-bs-dismiss="modal"><i class="bi bi-x"></i></button>
				<div class="step-modal-content">
					<h4>Long Distance Cruiser</h4>
					<p> I like to cruise for long distances, often for an hour or longer. Therefore, it's very important to me that my surfskate truck have good forward momentum. Lateral side-to-side motion is less important to me.</p>
				</div>
			</div>
		</div>
	</div>
</div><!-- .Modal How Far You Go 3 Ends Here -->

<?php get_footer(); ?>