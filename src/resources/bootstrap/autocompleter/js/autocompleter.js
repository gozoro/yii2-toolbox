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
			useIndex: false,
			emptyValue: '',
			regexpBegin: '',
			regexpEnd: '',
			regexpFlags: 'i',
        }, options);


		return this.each(function()
		{
			var selectLock = false;

			var searchInput = $(this);
			var searchInputValue = searchInput.val();
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

			// Reposition the results on window resize and font load in case the search box has moved
			window.addEventListener('resize', reposition);
			document.fonts.ready.then(reposition);

			reposition();


			resultPanel.show = function()
			{
				if(!resultPanelVisible)
					$.fn.show.apply(this, arguments);

				resultPanelVisible = true;
				$('.selected').removeClass('selected');
				resultPanel.children().first().addClass('selected');
				resultPanel.scrollTop(0);
			}

			resultPanel.hide = function()
			{
				if(resultPanelVisible)
				{
					$.fn.hide.apply(this, arguments);
					resultPanelVisible = false;
				}
			}

			searchInput.blur(function()
			{
				resultPanel.hide();
			});

			searchInput.click(function()
			{
				var value = searchInput.val();
				search(value);
			});


			resultPanel.mouseout(function()
			{
				if(!selectLock)
				{
					$('.selected').removeClass('selected');
					console.log('mouseout: ' + resultPanel.height());
				}
			});

			resultPanel.addResultItem = function(itemValue, itemIndex)
			{
						var resultRow = $('<div>').addClass('autocompleter-item')
							.html(itemValue)
							.mousedown(function(event)
							{
								event.preventDefault(); // This prevents the element from being hidden by .blur before it's clicked
							})
							.click(function()
							{
								var item = $(this);
								var newValue = item.html();
								searchInput.val( newValue );
								searchInputValue = newValue;

								resultPanel.hide();
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

						var selectedItem = resultPanel.find('.selected').first();

						if(selectedItem.length)
						{
							$('.selected').removeClass('selected');
							var newValue = selectedItem.html();
							searchInput.val( newValue );
							searchInputValue = newValue;

							console.log('Enter');
						}

						resultPanel.hide();
						return;
					case 9: resultPanel.hide(); return; // Tab
					case 27: resultPanel.hide(); return; // Esc

				}
			});






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
				console.log('function search');

				if(!value)
				{
					resultPanel.hide();
					resultPanel.empty();
					searchInputValue = value;
					return;
				}


				if(value === searchInputValue) return;
				searchInputValue = value;




				resultPanel.empty();


				if($.isArray(variants))
				{
						var regexp = options['regexpBegin'] + escapeRegExp(value) + options['regexpEnd'];
						var flags  = options['regexpFlags'];

						console.log('search regexp = '+regexp);





					variants.filter(function(itemValue)
					{
						if(itemValue.match(RegExp(regexp, flags)))
						{
							/////////////////////////
							resultPanel.addResultItem(itemValue);
							//////////////////////////
						}
					});



					if(resultPanel.children().length)
					{
						resultPanel.show();
					}
					else
					{
						resultPanel.hide();
					}


				}
				else
				{
					console.log('try get ajax data');
					$.get(variants, {}, function(req)
					{
						for(var k in req)
							resultPanel.addResultItem(req[k], k);


						console.log('ajax data - OK');


						if(resultPanel.children().length)
						{
							resultPanel.show();
						}
						else
						{
							resultPanel.hide();
						}
					});
				}
			} // end search()


		}); // end each

	};

}(jQuery));


