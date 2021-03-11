;(function($)
{
	'use strict';

	/**
	 * Returns random string
	 * @param {Number} length
	 * @return {String}
	 */
	$.generateRandomString = function(length)
	{
		length = length || 32;

		var guid = new Date().getTime().toString(length), i;
		for (i = 0; i < 10; i++)
		{
			guid += Math.floor(Math.random() * 65535).toString(length);
		}

		return guid.substr(-1*length, length);
	};



	/**
	 * Formats the file size to a readable format
	 * @param {Number} size
	 * @return {String}
	 */
	$.filesizeFormat = function(size)
	{
		var filesize = size || 0;

		if(filesize === 0 || filesize < 1000)
		{
			return filesize + " B";
		}
		else if(filesize >=1000 && filesize <1000000 )
		{
			return (filesize/1000).toFixed(1) + " KB";
		}
		else if(filesize >=1000000 && filesize <1000000000 )
		{
			return (filesize/1000000).toFixed(1) + " MB";
		}
		else if(filesize >=1000000000 && filesize <1000000000000 )
		{
			return (filesize/1000000000).toFixed(1) + " GB";
		}
		else
		{
			return (filelen/1000000000000).toFixed(1) + " TB";
		}
	};



})(jQuery);
