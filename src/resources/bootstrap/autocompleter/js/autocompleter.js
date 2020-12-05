;(function($)
{

	if(typeof(jQuery) == 'undefined')
	{
		console.warn('Autocompleter plugin requires jQuery.');
		return;
	}



	$.fn.autocompleter = function(variants, options)
	{
		options = $.extend({
            maxResults: 0,
			minChars: 1,
			timeout: 500,


			matchRegexp: null,
			matchValue:  function(item, index){return item;},
			itemDisplay: function(item, index){return item;},
			itemValue:   null,
			emptyValue: ''
        }, options);

		var _this = this;


		return this.each(function()
		{
			var selectLock = false;
			var useHiddenInput = (typeof options['itemValue'] == 'function');

			var searchInput = $(this);
			var searchInputValue = searchInput.val().trim();
			var searchInputName = searchInput.attr('name');
			var hiddenInput = $('<input type="hidden" value="'+options['emptyValue']+'">');
			var resultPanel = $('<div>').addClass('autocompleter-result');
			var resultPanelVisible = false;
			searchInput.after(resultPanel);



			function reposition()
			{
				var pos = searchInput.position();

				resultPanel.css({
					'left': pos.left,
					'top': pos.top + searchInput.outerHeight(),
					'width': searchInput.outerWidth()
				});
			}

			window.addEventListener('resize', reposition);
			document.fonts.ready.then(reposition);

			reposition();


			if(typeof options['itemValue'] == 'function')
			{
				var name = searchInput.attr('name');
				searchInput.removeAttr('name');
				hiddenInput.attr('name', name);
				searchInput.after(hiddenInput);
			}





			resultPanel.show = function()
			{
				console.log( 'resultPanelVisible=' + resultPanelVisible +'; resultPanel.show(); ');

				if(!resultPanelVisible)
					$.fn.show.apply(this, arguments);

				resultPanelVisible = true;
				$('.selected').removeClass('selected');
				resultPanel.children().first().addClass('selected');
				resultPanel.scrollTop(0);
				_this.trigger('resultShow', {});

			}

			resultPanel.hide = function()
			{
				console.log('resultPanelVisible=' + resultPanelVisible + '; resultPanel.hide(); ');

				if(resultPanelVisible)
					$.fn.hide.apply(this, arguments);

				resultPanelVisible = false;
				_this.trigger('resultHide', {});


			}

			searchInput.blur(function()
			{
				resultPanel.hide();
			});

			searchInput.click(function()
			{
				var value = searchInput.val();

				console.log('searchInput.CLick');

				search(value);

				if(resultPanel.children().length)
				    resultPanel.show()

			});

			searchInput.on('paste', function(event)
			{
				console.log('paste');
								var value = event.originalEvent.clipboardData.getData('text');


				search(value);

				if(resultPanel.children().length)
				    resultPanel.show()
			});



			resultPanel.mouseout(function()
			{
				if(!selectLock)
				{
					$('.selected').removeClass('selected');
					console.log('mouseout: ' + resultPanel.height());
				}
			});

			resultPanel.addResultItem = function(item, itemIndex)
			{
				var matchValue = options['matchValue'](item, itemIndex);
				if(typeof options['itemValue'] == 'function')
				{
					var itemValue = options['itemValue'](item, itemIndex);
				}
				else
				{
					var itemValue = matchValue;
				}


				var resultRow = $('<div>').addClass('autocompleter-item')
							.attr('data-match-value', matchValue)
							.attr('data-value', itemValue)
							.html( options['itemDisplay'](item, itemIndex) )
							.mousedown(function(event)
							{
								event.preventDefault(); // This prevents the element from being hidden by .blur before it's clicked
							})
							.click(function()
							{
								resultPanel.selectVariant($(this)).hide();
								console.log('item click');
							})
							.mouseover(function()
							{
								if(!selectLock)
								{
									selectLock = false;
									$('.selected').removeClass('selected');
									$(this).addClass('selected');
								}
							})
							.mousemove(function()
							{
								if(selectLock)
								{
									selectLock = false;
									$('.selected').removeClass('selected');
									$(this).addClass('selected');
								}
							})
							;

							resultPanel.append(resultRow);
			}



			searchInput.keyup(function(event)
			{
				inputDelay(function()
				{
					var value = searchInput.val();

					console.log('keyup before search');

					search(value);
				});
			});


			searchInput.keydown(function(event)
			{
				switch(event.which)
				{
					case 38: keydown_keyUpArrow(); return;
					case 40: keydown_keyDownArrow(); return;

					case 13: // Enter
						event.preventDefault();

						var selectedVariant = resultPanel.find('.selected').first();

						if(selectedVariant.length)
						{
							$('.selected').removeClass('selected');
							resultPanel.selectVariant(selectedVariant);
							console.log('Enter');
						}

						resultPanel.hide();
						return;
					case 9: resultPanel.hide(); return; // Tab
					case 27: resultPanel.hide(); return; // Esc

				}
			});

			resultPanel.selectVariant = function(variant)
			{
				console.log('select variant:');


				var matchValue = variant.data('match-value');
				var itemValue = variant.data('value');





				if(typeof options['itemValue'] == 'function')
				{
					hiddenInput.val(itemValue);

				}

				searchInput.val( matchValue );
				searchInputValue = matchValue;
				resultPanel.empty();



				return this;
			}





			function keydown_keyUpArrow()
			{
				if(resultPanelVisible)
				{
					selectLock = true;
					var selectedItem = resultPanel.find('.selected').first();

					if(selectedItem.length)
					{
						$('.selected').removeClass('selected');

						var prevItem = selectedItem.prev();

						if(prevItem.length)
						{
							prevItem.addClass('selected');

							var panelpos = resultPanel.position();
							var itempos  = prevItem.position();

							var panelTop    = panelpos.top;
							var panelHeight = resultPanel.outerHeight() ;
							var panelBottom = panelTop + panelHeight;
							var itemTop     = itempos.top ;
							var itemHeight  = prevItem.innerHeight();
							var itemBottom  = itemTop + itemHeight;


							var curScroll   = resultPanel.scrollTop();
							var nextScroll  = 0;

							var delta = panelTop + itemTop;

							if(itemTop < panelTop - itemHeight)
								nextScroll = curScroll   - itemHeight  + delta;

							if(itemTop < panelTop - itemHeight)
							{
								resultPanel.scrollTop( nextScroll  );
							}
						}
						else
						{
							resultPanel.children().last().addClass('selected');
							resultPanel.scrollTop( resultPanel.get(0).scrollHeight );
						}
					}
					else
					{
						resultPanel.children().last().addClass('selected');
						resultPanel.scrollTop( resultPanel.get(0).scrollHeight );
					}

					console.log('key upArrow');
				}
			}





			function keydown_keyDownArrow()
			{
				if(resultPanelVisible)
				{
					selectLock = true;
					var selectedItem = resultPanel.find('.selected').first();

					if(selectedItem.length)
					{
						$('.selected').removeClass('selected');

						var nextItem = selectedItem.next();

						if(nextItem.length)
						{
							nextItem.addClass('selected');

							var panelpos = resultPanel.position();
							var itempos  = nextItem.position();

							var panelTop    = panelpos.top;
							var panelHeight = resultPanel.outerHeight() ;
							var panelBottom = panelTop + panelHeight;
							var itemTop     = itempos.top ;
							var itemHeight  = nextItem.innerHeight();
							var itemBottom  = itemTop + itemHeight;

							var curScroll = resultPanel.scrollTop();
							var nextScroll = 0;

							var delta = panelBottom - itemBottom;

							if(itemBottom > panelHeight)
								nextScroll = curScroll + itemHeight;


							if(itemBottom > panelHeight)
							{
								resultPanel.scrollTop( nextScroll - delta );
							}
						}
						else
						{
							resultPanel.children().first().addClass('selected');
							resultPanel.scrollTop( 0 );
						}
					}
					else
					{
						resultPanel.children().first().addClass('selected');
						resultPanel.scrollTop( 0 );
					}

					console.log('key downArrow');
				}
				else
				{
					if(resultPanel.children().length)
					{
						resultPanel.show();
					}
				}
			}







			function escapeRegExp(string)
			{
				return string.replace(/[.*+\-?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
			}


			var locks = [];
			function inputDelay(callback)
			{
				locks.push(1);
				setTimeout(function()
				{
					locks.pop();
					if(!locks.length)
					{
						callback();
					}
				}, options['timeout']);
			}





			function search(value)
			{
				console.log('function search !!!');
				value = value.trim();

				if(value === searchInputValue)
				{
					console.log('value==value');
					return;
				}

				hiddenInput.val(options['emptyValue']);
				searchInputValue = value;
				resultPanel.empty();

				if(!value)
				{

					resultPanel.hide();
					return;
				}

				if(value.length < options['minChars']) return;

				_this.trigger('beforeSearch', {val:value});

				if(!$.isArray(variants))
				{

					console.log('AJAX');
					$.get(variants, {searchInputName:value}, function(response){ compilation(value, response); });
				}
				else
				{
					compilation(value, variants);
				}
			} // end search()



			function compilation(value, variants)
			{


					if(typeof options['matchRegexp'] == 'function')
						var regexp = options['matchRegexp'](value, escapeRegExp);
					else
						var regexp = RegExp(escapeRegExp(value), 'i');


					var fullregexp = RegExp('^'+escapeRegExp(value)+'$', regexp.flags);



					console.log('search regexp: '+regexp + "; flags: " +regexp.flags );
					console.log('maxResults = ' + options['maxResults']);


					var i = 0;
					variants.filter(function(item, itemIndex)
					{
						var matchValue = options['matchValue'](item, itemIndex);

							if(typeof options['itemValue'] == 'function')
							{
								var itemValue = options['itemValue'](item, itemIndex);
							}
							else
							{
								var itemValue = matchValue;
							}


						if(matchValue.match(regexp) && (options['maxResults'] <= 0 || i < options['maxResults']))
						{
							resultPanel.addResultItem(item, itemIndex);
							i++;
						}

						if(matchValue.match(fullregexp))
						{
							hiddenInput.val(itemValue);
						}



					});

					_this.trigger('afterSearch', {});

					if(resultPanel.children().length)
					{
						resultPanel.show();
					}
					else
					{
						resultPanel.hide();
					}
			}



		}); // end each

	};

}(jQuery));


