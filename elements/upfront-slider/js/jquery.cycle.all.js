/*!
* jQuery Cycle2; version: 2.1.6 build: 20141007
* http://jquery.malsup.com/cycle2/
* Copyright (c) 2014 M. Alsup; Dual licensed: MIT/GPL
*/

/* Cycle2 core engine */
;(function($) {
	"use strict";
	
	var version = '2.1.6';
	
	$.fn.cycle = function( options ) {
		// fix mistakes with the ready state
		var o;
		if ( this.length === 0 && !$.isReady ) {
			o = { s: this.selector, c: this.context };
			$.fn.cycle.log('requeuing slideshow (dom not ready)');
			$(function() {
				$( o.s, o.c ).cycle(options);
			});
			return this;
		}
	
		return this.each(function() {
			var data, opts, shortName, val;
			var container = $(this);
			var log = $.fn.cycle.log;
	
			if ( container.data('cycle.opts') )
				return; // already initialized
	
			if ( container.data('cycle-log') === false || 
				( options && options.log === false ) ||
				( opts && opts.log === false) ) {
				log = $.noop;
			}
	
			log('--c2 init--');
			data = container.data();
			for (var p in data) {
				// allow props to be accessed sans 'cycle' prefix and log the overrides
				if (data.hasOwnProperty(p) && /^cycle[A-Z]+/.test(p) ) {
					val = data[p];
					shortName = p.match(/^cycle(.*)/)[1].replace(/^[A-Z]/, lowerCase);
					log(shortName+':', val, '('+typeof val +')');
					data[shortName] = val;
				}
			}
	
			opts = $.extend( {}, $.fn.cycle.defaults, data, options || {});
	
			opts.timeoutId = 0;
			opts.paused = opts.paused || false; // #57
			opts.container = container;
			opts._maxZ = opts.maxZ;
	
			opts.API = $.extend ( { _container: container }, $.fn.cycle.API );
			opts.API.log = log;
			opts.API.trigger = function( eventName, args ) {
				opts.container.trigger( eventName, args );
				return opts.API;
			};
	
			container.data( 'cycle.opts', opts );
			container.data( 'cycle.API', opts.API );
	
			// opportunity for plugins to modify opts and API
			opts.API.trigger('cycle-bootstrap', [ opts, opts.API ]);
	
			opts.API.addInitialSlides();
			opts.API.preInitSlideshow();
	
			if ( opts.slides.length )
				opts.API.initSlideshow();
		});
	};
	
	$.fn.cycle.API = {
		opts: function() {
			return this._container.data( 'cycle.opts' );
		},
		addInitialSlides: function() {
			var opts = this.opts();
			var slides = opts.slides;
			opts.slideCount = 0;
			opts.slides = $(); // empty set
			
			// add slides that already exist
			slides = slides.jquery ? slides : opts.container.find( slides );
	
			if ( opts.random ) {
				slides.sort(function() {return Math.random() - 0.5;});
			}
	
			opts.API.add( slides );
		},
	
		preInitSlideshow: function() {
			var opts = this.opts();
			opts.API.trigger('cycle-pre-initialize', [ opts ]);
			var tx = $.fn.cycle.transitions[opts.fx];
			if (tx && typeof tx.preInit === 'function')
			tx.preInit( opts );
			opts._preInitialized = true;
		},
	
		postInitSlideshow: function() {
			var opts = this.opts();
			opts.API.trigger('cycle-post-initialize', [ opts ]);
			var tx = $.fn.cycle.transitions[opts.fx];
			if (tx && typeof tx.postInit === 'function')
			tx.postInit( opts );
		},
	
		initSlideshow: function() {
			var opts = this.opts();
			var pauseObj = opts.container;
			var slideOpts;
			opts.API.calcFirstSlide();
	
			if ( opts.container.css('position') == 'static' )
				opts.container.css('position', 'relative');
	
			$(opts.slides[opts.currSlide]).css({
				opacity: 1,
				display: 'block',
				visibility: 'visible'
			});
			opts.API.stackSlides( opts.slides[opts.currSlide], opts.slides[opts.nextSlide], !opts.reverse );
	
			if ( opts.pauseOnHover ) {
				// allow pauseOnHover to specify an element
				if ( opts.pauseOnHover !== true )
					pauseObj = $( opts.pauseOnHover );
	
				pauseObj.on('mouseenter',
					function(){ opts.API.pause( true ); }, 
					function(){ opts.API.resume( true ); }
				);
			}
	
			// stage initial transition
			if ( opts.timeout ) {
				slideOpts = opts.API.getSlideOpts( opts.currSlide );
				opts.API.queueTransition( slideOpts, slideOpts.timeout + opts.delay );
			}
	
			opts._initialized = true;
			opts.API.updateView( true );
			opts.API.trigger('cycle-initialized', [ opts ]);
			opts.API.postInitSlideshow();
		},
	
		pause: function( hover ) {
			var opts = this.opts(),
				slideOpts = opts.API.getSlideOpts(),
				alreadyPaused = opts.hoverPaused || opts.paused;
	
			if ( hover )
				opts.hoverPaused = true; 
			else
				opts.paused = true;
	
			if ( ! alreadyPaused ) {
				opts.container.addClass('cycle-paused');
				opts.API.trigger('cycle-paused', [ opts ]).log('cycle-paused');
	
				if ( slideOpts.timeout ) {
					clearTimeout( opts.timeoutId );
					opts.timeoutId = 0;
					
					// determine how much time is left for the current slide
					opts._remainingTimeout -= ( $.now() - opts._lastQueue );
					if ( opts._remainingTimeout < 0 || isNaN(opts._remainingTimeout) )
						opts._remainingTimeout = undefined;
				}
			}
		},
	
		resume: function( hover ) {
			var opts = this.opts(),
				alreadyResumed = !opts.hoverPaused && !opts.paused,
				remaining;
	
			if ( hover )
				opts.hoverPaused = false; 
			else
				opts.paused = false;
	
		
			if ( ! alreadyResumed ) {
				opts.container.removeClass('cycle-paused');
				// #gh-230; if an animation is in progress then don't queue a new transition; it will
				// happen naturally
				if ( opts.slides.filter(':animated').length === 0 )
					opts.API.queueTransition( opts.API.getSlideOpts(), opts._remainingTimeout );
				opts.API.trigger('cycle-resumed', [ opts, opts._remainingTimeout ] ).log('cycle-resumed');
			}
		},
	
		add: function( slides, prepend ) {
			var opts = this.opts();
			var oldSlideCount = opts.slideCount;
			var startSlideshow = false;
			var len;
	
			if ( $.type(slides) == 'string')
				slides = $.trim( slides );
	
			$( slides ).each(function(i) {
				var slideOpts;
				var slide = $(this);
	
				if ( prepend )
					opts.container.prepend( slide );
				else
					opts.container.append( slide );
	
				opts.slideCount++;
				slideOpts = opts.API.buildSlideOpts( slide );
	
				if ( prepend )
					opts.slides = $( slide ).add( opts.slides );
				else
					opts.slides = opts.slides.add( slide );
	
				opts.API.initSlide( slideOpts, slide, --opts._maxZ );
	
				slide.data('cycle.opts', slideOpts);
				opts.API.trigger('cycle-slide-added', [ opts, slideOpts, slide ]);
			});
	
			opts.API.updateView( true );
	
			startSlideshow = opts._preInitialized && (oldSlideCount < 2 && opts.slideCount >= 1);
			if ( startSlideshow ) {
				if ( !opts._initialized )
					opts.API.initSlideshow();
				else if ( opts.timeout ) {
					len = opts.slides.length;
					opts.nextSlide = opts.reverse ? len - 1 : 1;
					if ( !opts.timeoutId ) {
						opts.API.queueTransition( opts );
					}
				}
			}
		},
	
		calcFirstSlide: function() {
			var opts = this.opts();
			var firstSlideIndex;
			firstSlideIndex = parseInt( opts.startingSlide || 0, 10 );
			if (firstSlideIndex >= opts.slides.length || firstSlideIndex < 0)
				firstSlideIndex = 0;
	
			opts.currSlide = firstSlideIndex;
			if ( opts.reverse ) {
				opts.nextSlide = firstSlideIndex - 1;
				if (opts.nextSlide < 0)
					opts.nextSlide = opts.slides.length - 1;
			}
			else {
				opts.nextSlide = firstSlideIndex + 1;
				if (opts.nextSlide == opts.slides.length)
					opts.nextSlide = 0;
			}
		},
	
		calcNextSlide: function() {
			var opts = this.opts();
			var roll;
			if ( opts.reverse ) {
				roll = (opts.nextSlide - 1) < 0;
				opts.nextSlide = roll ? opts.slideCount - 1 : opts.nextSlide-1;
				opts.currSlide = roll ? 0 : opts.nextSlide+1;
			}
			else {
				roll = (opts.nextSlide + 1) == opts.slides.length;
				opts.nextSlide = roll ? 0 : opts.nextSlide+1;
				opts.currSlide = roll ? opts.slides.length-1 : opts.nextSlide-1;
			}
		},
	
		calcTx: function( slideOpts, manual ) {
			var opts = slideOpts;
			var tx;
	
			if ( opts._tempFx )
				tx = $.fn.cycle.transitions[opts._tempFx];
			else if ( manual && opts.manualFx )
				tx = $.fn.cycle.transitions[opts.manualFx];
	
			if ( !tx )
				tx = $.fn.cycle.transitions[opts.fx];
	
			opts._tempFx = null;
			this.opts()._tempFx = null;
	
			if (!tx) {
				tx = $.fn.cycle.transitions.fade;
				opts.API.log('Transition "' + opts.fx + '" not found.  Using fade.');
			}
			return tx;
		},
	
		prepareTx: function( manual, fwd ) {
			var opts = this.opts();
			var after, curr, next, slideOpts, tx;
	
			if ( opts.slideCount < 2 ) {
				opts.timeoutId = 0;
				return;
			}
			if ( manual && ( !opts.busy || opts.manualTrump ) ) {
				opts.API.stopTransition();
				opts.busy = false;
				clearTimeout(opts.timeoutId);
				opts.timeoutId = 0;
			}
			if ( opts.busy )
				return;
			if ( opts.timeoutId === 0 && !manual )
				return;
	
			curr = opts.slides[opts.currSlide];
			next = opts.slides[opts.nextSlide];
			slideOpts = opts.API.getSlideOpts( opts.nextSlide );
			tx = opts.API.calcTx( slideOpts, manual );
	
			opts._tx = tx;
	
			if ( manual && slideOpts.manualSpeed !== undefined )
				slideOpts.speed = slideOpts.manualSpeed;
	
			// if ( opts.nextSlide === opts.currSlide )
			//     opts.API.calcNextSlide();
	
			// ensure that:
			//      1. advancing to a different slide
			//      2. this is either a manual event (prev/next, pager, cmd) or 
			//              a timer event and slideshow is not paused
			if ( opts.nextSlide != opts.currSlide && 
				(manual || (!opts.paused && !opts.hoverPaused && opts.timeout) )) { // #62
	
				opts.API.trigger('cycle-before', [ slideOpts, curr, next, fwd ]);
				if ( tx.before )
					tx.before( slideOpts, curr, next, fwd );
	
				after = function() {
					opts.busy = false;
					// #76; bail if slideshow has been destroyed
					if (! opts.container.data( 'cycle.opts' ) )
						return;
	
					if (tx.after)
						tx.after( slideOpts, curr, next, fwd );
					opts.API.trigger('cycle-after', [ slideOpts, curr, next, fwd ]);
					opts.API.queueTransition( slideOpts);
					opts.API.updateView( true );
				};
	
				opts.busy = true;
				if (tx.transition)
					tx.transition(slideOpts, curr, next, fwd, after);
				else
					opts.API.doTransition( slideOpts, curr, next, fwd, after);
	
				opts.API.calcNextSlide();
				opts.API.updateView();
			} else {
				opts.API.queueTransition( slideOpts );
			}
		},
	
		// perform the actual animation
		doTransition: function( slideOpts, currEl, nextEl, fwd, callback) {
			var opts = slideOpts;
			var curr = $(currEl), next = $(nextEl);
			var fn = function() {
				// make sure animIn has something so that callback doesn't trigger immediately
				next.animate(opts.animIn || { opacity: 1}, opts.speed, opts.easeIn || opts.easing, callback);
			};
	
			next.css(opts.cssBefore || {});
			curr.animate(opts.animOut || {}, opts.speed, opts.easeOut || opts.easing, function() {
				curr.css(opts.cssAfter || {});
				if (!opts.sync) {
					fn();
				}
			});
			if (opts.sync) {
				fn();
			}
		},
	
		queueTransition: function( slideOpts, specificTimeout ) {
			var opts = this.opts();
			var timeout = specificTimeout !== undefined ? specificTimeout : slideOpts.timeout;
			if (opts.nextSlide === 0 && --opts.loop === 0) {
				opts.API.log('terminating; loop=0');
				opts.timeout = 0;
				if ( timeout ) {
					setTimeout(function() {
						opts.API.trigger('cycle-finished', [ opts ]);
					}, timeout);
				}
				else {
					opts.API.trigger('cycle-finished', [ opts ]);
				}
				// reset nextSlide
				opts.nextSlide = opts.currSlide;
				return;
			}
			if ( opts.continueAuto !== undefined ) {
				if (opts.continueAuto === false || (typeof opts.continueAuto === 'function' && opts.continueAuto() === false)) {
					opts.API.log('terminating automatic transitions');
					opts.timeout = 0;
					if ( opts.timeoutId )
						clearTimeout(opts.timeoutId);
					return;
				}
			}
			if ( timeout ) {
				opts._lastQueue = $.now();
				if ( specificTimeout === undefined )
					opts._remainingTimeout = slideOpts.timeout;
	
				if ( !opts.paused && ! opts.hoverPaused ) {
					opts.timeoutId = setTimeout(function() { 
						opts.API.prepareTx( false, !opts.reverse ); 
					}, timeout );
				}
			}
		},
	
		stopTransition: function() {
			var opts = this.opts();
			if ( opts.slides.filter(':animated').length ) {
				opts.slides.stop(false, true);
				opts.API.trigger('cycle-transition-stopped', [ opts ]);
			}
	
			if ( opts._tx && opts._tx.stopTransition )
				opts._tx.stopTransition( opts );
		},
	
		// advance slide forward or back
		advanceSlide: function( val ) {
			var opts = this.opts();
			clearTimeout(opts.timeoutId);
			opts.timeoutId = 0;
			opts.nextSlide = opts.currSlide + val;
			
			if (opts.nextSlide < 0)
				opts.nextSlide = opts.slides.length - 1;
			else if (opts.nextSlide >= opts.slides.length)
				opts.nextSlide = 0;
	
			opts.API.prepareTx( true,  val >= 0 );
			return false;
		},
	
		buildSlideOpts: function( slide ) {
			var opts = this.opts();
			var val, shortName;
			var slideOpts = slide.data() || {};
			for (var p in slideOpts) {
				// allow props to be accessed sans 'cycle' prefix and log the overrides
				if (slideOpts.hasOwnProperty(p) && /^cycle[A-Z]+/.test(p) ) {
					val = slideOpts[p];
					shortName = p.match(/^cycle(.*)/)[1].replace(/^[A-Z]/, lowerCase);
					opts.API.log('['+(opts.slideCount-1)+']', shortName+':', val, '('+typeof val +')');
					slideOpts[shortName] = val;
				}
			}
	
			slideOpts = $.extend( {}, $.fn.cycle.defaults, opts, slideOpts );
			slideOpts.slideNum = opts.slideCount;
	
			try {
				// these props should always be read from the master state object
				delete slideOpts.API;
				delete slideOpts.slideCount;
				delete slideOpts.currSlide;
				delete slideOpts.nextSlide;
				delete slideOpts.slides;
			} catch(e) {
				// no op
			}
			return slideOpts;
		},
	
		getSlideOpts: function( index ) {
			var opts = this.opts();
			if ( index === undefined )
				index = opts.currSlide;
	
			var slide = opts.slides[index];
			var slideOpts = $(slide).data('cycle.opts');
			return $.extend( {}, opts, slideOpts );
		},
		
		initSlide: function( slideOpts, slide, suggestedZindex ) {
			var opts = this.opts();
			slide.css( slideOpts.slideCss || {} );
			if ( suggestedZindex > 0 )
				slide.css( 'zIndex', suggestedZindex );
	
			// ensure that speed settings are sane
			if ( isNaN( slideOpts.speed ) )
				slideOpts.speed = $.fx.speeds[slideOpts.speed] || $.fx.speeds._default;
			if ( !slideOpts.sync )
				slideOpts.speed = slideOpts.speed / 2;
	
			slide.addClass( opts.slideClass );
		},
	
		updateView: function( isAfter, isDuring, forceEvent ) {
			var opts = this.opts();
			if ( !opts._initialized )
				return;
			var slideOpts = opts.API.getSlideOpts();
			var currSlide = opts.slides[ opts.currSlide ];
	
			if ( ! isAfter && isDuring !== true ) {
				opts.API.trigger('cycle-update-view-before', [ opts, slideOpts, currSlide ]);
				if ( opts.updateView < 0 )
					return;
			}
	
			if ( opts.slideActiveClass ) {
				opts.slides.removeClass( opts.slideActiveClass )
					.eq( opts.currSlide ).addClass( opts.slideActiveClass );
			}
	
			if ( isAfter && opts.hideNonActive )
				opts.slides.filter( ':not(.' + opts.slideActiveClass + ')' ).css('visibility', 'hidden');
	
			if ( opts.updateView === 0 ) {
				setTimeout(function() {
					opts.API.trigger('cycle-update-view', [ opts, slideOpts, currSlide, isAfter ]);
				}, slideOpts.speed / (opts.sync ? 2 : 1) );
			}
	
			if ( opts.updateView !== 0 )
				opts.API.trigger('cycle-update-view', [ opts, slideOpts, currSlide, isAfter ]);
			
			if ( isAfter )
				opts.API.trigger('cycle-update-view-after', [ opts, slideOpts, currSlide ]);
		},
	
		getComponent: function( name ) {
			var opts = this.opts();
			var selector = opts[name];
			if (typeof selector === 'string') {
				// if selector is a child, sibling combinator, adjancent selector then use find, otherwise query full dom
				return (/^\s*[\>|\+|~]/).test( selector ) ? opts.container.find( selector ) : $( selector );
			}
			if (selector.jquery)
				return selector;
			
			return $(selector);
		},
	
		stackSlides: function( curr, next, fwd ) {
			var opts = this.opts();
			if ( !curr ) {
				curr = opts.slides[opts.currSlide];
				next = opts.slides[opts.nextSlide];
				fwd = !opts.reverse;
			}
	
			// reset the zIndex for the common case:
			// curr slide on top,  next slide beneath, and the rest in order to be shown
			$(curr).css('zIndex', opts.maxZ);
	
			var i;
			var z = opts.maxZ - 2;
			var len = opts.slideCount;
			if (fwd) {
				for ( i = opts.currSlide + 1; i < len; i++ )
					$( opts.slides[i] ).css( 'zIndex', z-- );
				for ( i = 0; i < opts.currSlide; i++ )
					$( opts.slides[i] ).css( 'zIndex', z-- );
			}
			else {
				for ( i = opts.currSlide - 1; i >= 0; i-- )
					$( opts.slides[i] ).css( 'zIndex', z-- );
				for ( i = len - 1; i > opts.currSlide; i-- )
					$( opts.slides[i] ).css( 'zIndex', z-- );
			}
	
			$(next).css('zIndex', opts.maxZ - 1);
		},
	
		getSlideIndex: function( el ) {
			return this.opts().slides.index( el );
		}
	
	}; // API
	
	// default logger
	$.fn.cycle.log = function log() {
		/*global console:true */
		if (window.console && console.log)
			console.log('[cycle2] ' + Array.prototype.join.call(arguments, ' ') );
	};
	
	$.fn.cycle.version = function() { return 'Cycle2: ' + version; };
	
	// helper functions
	
	function lowerCase(s) {
		return (s || '').toLowerCase();
	}
	
	// expose transition object
	$.fn.cycle.transitions = {
		custom: {
		},
		none: {
			before: function( opts, curr, next, fwd ) {
				opts.API.stackSlides( next, curr, fwd );
				opts.cssBefore = { opacity: 1, visibility: 'visible', display: 'block' };
			}
		},
		fade: {
			before: function( opts, curr, next, fwd ) {
				var css = opts.API.getSlideOpts( opts.nextSlide ).slideCss || {};
				opts.API.stackSlides( curr, next, fwd );
				opts.cssBefore = $.extend(css, { opacity: 0, visibility: 'visible', display: 'block' });
				opts.animIn = { opacity: 1 };
				opts.animOut = { opacity: 0 };
			}
		},
		fadeout: {
			before: function( opts , curr, next, fwd ) {
				var css = opts.API.getSlideOpts( opts.nextSlide ).slideCss || {};
				opts.API.stackSlides( curr, next, fwd );
				opts.cssBefore = $.extend(css, { opacity: 1, visibility: 'visible', display: 'block' });
				opts.animOut = { opacity: 0 };
			}
		},
		scrollHorz: {
			before: function( opts, curr, next, fwd ) {
				opts.API.stackSlides( curr, next, fwd );
				var w = opts.container.css('overflow','hidden').width();
				opts.cssBefore = { left: fwd ? w : - w, top: 0, opacity: 1, visibility: 'visible', display: 'block' };
				opts.cssAfter = { zIndex: opts._maxZ - 2, left: 0 };
				opts.animIn = { left: 0 };
				opts.animOut = { left: fwd ? -w : w };
			}
		}
	};
	
	// @see: http://jquery.malsup.com/cycle2/api
	$.fn.cycle.defaults = {
		allowWrap:        true,
		autoSelector:     '.cycle-slideshow[data-cycle-auto-init!=false]',
		delay:            0,
		easing:           null,
		fx:              'fade',
		hideNonActive:    true,
		loop:             0,
		manualFx:         undefined,
		manualSpeed:      undefined,
		manualTrump:      true,
		maxZ:             100,
		pauseOnHover:     false,
		reverse:          false,
		slideActiveClass: 'cycle-slide-active',
		slideClass:       'cycle-slide',
		slideCss:         { position: 'absolute', top: 0, left: 0 },
		slides:          '> img',
		speed:            500,
		startingSlide:    0,
		sync:             true,
		timeout:          4000,
		updateView:       0
	};
	
	// automatically find and run slideshows
	$(document).ready(function() {
		$( $.fn.cycle.defaults.autoSelector ).cycle();
	});
	
	})(jQuery);
	
	/*! Cycle2 autoheight plugin; Copyright (c) M.Alsup, 2012; version: 20130913 */
	(function($) {
	"use strict";
	
	$.extend($.fn.cycle.defaults, {
		autoHeight: 0, // setting this option to false disables autoHeight logic
		autoHeightSpeed: 250,
		autoHeightEasing: null
	});    
	
	$(document).on( 'cycle-initialized', function( e, opts ) {
		var autoHeight = opts.autoHeight;
		var t = $.type( autoHeight );
		var resizeThrottle = null;
		var ratio;
	
		if ( t !== 'string' && t !== 'number' )
			return;
	
		// bind events
		opts.container.on( 'cycle-slide-added cycle-slide-removed', initAutoHeight );
		opts.container.on( 'cycle-destroyed', onDestroy );
	
		if ( autoHeight == 'container' ) {
			opts.container.on( 'cycle-before', onBefore );
		}
		else if ( t === 'string' && /\d+\:\d+/.test( autoHeight ) ) { 
			// use ratio
			ratio = autoHeight.match(/(\d+)\:(\d+)/);
			ratio = ratio[1] / ratio[2];
			opts._autoHeightRatio = ratio;
		}
	
		// if autoHeight is a number then we don't need to recalculate the sentinel
		// index on resize
		if ( t !== 'number' ) {
			// bind unique resize handler per slideshow (so it can be 'off-ed' in onDestroy)
			opts._autoHeightOnResize = function () {
				clearTimeout( resizeThrottle );
				resizeThrottle = setTimeout( onResize, 50 );
			};
	
			$(window).on( 'resize orientationchange', opts._autoHeightOnResize );
		}
	
		setTimeout( onResize, 30 );
	
		function onResize() {
			initAutoHeight( e, opts );
		}
	});
	
	function initAutoHeight( e, opts ) {
		var clone, height, sentinelIndex;
		var autoHeight = opts.autoHeight;
	
		if ( autoHeight == 'container' ) {
			height = $( opts.slides[ opts.currSlide ] ).outerHeight();
			opts.container.height( height );
		}
		else if ( opts._autoHeightRatio ) { 
			opts.container.height( opts.container.width() / opts._autoHeightRatio );
		}
		else if ( autoHeight === 'calc' || ( $.type( autoHeight ) == 'number' && autoHeight >= 0 ) ) {
			if ( autoHeight === 'calc' )
				sentinelIndex = calcSentinelIndex( e, opts );
			else if ( autoHeight >= opts.slides.length )
				sentinelIndex = 0;
			else 
				sentinelIndex = autoHeight;
	
			// only recreate sentinel if index is different
			if ( sentinelIndex == opts._sentinelIndex )
				return;
	
			opts._sentinelIndex = sentinelIndex;
			if ( opts._sentinel )
				opts._sentinel.remove();
	
			// clone existing slide as sentinel
			clone = $( opts.slides[ sentinelIndex ].cloneNode(true) );
			
			// #50; remove special attributes from cloned content
			clone.removeAttr( 'id name rel' ).find( '[id],[name],[rel]' ).removeAttr( 'id name rel' );
	
			clone.css({
				position: 'static',
				visibility: 'hidden',
				display: 'block'
			}).prependTo( opts.container ).addClass('cycle-sentinel cycle-slide').removeClass('cycle-slide-active');
			clone.find( '*' ).css( 'visibility', 'hidden' );
	
			opts._sentinel = clone;
		}
	}    
	
	function calcSentinelIndex( e, opts ) {
		var index = 0, max = -1;
	
		// calculate tallest slide index
		opts.slides.each(function(i) {
			var h = $(this).height();
			if ( h > max ) {
				max = h;
				index = i;
			}
		});
		return index;
	}
	
	function onBefore( e, opts, outgoing, incoming, forward ) {
		var h = $(incoming).outerHeight();
		opts.container.animate( { height: h }, opts.autoHeightSpeed, opts.autoHeightEasing );
	}
	
	function onDestroy( e, opts ) {
		if ( opts._autoHeightOnResize ) {
			$(window).off( 'resize orientationchange', opts._autoHeightOnResize );
			opts._autoHeightOnResize = null;
		}
		opts.container.off( 'cycle-slide-added cycle-slide-removed', initAutoHeight );
		opts.container.off( 'cycle-destroyed', onDestroy );
		opts.container.off( 'cycle-before', onBefore );
	
		if ( opts._sentinel ) {
			opts._sentinel.remove();
			opts._sentinel = null;
		}
	}
	
	})(jQuery);
	
	/*! caption plugin for Cycle2;  version: 20130306 */
	(function($) {
	"use strict";
	
	$.extend($.fn.cycle.defaults, {
		caption:          '> .cycle-caption',
		captionTemplate:  '{{slideNum}} / {{slideCount}}',
		overlay:          '> .cycle-overlay',
		overlayTemplate:  '<div>{{title}}</div><div>{{desc}}</div>',
		captionModule:    'caption'
	});    
	
	$(document).on( 'cycle-update-view', function( e, opts, slideOpts, currSlide ) {
		if ( opts.captionModule !== 'caption' )
			return;
		var el;
		$.each(['caption','overlay'], function() {
			var name = this; 
			var template = slideOpts[name+'Template'];
			var el = opts.API.getComponent( name );
			if( el.length && template ) {
				el.html( opts.API.tmpl( template, slideOpts, opts, currSlide ) );
				el.show();
			}
			else {
				el.hide();
			}
		});
	});
	
	$(document).on( 'cycle-destroyed', function( e, opts ) {
		var el;
		$.each(['caption','overlay'], function() {
			var name = this, template = opts[name+'Template'];
			if ( opts[name] && template ) {
				el = opts.API.getComponent( 'caption' );
				el.empty();
			}
		});
	});
	
	})(jQuery);
	
	/*! command plugin for Cycle2;  version: 20140415 */
	(function($) {
	"use strict";
	
	var c2 = $.fn.cycle;
	
	$.fn.cycle = function( options ) {
		var cmd, cmdFn, opts;
		var args = $.makeArray( arguments );
	
		if ( $.type( options ) == 'number' ) {
			return this.cycle( 'goto', options );
		}
	
		if ( $.type( options ) == 'string' ) {
			return this.each(function() {
				var cmdArgs;
				cmd = options;
				opts = $(this).data('cycle.opts');
	
				if ( opts === undefined ) {
					c2.log('slideshow must be initialized before sending commands; "' + cmd + '" ignored');
					return;
				}
				else {
					cmd = cmd == 'goto' ? 'jump' : cmd; // issue #3; change 'goto' to 'jump' internally
					cmdFn = opts.API[ cmd ];
					if (typeof cmdFn === 'function') {
						cmdArgs = $.makeArray( args );
						cmdArgs.shift();
						return cmdFn.apply( opts.API, cmdArgs );
					}
					else {
						c2.log( 'unknown command: ', cmd );
					}
				}
			});
		}
		else {
			return c2.apply( this, arguments );
		}
	};
	
	// copy props
	$.extend( $.fn.cycle, c2 );
	
	$.extend( c2.API, {
		next: function() {
			var opts = this.opts();
			if ( opts.busy && ! opts.manualTrump )
				return;
	
			var count = opts.reverse ? -1 : 1;
			if ( opts.allowWrap === false && ( opts.currSlide + count ) >= opts.slideCount )
				return;
	
			opts.API.advanceSlide( count );
			opts.API.trigger('cycle-next', [ opts ]).log('cycle-next');
		},
	
		prev: function() {
			var opts = this.opts();
			if ( opts.busy && ! opts.manualTrump )
				return;
			var count = opts.reverse ? 1 : -1;
			if ( opts.allowWrap === false && ( opts.currSlide + count ) < 0 )
				return;
	
			opts.API.advanceSlide( count );
			opts.API.trigger('cycle-prev', [ opts ]).log('cycle-prev');
		},
	
		destroy: function() {
			this.stop(); //#204
	
			var opts = this.opts();
			var clean = typeof $._data === 'function' ? $._data : $.noop;  // hack for #184 and #201
			clearTimeout(opts.timeoutId);
			opts.timeoutId = 0;
			opts.API.stop();
			opts.API.trigger( 'cycle-destroyed', [ opts ] ).log('cycle-destroyed');
			opts.container.removeData();
			clean( opts.container[0], 'parsedAttrs', false );
	
			// #75; remove inline styles
			if ( ! opts.retainStylesOnDestroy ) {
				opts.container.removeAttr( 'style' );
				opts.slides.removeAttr( 'style' );
				opts.slides.removeClass( opts.slideActiveClass );
			}
			opts.slides.each(function() {
				var slide = $(this);
				slide.removeData();
				slide.removeClass( opts.slideClass );
				clean( this, 'parsedAttrs', false );
			});
		},
	
		jump: function( index, fx ) {
			// go to the requested slide
			var fwd;
			var opts = this.opts();
			if ( opts.busy && ! opts.manualTrump )
				return;
			var num = parseInt( index, 10 );
			if (isNaN(num) || num < 0 || num >= opts.slides.length) {
				opts.API.log('goto: invalid slide index: ' + num);
				return;
			}
			if (num == opts.currSlide) {
				opts.API.log('goto: skipping, already on slide', num);
				return;
			}
			opts.nextSlide = num;
			clearTimeout(opts.timeoutId);
			opts.timeoutId = 0;
			opts.API.log('goto: ', num, ' (zero-index)');
			fwd = opts.currSlide < opts.nextSlide;
			opts._tempFx = fx;
			opts.API.prepareTx( true, fwd );
		},
	
		stop: function() {
			var opts = this.opts();
			var pauseObj = opts.container;
			clearTimeout(opts.timeoutId);
			opts.timeoutId = 0;
			opts.API.stopTransition();
			if ( opts.pauseOnHover ) {
				if ( opts.pauseOnHover !== true )
					pauseObj = $( opts.pauseOnHover );
				pauseObj.off('mouseenter mouseleave');
			}
			opts.API.trigger('cycle-stopped', [ opts ]).log('cycle-stopped');
		},
	
		reinit: function() {
			var opts = this.opts();
			opts.API.destroy();
			opts.container.cycle();
		},
	
		remove: function( index ) {
			var opts = this.opts();
			var slide, slideToRemove, slides = [], slideNum = 1;
			for ( var i=0; i < opts.slides.length; i++ ) {
				slide = opts.slides[i];
				if ( i == index ) {
					slideToRemove = slide;
				}
				else {
					slides.push( slide );
					$( slide ).data('cycle.opts').slideNum = slideNum;
					slideNum++;
				}
			}
			if ( slideToRemove ) {
				opts.slides = $( slides );
				opts.slideCount--;
				$( slideToRemove ).remove();
				if (index == opts.currSlide)
					opts.API.advanceSlide( 1 );
				else if ( index < opts.currSlide )
					opts.currSlide--;
				else
					opts.currSlide++;
	
				opts.API.trigger('cycle-slide-removed', [ opts, index, slideToRemove ]).log('cycle-slide-removed');
				opts.API.updateView();
			}
		}
	
	});
	
	// listen for clicks on elements with data-cycle-cmd attribute
	$(document).on('click.cycle', '[data-cycle-cmd]', function(e) {
		// issue cycle command
		e.preventDefault();
		var el = $(this);
		var command = el.data('cycle-cmd');
		var context = el.data('cycle-context') || '.cycle-slideshow';
		$(context).cycle(command, el.data('cycle-arg'));
	});
	
	
	})(jQuery);
	
	/*! hash plugin for Cycle2;  version: 20130905 */
	(function($) {
	"use strict";
	
	$(document).on( 'cycle-pre-initialize', function( e, opts ) {
		onHashChange( opts, true );
	
		opts._onHashChange = function() {
			onHashChange( opts, false );
		};
	
		$( window ).on( 'hashchange', opts._onHashChange);
	});
	
	$(document).on( 'cycle-update-view', function( e, opts, slideOpts ) {
		if ( slideOpts.hash && ( '#' + slideOpts.hash ) != window.location.hash ) {
			opts._hashFence = true;
			window.location.hash = slideOpts.hash;
		}
	});
	
	$(document).on( 'cycle-destroyed', function( e, opts) {
		if ( opts._onHashChange ) {
			$( window ).off( 'hashchange', opts._onHashChange );
		}
	});
	
	function onHashChange( opts, setStartingSlide ) {
		var hash;
		if ( opts._hashFence ) {
			opts._hashFence = false;
			return;
		}
		
		hash = window.location.hash.substring(1);
	
		opts.slides.each(function(i) {
			if ( $(this).data( 'cycle-hash' ) == hash ) {
				if ( setStartingSlide === true ) {
					opts.startingSlide = i;
				}
				else {
					var fwd = opts.currSlide < i;
					opts.nextSlide = i;
					opts.API.prepareTx( true, fwd );
				}
				return false;
			}
		});
	}
	
	})(jQuery);
	
	/*! loader plugin for Cycle2;  version: 20131121 */
	(function($) {
	"use strict";
	
	$.extend($.fn.cycle.defaults, {
		loader: false
	});
	
	$(document).on( 'cycle-bootstrap', function( e, opts ) {
		var addFn;
	
		if ( !opts.loader )
			return;
	
		// override API.add for this slideshow
		addFn = opts.API.add;
		opts.API.add = add;
	
		function add( slides, prepend ) {
			var slideArr = [];
			if ( $.type( slides ) == 'string' )
				slides = $.trim( slides );
			else if ( $.type( slides) === 'array' ) {
				for (var i=0; i < slides.length; i++ )
					slides[i] = $(slides[i])[0];
			}
	
			slides = $( slides );
			var slideCount = slides.length;
	
			if ( ! slideCount )
				return;
	
			slides.css('visibility','hidden').appendTo('body').each(function(i) { // appendTo fixes #56
				var count = 0;
				var slide = $(this);
				var images = slide.is('img') ? slide : slide.find('img');
				slide.data('index', i);
				// allow some images to be marked as unimportant (and filter out images w/o src value)
				images = images.filter(':not(.cycle-loader-ignore)').filter(':not([src=""])');
				if ( ! images.length ) {
					--slideCount;
					slideArr.push( slide );
					return;
				}
	
				count = images.length;
				images.each(function() {
					// add images that are already loaded
					if ( this.complete ) {
						imageLoaded();
					}
					else {
						$(this).load(function() {
							imageLoaded();
						}).on("error", function() {
							if ( --count === 0 ) {
								// ignore this slide
								opts.API.log('slide skipped; img not loaded:', this.src);
								if ( --slideCount === 0 && opts.loader == 'wait') {
									addFn.apply( opts.API, [ slideArr, prepend ] );
								}
							}
						});
					}
				});
	
				function imageLoaded() {
					if ( --count === 0 ) {
						--slideCount;
						addSlide( slide );
					}
				}
			});
	
			if ( slideCount )
				opts.container.addClass('cycle-loading');
			
	
			function addSlide( slide ) {
				var curr;
				if ( opts.loader == 'wait' ) {
					slideArr.push( slide );
					if ( slideCount === 0 ) {
						// #59; sort slides into original markup order
						slideArr.sort( sorter );
						addFn.apply( opts.API, [ slideArr, prepend ] );
						opts.container.removeClass('cycle-loading');
					}
				}
				else {
					curr = $(opts.slides[opts.currSlide]);
					addFn.apply( opts.API, [ slide, prepend ] );
					curr.show();
					opts.container.removeClass('cycle-loading');
				}
			}
	
			function sorter(a, b) {
				return a.data('index') - b.data('index');
			}
		}
	});
	
	})(jQuery);
	
	/*! pager plugin for Cycle2;  version: 20140415 */
	(function($) {
	"use strict";
	
	$.extend($.fn.cycle.defaults, {
		pager:            '> .cycle-pager',
		pagerActiveClass: 'cycle-pager-active',
		pagerEvent:       'click.cycle',
		pagerEventBubble: undefined,
		pagerTemplate:    '<span>&bull;</span>'
	});
	
	$(document).on( 'cycle-bootstrap', function( e, opts, API ) {
		// add method to API
		API.buildPagerLink = buildPagerLink;
	});
	
	$(document).on( 'cycle-slide-added', function( e, opts, slideOpts, slideAdded ) {
		if ( opts.pager ) {
			opts.API.buildPagerLink ( opts, slideOpts, slideAdded );
			opts.API.page = page;
		}
	});
	
	$(document).on( 'cycle-slide-removed', function( e, opts, index, slideRemoved ) {
		if ( opts.pager ) {
			var pagers = opts.API.getComponent( 'pager' );
			pagers.each(function() {
				var pager = $(this);
				$( pager.children()[index] ).remove();
			});
		}
	});
	
	$(document).on( 'cycle-update-view', function( e, opts, slideOpts ) {
		var pagers;
	
		if ( opts.pager ) {
			pagers = opts.API.getComponent( 'pager' );
			pagers.each(function() {
			   $(this).children().removeClass( opts.pagerActiveClass )
				.eq( opts.currSlide ).addClass( opts.pagerActiveClass );
			});
		}
	});
	
	$(document).on( 'cycle-destroyed', function( e, opts ) {
		var pager = opts.API.getComponent( 'pager' );
	
		if ( pager ) {
			pager.children().off( opts.pagerEvent ); // #202
			if ( opts.pagerTemplate )
				pager.empty();
		}
	});
	
	function buildPagerLink( opts, slideOpts, slide ) {
		var pagerLink;
		var pagers = opts.API.getComponent( 'pager' );
		pagers.each(function() {
			var pager = $(this);
			if ( slideOpts.pagerTemplate ) {
				var markup = opts.API.tmpl( slideOpts.pagerTemplate, slideOpts, opts, slide[0] );
				pagerLink = $( markup ).appendTo( pager );
			}
			else {
				pagerLink = pager.children().eq( opts.slideCount - 1 );
			}
			pagerLink.on( opts.pagerEvent, function(e) {
				if ( ! opts.pagerEventBubble )
					e.preventDefault();
				opts.API.page( pager, e.currentTarget);
			});
		});
	}
	
	function page( pager, target ) {
		/*jshint validthis:true */
		var opts = this.opts();
		if ( opts.busy && ! opts.manualTrump )
			return;
	
		var index = pager.children().index( target );
		var nextSlide = index;
		var fwd = opts.currSlide < nextSlide;
		if (opts.currSlide == nextSlide) {
			return; // no op, clicked pager for the currently displayed slide
		}
		opts.nextSlide = nextSlide;
		opts._tempFx = opts.pagerFx;
		opts.API.prepareTx( true, fwd );
		opts.API.trigger('cycle-pager-activated', [opts, pager, target ]);
	}
	
	})(jQuery);
	
	/*! prevnext plugin for Cycle2;  version: 20140408 */
	(function($) {
	"use strict";
	
	$.extend($.fn.cycle.defaults, {
		next:           '> .cycle-next',
		nextEvent:      'click.cycle',
		disabledClass:  'disabled',
		prev:           '> .cycle-prev',
		prevEvent:      'click.cycle',
		swipe:          false
	});
	
	$(document).on( 'cycle-initialized', function( e, opts ) {
		opts.API.getComponent( 'next' ).on( opts.nextEvent, function(e) {
			e.preventDefault();
			opts.API.next();
		});
	
		opts.API.getComponent( 'prev' ).on( opts.prevEvent, function(e) {
			e.preventDefault();
			opts.API.prev();
		});
	
		if ( opts.swipe ) {
			var nextEvent = opts.swipeVert ? 'swipeUp.cycle' : 'swipeLeft.cycle swipeleft.cycle';
			var prevEvent = opts.swipeVert ? 'swipeDown.cycle' : 'swipeRight.cycle swiperight.cycle';
			opts.container.on( nextEvent, function(e) {
				opts._tempFx = opts.swipeFx;
				opts.API.next();
			});
			opts.container.on( prevEvent, function() {
				opts._tempFx = opts.swipeFx;
				opts.API.prev();
			});
		}
	});
	
	$(document).on( 'cycle-update-view', function( e, opts, slideOpts, currSlide ) {
		if ( opts.allowWrap )
			return;
	
		var cls = opts.disabledClass;
		var next = opts.API.getComponent( 'next' );
		var prev = opts.API.getComponent( 'prev' );
		var prevBoundry = opts._prevBoundry || 0;
		var nextBoundry = (opts._nextBoundry !== undefined)?opts._nextBoundry:opts.slideCount - 1;
	
		if ( opts.currSlide == nextBoundry )
			next.addClass( cls ).prop( 'disabled', true );
		else
			next.removeClass( cls ).prop( 'disabled', false );
	
		if ( opts.currSlide === prevBoundry )
			prev.addClass( cls ).prop( 'disabled', true );
		else
			prev.removeClass( cls ).prop( 'disabled', false );
	});
	
	
	$(document).on( 'cycle-destroyed', function( e, opts ) {
		opts.API.getComponent( 'prev' ).off( opts.nextEvent );
		opts.API.getComponent( 'next' ).off( opts.prevEvent );
		opts.container.off( 'swipeleft.cycle swiperight.cycle swipeLeft.cycle swipeRight.cycle swipeUp.cycle swipeDown.cycle' );
	});
	
	})(jQuery);
	
	/*! progressive loader plugin for Cycle2;  version: 20130315 */
	(function($) {
	"use strict";
	
	$.extend($.fn.cycle.defaults, {
		progressive: false
	});
	
	$(document).on( 'cycle-pre-initialize', function( e, opts ) {
		if ( !opts.progressive )
			return;
	
		var API = opts.API;
		var nextFn = API.next;
		var prevFn = API.prev;
		var prepareTxFn = API.prepareTx;
		var type = $.type( opts.progressive );
		var slides, scriptEl;
	
		if ( type == 'array' ) {
			slides = opts.progressive;
		}
		else if (typeof opts.progressive === 'function') {
			slides = opts.progressive( opts );
		}
		else if ( type == 'string' ) {
			scriptEl = $( opts.progressive );
			slides = $.trim( scriptEl.html() );
			if ( !slides )
				return;
			// is it json array?
			if (/^(\[)/.test(slides)) {
				try {
					slides = JSON.parse(slides);
				} catch (err) {
					API.log('error parsing progressive slides', err);
					return;
				}
			}
			else {
				// plain text, split on delimeter
				slides = slides.split( new RegExp( scriptEl.data('cycle-split') || '\n') );
				
				// #95; look for empty slide
				if ( ! slides[ slides.length - 1 ] )
					slides.pop();
			}
		}
	
	
	
		if ( prepareTxFn ) {
			API.prepareTx = function( manual, fwd ) {
				var index, slide;
	
				if ( manual || slides.length === 0 ) {
					prepareTxFn.apply( opts.API, [ manual, fwd ] );
					return;
				}
	
				if ( fwd && opts.currSlide == ( opts.slideCount-1) ) {
					slide = slides[ 0 ];
					slides = slides.slice( 1 );
					opts.container.one('cycle-slide-added', function(e, opts ) {
						setTimeout(function() {
							opts.API.advanceSlide( 1 );
						},50);
					});
					opts.API.add( slide );
				}
				else if ( !fwd && opts.currSlide === 0 ) {
					index = slides.length-1;
					slide = slides[ index ];
					slides = slides.slice( 0, index );
					opts.container.one('cycle-slide-added', function(e, opts ) {
						setTimeout(function() {
							opts.currSlide = 1;
							opts.API.advanceSlide( -1 );
						},50);
					});
					opts.API.add( slide, true );
				}
				else {
					prepareTxFn.apply( opts.API, [ manual, fwd ] );
				}
			};
		}
	
		if ( nextFn ) {
			API.next = function() {
				var opts = this.opts();
				if ( slides.length && opts.currSlide == ( opts.slideCount - 1 ) ) {
					var slide = slides[ 0 ];
					slides = slides.slice( 1 );
					opts.container.one('cycle-slide-added', function(e, opts ) {
						nextFn.apply( opts.API );
						opts.container.removeClass('cycle-loading');
					});
					opts.container.addClass('cycle-loading');
					opts.API.add( slide );
				}
				else {
					nextFn.apply( opts.API );    
				}
			};
		}
		
		if ( prevFn ) {
			API.prev = function() {
				var opts = this.opts();
				if ( slides.length && opts.currSlide === 0 ) {
					var index = slides.length-1;
					var slide = slides[ index ];
					slides = slides.slice( 0, index );
					opts.container.one('cycle-slide-added', function(e, opts ) {
						opts.currSlide = 1;
						opts.API.advanceSlide( -1 );
						opts.container.removeClass('cycle-loading');
					});
					opts.container.addClass('cycle-loading');
					opts.API.add( slide, true );
				}
				else {
					prevFn.apply( opts.API );
				}
			};
		}
	});
	
	})(jQuery);
	
	/*! tmpl plugin for Cycle2;  version: 20121227 */
	(function($) {
	"use strict";
	
	$.extend($.fn.cycle.defaults, {
		tmplRegex: '{{((.)?.*?)}}'
	});
	
	$.extend($.fn.cycle.API, {
		tmpl: function( str, opts /*, ... */) {
			var regex = new RegExp( opts.tmplRegex || $.fn.cycle.defaults.tmplRegex, 'g' );
			var args = $.makeArray( arguments );
			args.shift();
			return str.replace(regex, function(_, str) {
				var i, j, obj, prop, names = str.split('.');
				for (i=0; i < args.length; i++) {
					obj = args[i];
					if ( ! obj )
						continue;
					if (names.length > 1) {
						prop = obj;
						for (j=0; j < names.length; j++) {
							obj = prop;
							prop = prop[ names[j] ] || str;
						}
					} else {
						prop = obj[str];
					}
	
					if (typeof prop === 'function')
						return prop.apply(obj, args);
					if (prop !== undefined && prop !== null && prop != str)
						return prop;
				}
				return str;
			});
		}
	});    
	
	})(jQuery);
		return;
	

	// stop cycling if we have an outstanding stop request
	if (p.cycleStop != opts.stopCount || p.cycleTimeout === 0 && !manual)
		return;

	// check to see if we should stop cycling based on autostop options
	if (!manual && !p.cyclePause && !opts.bounce &&
		((opts.autostop && (--opts.countdown <= 0)) ||
		(opts.nowrap && !opts.random && opts.nextSlide < opts.currSlide))) {
		if (opts.end)
			opts.end(opts);
		return;
	}

	// if slideshow is paused, only transition on a manual trigger
	var changed = false;
	if ((manual || !p.cyclePause) && (opts.nextSlide != opts.currSlide)) {
		changed = true;
		var fx = opts.fx;
		// keep trying to get the slide size if we don't have it yet
		curr.cycleH = curr.cycleH || $(curr).height();
		curr.cycleW = curr.cycleW || $(curr).width();
		next.cycleH = next.cycleH || $(next).height();
		next.cycleW = next.cycleW || $(next).width();

		// support multiple transition types
		if (opts.multiFx) {
			if (fwd && (opts.lastFx === undefined || ++opts.lastFx >= opts.fxs.length))
				opts.lastFx = 0;
			else if (!fwd && (opts.lastFx === undefined || --opts.lastFx < 0))
				opts.lastFx = opts.fxs.length - 1;
			fx = opts.fxs[opts.lastFx];
		}

		// one-time fx overrides apply to:  $('div').cycle(3,'zoom');
		if (opts.oneTimeFx) {
			fx = opts.oneTimeFx;
			opts.oneTimeFx = null;
		}

		$.fn.cycle.resetState(opts, fx);

		// run the before callbacks
		if (opts.before.length)
			$.each(opts.before, function(i,o) {
				if (p.cycleStop != opts.stopCount) return;
				o.apply(next, [curr, next, opts, fwd]);
			});

		// stage the after callacks
		var after = function() {
			opts.busy = 0;
			$.each(opts.after, function(i,o) {
				if (p.cycleStop != opts.stopCount) return;
				o.apply(next, [curr, next, opts, fwd]);
			});
			if (!p.cycleStop) {
				// queue next transition
				queueNext();
			}
		};

		debug('tx firing('+fx+'); currSlide: ' + opts.currSlide + '; nextSlide: ' + opts.nextSlide);
		
		// get ready to perform the transition
		opts.busy = 1;
		if (opts.fxFn) // fx function provided?
			opts.fxFn(curr, next, opts, after, fwd, manual && opts.fastOnEvent);
			else if (typeof $.fn.cycle[opts.fx] === 'function') // fx plugin ?
			$.fn.cycle[opts.fx](curr, next, opts, after, fwd, manual && opts.fastOnEvent);
		else
			$.fn.cycle.custom(curr, next, opts, after, fwd, manual && opts.fastOnEvent);
	}
	else {
		queueNext();
	}

	if (changed || opts.nextSlide == opts.currSlide) {
		// calculate the next slide
		var roll;
		opts.lastSlide = opts.currSlide;
		if (opts.random) {
			opts.currSlide = opts.nextSlide;
			if (++opts.randomIndex == els.length) {
				opts.randomIndex = 0;
				opts.randomMap.sort(function(a,b) {return Math.random() - 0.5;});
			}
			opts.nextSlide = opts.randomMap[opts.randomIndex];
			if (opts.nextSlide == opts.currSlide)
				opts.nextSlide = (opts.currSlide == opts.slideCount - 1) ? 0 : opts.currSlide + 1;
		}
		else if (opts.backwards) {
			roll = (opts.nextSlide - 1) < 0;
			if (roll && opts.bounce) {
				opts.backwards = !opts.backwards;
				opts.nextSlide = 1;
				opts.currSlide = 0;
			}
			else {
				opts.nextSlide = roll ? (els.length-1) : opts.nextSlide-1;
				opts.currSlide = roll ? 0 : opts.nextSlide+1;
			}
		}
		else { // sequence
			roll = (opts.nextSlide + 1) == els.length;
			if (roll && opts.bounce) {
				opts.backwards = !opts.backwards;
				opts.nextSlide = els.length-2;
				opts.currSlide = els.length-1;
			}
			else {
				opts.nextSlide = roll ? 0 : opts.nextSlide+1;
				opts.currSlide = roll ? els.length-1 : opts.nextSlide-1;
			}
		}
	}
	if (changed && opts.pager)
		opts.updateActivePagerLink(opts.pager, opts.currSlide, opts.activePagerClass);
	
	function queueNext() {
		// stage the next transition
		var ms = 0, timeout = opts.timeout;
		if (opts.timeout && !opts.continuous) {
			ms = getTimeout(els[opts.currSlide], els[opts.nextSlide], opts, fwd);
         if (opts.fx == 'shuffle')
            ms -= opts.speedOut;
      }
		else if (opts.continuous && p.cyclePause) // continuous shows work off an after callback, not this timer logic
			ms = 10;
		if (ms > 0)
			p.cycleTimeout = setTimeout(function(){ go(els, opts, 0, !opts.backwards); }, ms);
	}
}

// invoked after transition
$.fn.cycle.updateActivePagerLink = function(pager, currSlide, clsName) {
   $(pager).each(function() {
       $(this).children().removeClass(clsName).eq(currSlide).addClass(clsName);
   });
};

// calculate timeout value for current transition
function getTimeout(curr, next, opts, fwd) {
	if (opts.timeoutFn) {
		// call user provided calc fn
		var t = opts.timeoutFn.call(curr,curr,next,opts,fwd);
		while (opts.fx != 'none' && (t - opts.speed) < 250) // sanitize timeout
			t += opts.speed;
		debug('calculated timeout: ' + t + '; speed: ' + opts.speed);
		if (t !== false)
			return t;
	}
	return opts.timeout;
}

// expose next/prev function, caller must pass in state
$.fn.cycle.next = function(opts) { advance(opts,1); };
$.fn.cycle.prev = function(opts) { advance(opts,0);};

// advance slide forward or back
function advance(opts, moveForward) {
	var val = moveForward ? 1 : -1;
	var els = opts.elements;
	var p = opts.$cont[0], timeout = p.cycleTimeout;
	if (timeout) {
		clearTimeout(timeout);
		p.cycleTimeout = 0;
	}
	if (opts.random && val < 0) {
		// move back to the previously display slide
		opts.randomIndex--;
		if (--opts.randomIndex == -2)
			opts.randomIndex = els.length-2;
		else if (opts.randomIndex == -1)
			opts.randomIndex = els.length-1;
		opts.nextSlide = opts.randomMap[opts.randomIndex];
	}
	else if (opts.random) {
		opts.nextSlide = opts.randomMap[opts.randomIndex];
	}
	else {
		opts.nextSlide = opts.currSlide + val;
		if (opts.nextSlide < 0) {
			if (opts.nowrap) return false;
			opts.nextSlide = els.length - 1;
		}
		else if (opts.nextSlide >= els.length) {
			if (opts.nowrap) return false;
			opts.nextSlide = 0;
		}
	}

	var cb = opts.onPrevNextEvent || opts.prevNextClick; // prevNextClick is deprecated
	if (typeof cb === 'function')
		cb(val > 0, opts.nextSlide, els[opts.nextSlide]);
	go(els, opts, 1, moveForward);
	return false;
}

function buildPager(els, opts) {
	var $p = $(opts.pager);
	$.each(els, function(i,o) {
		$.fn.cycle.createPagerAnchor(i,o,$p,els,opts);
	});
	opts.updateActivePagerLink(opts.pager, opts.startingSlide, opts.activePagerClass);
}

$.fn.cycle.createPagerAnchor = function(i, el, $p, els, opts) {
	var a;
	if (typeof opts.pagerAnchorBuilder === 'function') {
		a = opts.pagerAnchorBuilder(i,el);
		debug('pagerAnchorBuilder('+i+', el) returned: ' + a);
	}
	else
		a = '<a href="#">'+(i+1)+'</a>';
		
	if (!a)
		return;
	var $a = $(a);
	// don't reparent if anchor is in the dom
	if ($a.parents('body').length === 0) {
		var arr = [];
		if ($p.length > 1) {
			$p.each(function() {
				var $clone = $a.clone(true);
				$(this).append($clone);
				arr.push($clone[0]);
			});
			$a = $(arr);
		}
		else {
			$a.appendTo($p);
		}
	}

	opts.pagerAnchors =  opts.pagerAnchors || [];
	opts.pagerAnchors.push($a);
	
	var pagerFn = function(e) {
		e.preventDefault();
		opts.nextSlide = i;
		var p = opts.$cont[0], timeout = p.cycleTimeout;
		if (timeout) {
			clearTimeout(timeout);
			p.cycleTimeout = 0;
		}
		var cb = opts.onPagerEvent || opts.pagerClick; // pagerClick is deprecated
		if (typeof cb === 'function')
			cb(opts.nextSlide, els[opts.nextSlide]);
		go(els,opts,1,opts.currSlide < i); // trigger the trans
//		return false; // <== allow bubble
	};
	
	if ( /mouseenter|mouseover/i.test(opts.pagerEvent) ) {
		$a.on('mouseenter',pagerFn, function(){/* no-op */} );
	}
	else {
		$a.on(opts.pagerEvent, pagerFn);
	}
	
	if ( ! /^click/.test(opts.pagerEvent) && !opts.allowPagerClickBubble)
		$a.on('click.cycle', function(){return false;}); // suppress click
	
	var cont = opts.$cont[0];
	var pauseFlag = false; // https://github.com/malsup/cycle/issues/44
	if (opts.pauseOnPagerHover) {
		$a.on('mouseenter',
			function() { 
				pauseFlag = true;
				cont.cyclePause++; 
				triggerPause(cont,true,true);
			}, function() { 
				if (pauseFlag)
					cont.cyclePause--; 
				triggerPause(cont,true,true);
			} 
		);
	}
};

// helper fn to calculate the number of slides between the current and the next
$.fn.cycle.hopsFromLast = function(opts, fwd) {
	var hops, l = opts.lastSlide, c = opts.currSlide;
	if (fwd)
		hops = c > l ? c - l : opts.slideCount - l;
	else
		hops = c < l ? l - c : l + opts.slideCount - c;
	return hops;
};

// fix clearType problems in ie6 by setting an explicit bg color
// (otherwise text slides look horrible during a fade transition)
function clearTypeFix($slides) {
	debug('applying clearType background-color hack');
	function hex(s) {
		s = parseInt(s,10).toString(16);
		return s.length < 2 ? '0'+s : s;
	}
	function getBg(e) {
		for ( ; e && e.nodeName.toLowerCase() != 'html'; e = e.parentNode) {
			var v = $.css(e,'background-color');
			if (v && v.indexOf('rgb') >= 0 ) {
				var rgb = v.match(/\d+/g);
				return '#'+ hex(rgb[0]) + hex(rgb[1]) + hex(rgb[2]);
			}
			if (v && v != 'transparent')
				return v;
		}
		return '#ffffff';
	}
	$slides.each(function() { $(this).css('background-color', getBg(this)); });
}

// reset common props before the next transition
$.fn.cycle.commonReset = function(curr,next,opts,w,h,rev) {
	$(opts.elements).not(curr).hide();
	if (typeof opts.cssBefore.opacity == 'undefined')
		opts.cssBefore.opacity = 1;
	opts.cssBefore.display = 'block';
	if (opts.slideResize && w !== false && next.cycleW > 0)
		opts.cssBefore.width = next.cycleW;
	if (opts.slideResize && h !== false && next.cycleH > 0)
		opts.cssBefore.height = next.cycleH;
	opts.cssAfter = opts.cssAfter || {};
	opts.cssAfter.display = 'none';
	$(curr).css('zIndex',opts.slideCount + (rev === true ? 1 : 0));
	$(next).css('zIndex',opts.slideCount + (rev === true ? 0 : 1));
};

// the actual fn for effecting a transition
$.fn.cycle.custom = function(curr, next, opts, cb, fwd, speedOverride) {
	var $l = $(curr), $n = $(next);
	var speedIn = opts.speedIn, speedOut = opts.speedOut, easeIn = opts.easeIn, easeOut = opts.easeOut;
	$n.css(opts.cssBefore);
	if (speedOverride) {
		if (typeof speedOverride == 'number')
			speedIn = speedOut = speedOverride;
		else
			speedIn = speedOut = 1;
		easeIn = easeOut = null;
	}
	var fn = function() {
		$n.animate(opts.animIn, speedIn, easeIn, function() {
			cb();
		});
	};
	$l.animate(opts.animOut, speedOut, easeOut, function() {
		$l.css(opts.cssAfter);
		if (!opts.sync) 
			fn();
	});
	if (opts.sync) fn();
};

// transition definitions - only fade is defined here, transition pack defines the rest
$.fn.cycle.transitions = {
	fade: function($cont, $slides, opts) {
		$slides.not(':eq('+opts.currSlide+')').css('opacity',0);
		opts.before.push(function(curr,next,opts) {
			$.fn.cycle.commonReset(curr,next,opts);
			opts.cssBefore.opacity = 0;
		});
		opts.animIn	   = { opacity: 1 };
		opts.animOut   = { opacity: 0 };
		opts.cssBefore = { top: 0, left: 0 };
	}
};

$.fn.cycle.ver = function() { return ver; };

// override these globally if you like (they are all optional)
$.fn.cycle.defaults = {
    activePagerClass: 'activeSlide', // class name used for the active pager link
    after:            null,     // transition callback (scope set to element that was shown):  function(currSlideElement, nextSlideElement, options, forwardFlag)
    allowPagerClickBubble: false, // allows or prevents click event on pager anchors from bubbling
    animIn:           null,     // properties that define how the slide animates in
    animOut:          null,     // properties that define how the slide animates out
    aspect:           false,    // preserve aspect ratio during fit resizing, cropping if necessary (must be used with fit option)
    autostop:         0,        // true to end slideshow after X transitions (where X == slide count)
    autostopCount:    0,        // number of transitions (optionally used with autostop to define X)
    backwards:        false,    // true to start slideshow at last slide and move backwards through the stack
    before:           null,     // transition callback (scope set to element to be shown):     function(currSlideElement, nextSlideElement, options, forwardFlag)
    center:           null,     // set to true to have cycle add top/left margin to each slide (use with width and height options)
    cleartype:        !$.support.opacity,  // true if clearType corrections should be applied (for IE)
    cleartypeNoBg:    false,    // set to true to disable extra cleartype fixing (leave false to force background color setting on slides)
    containerResize:  1,        // resize container to fit largest slide
    containerResizeHeight:  0,  // resize containers height to fit the largest slide but leave the width dynamic
    continuous:       0,        // true to start next transition immediately after current one completes
    cssAfter:         null,     // properties that defined the state of the slide after transitioning out
    cssBefore:        null,     // properties that define the initial state of the slide before transitioning in
    delay:            0,        // additional delay (in ms) for first transition (hint: can be negative)
    easeIn:           null,     // easing for "in" transition
    easeOut:          null,     // easing for "out" transition
    easing:           null,     // easing method for both in and out transitions
    end:              null,     // callback invoked when the slideshow terminates (use with autostop or nowrap options): function(options)
    fastOnEvent:      0,        // force fast transitions when triggered manually (via pager or prev/next); value == time in ms
    fit:              0,        // force slides to fit container
    fx:               'fade',   // name of transition effect (or comma separated names, ex: 'fade,scrollUp,shuffle')
    fxFn:             null,     // function used to control the transition: function(currSlideElement, nextSlideElement, options, afterCalback, forwardFlag)
    height:           'auto',   // container height (if the 'fit' option is true, the slides will be set to this height as well)
    manualTrump:      true,     // causes manual transition to stop an active transition instead of being ignored
    metaAttr:         'cycle',  // data- attribute that holds the option data for the slideshow
    next:             null,     // element, jQuery object, or jQuery selector string for the element to use as event trigger for next slide
    nowrap:           0,        // true to prevent slideshow from wrapping
    onPagerEvent:     null,     // callback fn for pager events: function(zeroBasedSlideIndex, slideElement)
    onPrevNextEvent:  null,     // callback fn for prev/next events: function(isNext, zeroBasedSlideIndex, slideElement)
    pager:            null,     // element, jQuery object, or jQuery selector string for the element to use as pager container
    pagerAnchorBuilder: null,   // callback fn for building anchor links:  function(index, DOMelement)
    pagerEvent:       'click.cycle', // name of event which drives the pager navigation
    pause:            0,        // true to enable "pause on hover"
    pauseOnPagerHover: 0,       // true to pause when hovering over pager link
    prev:             null,     // element, jQuery object, or jQuery selector string for the element to use as event trigger for previous slide
    prevNextEvent:    'click.cycle',// event which drives the manual transition to the previous or next slide
    random:           0,        // true for random, false for sequence (not applicable to shuffle fx)
    randomizeEffects: 1,        // valid when multiple effects are used; true to make the effect sequence random
    requeueOnImageNotLoaded: true, // requeue the slideshow if any image slides are not yet loaded
    requeueTimeout:   250,      // ms delay for requeue
    rev:              0,        // causes animations to transition in reverse (for effects that support it such as scrollHorz/scrollVert/shuffle)
    shuffle:          null,     // coords for shuffle animation, ex: { top:15, left: 200 }
    skipInitializationCallbacks: false, // set to true to disable the first before/after callback that occurs prior to any transition
    slideExpr:        null,     // expression for selecting slides (if something other than all children is required)
    slideResize:      1,        // force slide width/height to fixed size before every transition
    speed:            1000,     // speed of the transition (any valid fx speed value)
    speedIn:          null,     // speed of the 'in' transition
    speedOut:         null,     // speed of the 'out' transition
    startingSlide:    undefined,// zero-based index of the first slide to be displayed
    sync:             1,        // true if in/out transitions should occur simultaneously
    timeout:          4000,     // milliseconds between slide transitions (0 to disable auto advance)
    timeoutFn:        null,     // callback for determining per-slide timeout value:  function(currSlideElement, nextSlideElement, options, forwardFlag)
    updateActivePagerLink: null,// callback fn invoked to update the active pager link (adds/removes activePagerClass style)
    width:            null      // container width (if the 'fit' option is true, the slides will be set to this width as well)
};

})(jQuery);


/*!
 * jQuery Cycle Plugin Transition Definitions
 * This script is a plugin for the jQuery Cycle Plugin
 * Examples and documentation at: http://malsup.com/jquery/cycle/
 * Copyright (c) 2007-2010 M. Alsup
 * Version:	 2.73
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 */
(function($) {
"use strict";

//
// These functions define slide initialization and properties for the named
// transitions. To save file size feel free to remove any of these that you
// don't need.
//
$.fn.cycle.transitions.none = function($cont, $slides, opts) {
	opts.fxFn = function(curr,next,opts,after){
		$(next).show();
		$(curr).hide();
		after();
	};
};

// not a cross-fade, fadeout only fades out the top slide
$.fn.cycle.transitions.fadeout = function($cont, $slides, opts) {
	$slides.not(':eq('+opts.currSlide+')').css({ display: 'block', 'opacity': 1 });
	opts.before.push(function(curr,next,opts,w,h,rev) {
		$(curr).css('zIndex',opts.slideCount + (rev !== true ? 1 : 0));
		$(next).css('zIndex',opts.slideCount + (rev !== true ? 0 : 1));
	});
	opts.animIn.opacity = 1;
	opts.animOut.opacity = 0;
	opts.cssBefore.opacity = 1;
	opts.cssBefore.display = 'block';
	opts.cssAfter.zIndex = 0;
};

// scrollUp/Down/Left/Right
$.fn.cycle.transitions.scrollUp = function($cont, $slides, opts) {
	$cont.css('overflow','hidden');
	opts.before.push($.fn.cycle.commonReset);
	var h = $cont.height();
	opts.cssBefore.top = h;
	opts.cssBefore.left = 0;
	opts.cssFirst.top = 0;
	opts.animIn.top = 0;
	opts.animOut.top = -h;
};
$.fn.cycle.transitions.scrollDown = function($cont, $slides, opts) {
	$cont.css('overflow','hidden');
	opts.before.push($.fn.cycle.commonReset);
	var h = $cont.height();
	opts.cssFirst.top = 0;
	opts.cssBefore.top = -h;
	opts.cssBefore.left = 0;
	opts.animIn.top = 0;
	opts.animOut.top = h;
};
$.fn.cycle.transitions.scrollLeft = function($cont, $slides, opts) {
	$cont.css('overflow','hidden');
	opts.before.push($.fn.cycle.commonReset);
	var w = $cont.width();
	opts.cssFirst.left = 0;
	opts.cssBefore.left = w;
	opts.cssBefore.top = 0;
	opts.animIn.left = 0;
	opts.animOut.left = 0-w;
};
$.fn.cycle.transitions.scrollRight = function($cont, $slides, opts) {
	$cont.css('overflow','hidden');
	opts.before.push($.fn.cycle.commonReset);
	var w = $cont.width();
	opts.cssFirst.left = 0;
	opts.cssBefore.left = -w;
	opts.cssBefore.top = 0;
	opts.animIn.left = 0;
	opts.animOut.left = w;
};
$.fn.cycle.transitions.scrollHorz = function($cont, $slides, opts) {
	$cont.css('overflow','hidden').width();
	opts.before.push(function(curr, next, opts, fwd) {
		if (opts.rev)
			fwd = !fwd;
		$.fn.cycle.commonReset(curr,next,opts);
		opts.cssBefore.left = fwd ? (next.cycleW-1) : (1-next.cycleW);
		opts.animOut.left = fwd ? -curr.cycleW : curr.cycleW;
	});
	opts.cssFirst.left = 0;
	opts.cssBefore.top = 0;
	opts.animIn.left = 0;
	opts.animOut.top = 0;
};
$.fn.cycle.transitions.scrollVert = function($cont, $slides, opts) {
	$cont.css('overflow','hidden');
	opts.before.push(function(curr, next, opts, fwd) {
		if (opts.rev)
			fwd = !fwd;
		$.fn.cycle.commonReset(curr,next,opts);
		opts.cssBefore.top = fwd ? (1-next.cycleH) : (next.cycleH-1);
		opts.animOut.top = fwd ? curr.cycleH : -curr.cycleH;
	});
	opts.cssFirst.top = 0;
	opts.cssBefore.left = 0;
	opts.animIn.top = 0;
	opts.animOut.left = 0;
};

// slideX/slideY
$.fn.cycle.transitions.slideX = function($cont, $slides, opts) {
	opts.before.push(function(curr, next, opts) {
		$(opts.elements).not(curr).hide();
		$.fn.cycle.commonReset(curr,next,opts,false,true);
		opts.animIn.width = next.cycleW;
	});
	opts.cssBefore.left = 0;
	opts.cssBefore.top = 0;
	opts.cssBefore.width = 0;
	opts.animIn.width = 'show';
	opts.animOut.width = 0;
};
$.fn.cycle.transitions.slideY = function($cont, $slides, opts) {
	opts.before.push(function(curr, next, opts) {
		$(opts.elements).not(curr).hide();
		$.fn.cycle.commonReset(curr,next,opts,true,false);
		opts.animIn.height = next.cycleH;
	});
	opts.cssBefore.left = 0;
	opts.cssBefore.top = 0;
	opts.cssBefore.height = 0;
	opts.animIn.height = 'show';
	opts.animOut.height = 0;
};

// shuffle
$.fn.cycle.transitions.shuffle = function($cont, $slides, opts) {
	var i, w = $cont.css('overflow', 'visible').width();
	$slides.css({left: 0, top: 0});
	opts.before.push(function(curr,next,opts) {
		$.fn.cycle.commonReset(curr,next,opts,true,true,true);
	});
	// only adjust speed once!
	if (!opts.speedAdjusted) {
		opts.speed = opts.speed / 2; // shuffle has 2 transitions
		opts.speedAdjusted = true;
	}
	opts.random = 0;
	opts.shuffle = opts.shuffle || {left:-w, top:15};
	opts.els = [];
	for (i=0; i < $slides.length; i++)
		opts.els.push($slides[i]);

	for (i=0; i < opts.currSlide; i++)
		opts.els.push(opts.els.shift());

	// custom transition fn (hat tip to Benjamin Sterling for this bit of sweetness!)
	opts.fxFn = function(curr, next, opts, cb, fwd) {
		if (opts.rev)
			fwd = !fwd;
		var $el = fwd ? $(curr) : $(next);
		$(next).css(opts.cssBefore);
		var count = opts.slideCount;
		$el.animate(opts.shuffle, opts.speedIn, opts.easeIn, function() {
			var hops = $.fn.cycle.hopsFromLast(opts, fwd);
			for (var k=0; k < hops; k++) {
				if (fwd)
					opts.els.push(opts.els.shift());
				else
					opts.els.unshift(opts.els.pop());
			}
			if (fwd) {
				for (var i=0, len=opts.els.length; i < len; i++)
					$(opts.els[i]).css('z-index', len-i+count);
			}
			else {
				var z = $(curr).css('z-index');
				$el.css('z-index', parseInt(z,10)+1+count);
			}
			$el.animate({left:0, top:0}, opts.speedOut, opts.easeOut, function() {
				$(fwd ? this : curr).hide();
				if (cb) cb();
			});
		});
	};
	$.extend(opts.cssBefore, { display: 'block', opacity: 1, top: 0, left: 0 });
};

// turnUp/Down/Left/Right
$.fn.cycle.transitions.turnUp = function($cont, $slides, opts) {
	opts.before.push(function(curr, next, opts) {
		$.fn.cycle.commonReset(curr,next,opts,true,false);
		opts.cssBefore.top = next.cycleH;
		opts.animIn.height = next.cycleH;
		opts.animOut.width = next.cycleW;
	});
	opts.cssFirst.top = 0;
	opts.cssBefore.left = 0;
	opts.cssBefore.height = 0;
	opts.animIn.top = 0;
	opts.animOut.height = 0;
};
$.fn.cycle.transitions.turnDown = function($cont, $slides, opts) {
	opts.before.push(function(curr, next, opts) {
		$.fn.cycle.commonReset(curr,next,opts,true,false);
		opts.animIn.height = next.cycleH;
		opts.animOut.top   = curr.cycleH;
	});
	opts.cssFirst.top = 0;
	opts.cssBefore.left = 0;
	opts.cssBefore.top = 0;
	opts.cssBefore.height = 0;
	opts.animOut.height = 0;
};
$.fn.cycle.transitions.turnLeft = function($cont, $slides, opts) {
	opts.before.push(function(curr, next, opts) {
		$.fn.cycle.commonReset(curr,next,opts,false,true);
		opts.cssBefore.left = next.cycleW;
		opts.animIn.width = next.cycleW;
	});
	opts.cssBefore.top = 0;
	opts.cssBefore.width = 0;
	opts.animIn.left = 0;
	opts.animOut.width = 0;
};
$.fn.cycle.transitions.turnRight = function($cont, $slides, opts) {
	opts.before.push(function(curr, next, opts) {
		$.fn.cycle.commonReset(curr,next,opts,false,true);
		opts.animIn.width = next.cycleW;
		opts.animOut.left = curr.cycleW;
	});
	$.extend(opts.cssBefore, { top: 0, left: 0, width: 0 });
	opts.animIn.left = 0;
	opts.animOut.width = 0;
};

// zoom
$.fn.cycle.transitions.zoom = function($cont, $slides, opts) {
	opts.before.push(function(curr, next, opts) {
		$.fn.cycle.commonReset(curr,next,opts,false,false,true);
		opts.cssBefore.top = next.cycleH/2;
		opts.cssBefore.left = next.cycleW/2;
		$.extend(opts.animIn, { top: 0, left: 0, width: next.cycleW, height: next.cycleH });
		$.extend(opts.animOut, { width: 0, height: 0, top: curr.cycleH/2, left: curr.cycleW/2 });
	});
	opts.cssFirst.top = 0;
	opts.cssFirst.left = 0;
	opts.cssBefore.width = 0;
	opts.cssBefore.height = 0;
};

// fadeZoom
$.fn.cycle.transitions.fadeZoom = function($cont, $slides, opts) {
	opts.before.push(function(curr, next, opts) {
		$.fn.cycle.commonReset(curr,next,opts,false,false);
		opts.cssBefore.left = next.cycleW/2;
		opts.cssBefore.top = next.cycleH/2;
		$.extend(opts.animIn, { top: 0, left: 0, width: next.cycleW, height: next.cycleH });
	});
	opts.cssBefore.width = 0;
	opts.cssBefore.height = 0;
	opts.animOut.opacity = 0;
};

// blindX
$.fn.cycle.transitions.blindX = function($cont, $slides, opts) {
	var w = $cont.css('overflow','hidden').width();
	opts.before.push(function(curr, next, opts) {
		$.fn.cycle.commonReset(curr,next,opts);
		opts.animIn.width = next.cycleW;
		opts.animOut.left   = curr.cycleW;
	});
	opts.cssBefore.left = w;
	opts.cssBefore.top = 0;
	opts.animIn.left = 0;
	opts.animOut.left = w;
};
// blindY
$.fn.cycle.transitions.blindY = function($cont, $slides, opts) {
	var h = $cont.css('overflow','hidden').height();
	opts.before.push(function(curr, next, opts) {
		$.fn.cycle.commonReset(curr,next,opts);
		opts.animIn.height = next.cycleH;
		opts.animOut.top   = curr.cycleH;
	});
	opts.cssBefore.top = h;
	opts.cssBefore.left = 0;
	opts.animIn.top = 0;
	opts.animOut.top = h;
};
// blindZ
$.fn.cycle.transitions.blindZ = function($cont, $slides, opts) {
	var h = $cont.css('overflow','hidden').height();
	var w = $cont.width();
	opts.before.push(function(curr, next, opts) {
		$.fn.cycle.commonReset(curr,next,opts);
		opts.animIn.height = next.cycleH;
		opts.animOut.top   = curr.cycleH;
	});
	opts.cssBefore.top = h;
	opts.cssBefore.left = w;
	opts.animIn.top = 0;
	opts.animIn.left = 0;
	opts.animOut.top = h;
	opts.animOut.left = w;
};

// growX - grow horizontally from centered 0 width
$.fn.cycle.transitions.growX = function($cont, $slides, opts) {
	opts.before.push(function(curr, next, opts) {
		$.fn.cycle.commonReset(curr,next,opts,false,true);
		opts.cssBefore.left = this.cycleW/2;
		opts.animIn.left = 0;
		opts.animIn.width = this.cycleW;
		opts.animOut.left = 0;
	});
	opts.cssBefore.top = 0;
	opts.cssBefore.width = 0;
};
// growY - grow vertically from centered 0 height
$.fn.cycle.transitions.growY = function($cont, $slides, opts) {
	opts.before.push(function(curr, next, opts) {
		$.fn.cycle.commonReset(curr,next,opts,true,false);
		opts.cssBefore.top = this.cycleH/2;
		opts.animIn.top = 0;
		opts.animIn.height = this.cycleH;
		opts.animOut.top = 0;
	});
	opts.cssBefore.height = 0;
	opts.cssBefore.left = 0;
};

// curtainX - squeeze in both edges horizontally
$.fn.cycle.transitions.curtainX = function($cont, $slides, opts) {
	opts.before.push(function(curr, next, opts) {
		$.fn.cycle.commonReset(curr,next,opts,false,true,true);
		opts.cssBefore.left = next.cycleW/2;
		opts.animIn.left = 0;
		opts.animIn.width = this.cycleW;
		opts.animOut.left = curr.cycleW/2;
		opts.animOut.width = 0;
	});
	opts.cssBefore.top = 0;
	opts.cssBefore.width = 0;
};
// curtainY - squeeze in both edges vertically
$.fn.cycle.transitions.curtainY = function($cont, $slides, opts) {
	opts.before.push(function(curr, next, opts) {
		$.fn.cycle.commonReset(curr,next,opts,true,false,true);
		opts.cssBefore.top = next.cycleH/2;
		opts.animIn.top = 0;
		opts.animIn.height = next.cycleH;
		opts.animOut.top = curr.cycleH/2;
		opts.animOut.height = 0;
	});
	opts.cssBefore.height = 0;
	opts.cssBefore.left = 0;
};

// cover - curr slide covered by next slide
$.fn.cycle.transitions.cover = function($cont, $slides, opts) {
	var d = opts.direction || 'left';
	var w = $cont.css('overflow','hidden').width();
	var h = $cont.height();
	opts.before.push(function(curr, next, opts) {
		$.fn.cycle.commonReset(curr,next,opts);
		opts.cssAfter.display = '';
		if (d == 'right')
			opts.cssBefore.left = -w;
		else if (d == 'up')
			opts.cssBefore.top = h;
		else if (d == 'down')
			opts.cssBefore.top = -h;
		else
			opts.cssBefore.left = w;
	});
	opts.animIn.left = 0;
	opts.animIn.top = 0;
	opts.cssBefore.top = 0;
	opts.cssBefore.left = 0;
};

// uncover - curr slide moves off next slide
$.fn.cycle.transitions.uncover = function($cont, $slides, opts) {
	var d = opts.direction || 'left';
	var w = $cont.css('overflow','hidden').width();
	var h = $cont.height();
	opts.before.push(function(curr, next, opts) {
		$.fn.cycle.commonReset(curr,next,opts,true,true,true);
		if (d == 'right')
			opts.animOut.left = w;
		else if (d == 'up')
			opts.animOut.top = -h;
		else if (d == 'down')
			opts.animOut.top = h;
		else
			opts.animOut.left = -w;
	});
	opts.animIn.left = 0;
	opts.animIn.top = 0;
	opts.cssBefore.top = 0;
	opts.cssBefore.left = 0;
};

// toss - move top slide and fade away
$.fn.cycle.transitions.toss = function($cont, $slides, opts) {
	var w = $cont.css('overflow','visible').width();
	var h = $cont.height();
	opts.before.push(function(curr, next, opts) {
		$.fn.cycle.commonReset(curr,next,opts,true,true,true);
		// provide default toss settings if animOut not provided
		if (!opts.animOut.left && !opts.animOut.top)
			$.extend(opts.animOut, { left: w*2, top: -h/2, opacity: 0 });
		else
			opts.animOut.opacity = 0;
	});
	opts.cssBefore.left = 0;
	opts.cssBefore.top = 0;
	opts.animIn.left = 0;
};

// wipe - clip animation
$.fn.cycle.transitions.wipe = function($cont, $slides, opts) {
	var w = $cont.css('overflow','hidden').width();
	var h = $cont.height();
	opts.cssBefore = opts.cssBefore || {};
	var clip;
	if (opts.clip) {
		if (/l2r/.test(opts.clip))
			clip = 'rect(0px 0px '+h+'px 0px)';
		else if (/r2l/.test(opts.clip))
			clip = 'rect(0px '+w+'px '+h+'px '+w+'px)';
		else if (/t2b/.test(opts.clip))
			clip = 'rect(0px '+w+'px 0px 0px)';
		else if (/b2t/.test(opts.clip))
			clip = 'rect('+h+'px '+w+'px '+h+'px 0px)';
		else if (/zoom/.test(opts.clip)) {
			var top = parseInt(h/2,10);
			var left = parseInt(w/2,10);
			clip = 'rect('+top+'px '+left+'px '+top+'px '+left+'px)';
		}
	}

	opts.cssBefore.clip = opts.cssBefore.clip || clip || 'rect(0px 0px 0px 0px)';

	var d = opts.cssBefore.clip.match(/(\d+)/g);
	var t = parseInt(d[0],10), r = parseInt(d[1],10), b = parseInt(d[2],10), l = parseInt(d[3],10);

	opts.before.push(function(curr, next, opts) {
		if (curr == next) return;
		var $curr = $(curr), $next = $(next);
		$.fn.cycle.commonReset(curr,next,opts,true,true,false);
		opts.cssAfter.display = 'block';

		var step = 1, count = parseInt((opts.speedIn / 13),10) - 1;
		(function f() {
			var tt = t ? t - parseInt(step * (t/count),10) : 0;
			var ll = l ? l - parseInt(step * (l/count),10) : 0;
			var bb = b < h ? b + parseInt(step * ((h-b)/count || 1),10) : h;
			var rr = r < w ? r + parseInt(step * ((w-r)/count || 1),10) : w;
			$next.css({ clip: 'rect('+tt+'px '+rr+'px '+bb+'px '+ll+'px)' });
			(step++ <= count) ? setTimeout(f, 13) : $curr.css('display', 'none');
		})();
	});
	$.extend(opts.cssBefore, { display: 'block', opacity: 1, top: 0, left: 0 });
	opts.animIn	   = { left: 0 };
	opts.animOut   = { left: 0 };
};

})(jQuery);
