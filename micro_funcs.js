/*
Copyright (C) 2010 Robert Davies (bobbigmac) admin@picNiche.com admin@bobbigmac.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if(typeof(localStorage) == 'undefined')
{
	var localStorage = {};
}

function mspindexOfValue(arr, val)
{
	if(arr && val)
	{
		for(var i=0; i<arr.length; i++)
		{
			if(arr[i] == val)
			{
				return i;
			}
		}
	}
	return -1;
};
function mspclearResultPanels(specificPanel) 
{
	if(!specificPanel)
	{
		jQuery('#MicroResults span').empty();
	}
	else
	{
		jQuery('#MicroResults #MicroResults' + specificPanel).empty();
	}
};

function mspsaveAgencyDefaults(event)
{
	var currChanged = event.target;
	if(currChanged)
	{
		var siteId = currChanged.getAttribute('siteid');
		if(siteId)
		{
			var reRunSearch = false;
			isChecked =  currChanged.checked;
			
			var defaultSites = localStorage["sites_default"];
			defaultSites = ((!defaultSites) ? [] : defaultSites.split(','));
			
			indexOfItem = mspindexOfValue(defaultSites, siteId);
			if(indexOfItem > -1)
			{
				if(!isChecked)
				{
					mspclearResultPanels(siteId);
					defaultSites.splice(indexOfItem, 1);
				}
			}
			else if(isChecked)
			{
				defaultSites.push(siteId);
				reRunSearch = true;
			}
			localStorage["sites_default"] = defaultSites;
			if(reRunSearch)
			{
				localStorage['search_numresults'] = jQuery('#MicroSearchCount').val();
				if(mspsearchActivated)
				{
					var lastSearchRun = localStorage["last_search_run"];
					if(lastSearchRun)
					{
						setTimeout(function() { msprunSearch(lastSearchRun); }, 200);
					}
				}
			}
		}
	}
};

function mspopenMicroLink(url)
{
	window.open(url);
};

function mspsaveLastKnownSearch()
{
	var searchBox = document.getElementById('MicroSearchText');
	if(searchBox)
	{
		var searchText = searchBox.value;
		if(searchText && searchText != '' && searchText != 'Enter Query:')
		{
			localStorage["last_search_entered"] = searchText;
		}
		else
		{
			localStorage["last_search_entered"] = '';
		}
	}
};

function msphidePreviewImage()
{
	jQuery('#MicroImagePreview').removeAttr('desturl');
	jQuery('#MicroImagePreview_Image').removeAttr('src');
	jQuery('#MicroImagePreview').fadeOut('fast', function() {
		jQuery('#MicroImagePreview').hide();
		
		//TODO: Clear currently-viewed image from localStorage
	});
};

function mspsetCorrectSizeFunc() {
	var previewImageHeight = (document.getElementById('MicroImagePreview_Image').height + jQuery('#MicroImagePreview_Info').height() + 80);
	var searchResultsHeight = jQuery('#MicroResults').height();
	if(searchResultsHeight < previewImageHeight)
	{
		jQuery('#MicroImagePreview').height(jQuery('#MicroImagePreview').height() + (previewImageHeight - searchResultsHeight));
		jQuery('#MicroResults').animate({ height: previewImageHeight + 'px' }, 'slow');
	}
	jQuery('#MicroImagePreview').fadeIn('fast');
};

function mspsetStatusMessage(message)
{
	jQuery('#MicroStatusMessage').text(message).fadeIn('fast', function() { jQuery('#MicroStatusMessage').text(message).fadeOut(2000); });
};

function msppreviewImage(thumb)
{
	if(thumb)
	{
		var destUrl = jQuery(thumb).attr('desturl');
		var guid = jQuery(thumb).attr('guid');
		var agency = jQuery(thumb).attr('agency');
		var compUrl = jQuery(thumb).attr('comp');
		var description = jQuery(thumb).attr('description');
		
		var loadedImage = document.getElementById('MicroImagePreview_Image')
		
		if(jQuery('#MicroImagePreview').attr('desturl') == destUrl)
		{
			var previewImageHeight = (loadedImage.height + jQuery('#MicroImagePreview_Info').height() + 80);
			var searchResultsHeight = jQuery('#MicroResults').height();
			if(searchResultsHeight < previewImageHeight)
			{
				jQuery('#MicroImagePreview').height(jQuery('#MicroImagePreview').height() + (previewImageHeight - searchResultsHeight));
				jQuery('#MicroResults').animate({ height: previewImageHeight + 'px' }, 'slow');
			}
			jQuery('#MicroImagePreview').fadeIn('fast');
		}
		else
		{
			jQuery('#MicroImagePreview').attr('desturl', destUrl);
			jQuery('#MicroImagePreview').height(jQuery('#mpsstats').height());
			
			jQuery('#MicroImagePreview_Container').css({ marginTop: ((jQuery('#mpsstats').height() * 0.04) + 'px') });
			
			mspsetStatusMessage('Loading preview image');
			//console.log('setting like url to: ' + 'http://www.stockphotofeeds.com/social/' + agency + '/' + guid + '/');
			var fbLikeUrl = 'http://www.facebook.com/plugins/like.php?href=' + encodeURIComponent('http://www.stockphotofeeds.com/social/' + agency + '/' + guid + '/') + '&amp;layout=button_count&amp;show_faces=false&amp;width=100&amp;action=like&amp;font=arial&amp;colorscheme=light&amp;height=25&amp;ref=spf_micro_preview';
			jQuery('#MicroFacebookLikeFrame').attr('src', fbLikeUrl);
			jQuery('#MicroImagePreview_Image').attr('src', compUrl);
			
			if(!window.attachEvent)// && !mspPreviewImageHasEventListener)
			{
				loadedImage.removeEventListener('load', mspsetCorrectSizeFunc, false);
				loadedImage.addEventListener('load', mspsetCorrectSizeFunc, false);
			}
			else
			{	//Fucking IE
				loadedImage.attachEvent('onload', mspsetCorrectSizeFunc);
			}
			//mspPreviewImageHasEventListener = true;
			jQuery('#MicroImagePreview_InfoDescription').text(description);
		}
	}
};

function mspopenPreviewImageAgencyPage()
{
	var destUrl = jQuery('#MicroImagePreview').attr('desturl');
	if(destUrl)
	{
		mspopenMicroLink(destUrl);
	}
};

function mps_setDragData(dragEvent, newContent)
{
	if(dragEvent && newContent)
	{
		if(dragEvent.dataTransfer)
		{
			try
			{
				//Standards
				dragEvent.dataTransfer.setData("text/plain", newContent);
				dragEvent.dataTransfer.setData("text/html", newContent);
			} catch(exc)
			{
				//IE:
				dragEvent.dataTransfer.setData('Text', newContent);
				dragEvent.dataTransfer.setData('URL', newContent);
			}
		}
	}
}

function mspshowSearchResults(results, preventClear)
{
	//for each key (which each come back pretty) append the results to each relevant search results box (until the number added match the option
	var searchNumResults = localStorage['search_numresults'];
	searchNumResults = parseInt(((!searchNumResults) ? '5' : searchNumResults));
	
	if(!preventClear)
	{
		mspclearResultPanels();
	}
	
	if(results)
	{
		//setStatusMessage('Displaying Search Results...');
		for(var sourceResult in results)
		{
			jQuery('#MicroResults #MicroResults' + sourceResult).detach().prependTo('#MicroResults');
			jQuery('#MicroResults #MicroResults' + sourceResult).empty();
			//jQuery('#MicroResults #MicroResults' + sourceResult).append('got ' + sourceResult + ' with ' + results[sourceResult].length + ' records');
			var numAdded = 0;
			for(var imageResult in results[sourceResult])
			{
				var currImage = results[sourceResult][imageResult];
				if(currImage)
				{
					var imageString = '<div class="MicroThumbContainer"><a id="MicroPreviewLink' + currImage.guid + '" guid="' + currImage.guid + '" agency="' + sourceResult + '" class="MicroPreviewLink" desturl="' + currImage.link + '" title="' + currImage.title + ' on ' + sourceResult + ' - Click for Preview" comp="' + currImage.comp + '" description="' + currImage.description + '" onclick="msppreviewImage(this);">' + 
					'<img class="MicroPreviewImage" onload="if(this.attachEvent) { /*IE7*/ this.attachEvent(\'ondragstart\', function() { mps_setDragData(event, \'Using these images without purchasing a license is both illegal and traceable.\'); }); }" ondragstart="mps_setDragData(event, \'Using these images without purchasing a license is both illegal and traceable.\')" src="' + currImage.preview + '" />' +
					'</a></div>';
					jQuery('#MicroResults #MicroResults' + sourceResult).append(imageString);
					numAdded++;
				}
				if(numAdded == searchNumResults)
				{
					break;
				}
			}
			jQuery('#MicroResults #MicroResults' + sourceResult).slideDown('fast');
		}
	}
}

var mspsearchBarBackgroundColor = null;
var mspsearchActivated = false;
function msprunSearch(directSearch)
{
	var searchBox = document.getElementById('MicroSearchText');
	var searchString = null;
	
	if(searchBox  && !directSearch)
	{
		if(searchBox.style.backgroundColor && searchBox.style.backgroundColor != '#ffcccc' && searchBox.style.backgroundColor != 'rgb(255, 204, 204)')
		{
			mspsearchBarBackgroundColor = searchBox.style.backgroundColor;
		}
		searchBox.style.backgroundColor = ((mspsearchBarBackgroundColor) ? mspsearchBarBackgroundColor : null);
		searchString = searchBox.value;
		if(!searchString || searchString == '' || searchString == 'Enter Query:')
		{
			searchString = null;
			searchBox.style.backgroundColor = '#ffcccc';
			searchBox.focus();
			setTimeout(function() { document.getElementById('MicroSearchText').style.backgroundColor = ((mspsearchBarBackgroundColor) ? mspsearchBarBackgroundColor : null); }, 2000);
		}
	}
	else if(directSearch && directSearch != '')
	{
		searchString = directSearch;
		if(searchBox)
		{
			searchBox.value = directSearch;
			searchBox.select();
		}
	}
	
	if(searchString && searchString != '')
	{
		mspsearchActivated = true;
		
		var searchType = localStorage['search_type'];
		searchType = ((!searchType) ? 'engine' : searchType);
		
		localStorage['last_search_run'] = searchString;
		
		searchString = encodeURIComponent(searchString);
		
		var defaultSites = localStorage["sites_default"];
		if(!defaultSites || (defaultSites && defaultSites.length < 5))
		{
			defaultSites = [];
			//For IE and Older Browsers with no localStorage
			jQuery('#MicroAgencies input:checked').each(function(currPos, currSiteEl) { defaultSites.push(jQuery(currSiteEl).attr('siteid')); });
			localStorage["sites_default"] = defaultSites;
		}
		else
		{
			defaultSites = defaultSites.split(',');
		}
		
		//Check localStorage for a matching search result-set for each agency before running against server
		var previousAgencyResults = {};
		var foundNumInCache = 0;
		var preventClear = false;
		var storedResults = localStorage["sites_results"];
		if(storedResults && storedResults.length > 0)
		{
			var storedResults = JSON.parse(storedResults);
			if(storedResults && storedResults.search)
			{
				if(searchString == storedResults.search)
				{
					var needToGetAgencies = [];
					for(var i=0; i<defaultSites.length; i++)
					{
						if(storedResults.results[searchType] && storedResults.results[searchType][defaultSites[i]])
						{
							previousAgencyResults[defaultSites[i]] = storedResults.results[searchType][defaultSites[i]];
							foundNumInCache++;
						}
						else
						{
							needToGetAgencies.push(defaultSites[i]);
						}
					}
					defaultSites = needToGetAgencies;
				}
			}
		}
		if(foundNumInCache > 0)
		{
			if(!defaultSites || (defaultSites && defaultSites.length == 0))
			{
				mspsetStatusMessage('Loading from cache...');
			}
			mspshowSearchResults(previousAgencyResults);
			preventClear = true;
		}
		
		//Run search if there are any left to search after local cache check
		if(defaultSites && defaultSites.length > 0)
		{
			mspsetStatusMessage('Searching...');
			jQuery('#MicroStatusImage').fadeIn('fast');
			jQuery.ajax({
				url: 'http://www.stockphotofeeds.com/imagefeed.php?format=json&search=' + searchString + '&source=' + defaultSites.join(',').toLowerCase() + ((searchType == 'engine') ? '&type=engine' : '') + '&callback=?', 
				dataType: 'json',
				data: null,
				success: function(resultsObj, status) {
					if(resultsObj)
					{
						try
						{
							if(typeof(resultsObj) == 'array' || typeof(resultsObj) == 'object')
							{
								var storedResults = localStorage["sites_results"];
								var resultsToStore = {search:searchString};
								if(storedResults && storedResults.length > 0)
								{
									var storedResults = JSON.parse(storedResults);
									if(storedResults && storedResults.search && searchString == storedResults.search)
									{
										if(!storedResults.results)
										{
											storedResults.results = {};
										}
										for(var receivedAgency in resultsObj)
										{
											if(resultsObj[receivedAgency])
											{
												if(!storedResults.results[searchType])
												{
													storedResults.results[searchType] = {};
												}
												storedResults.results[searchType][receivedAgency] = resultsObj[receivedAgency];
											}
										}
										resultsToStore.results = storedResults.results;
									}
								}
								if(!resultsToStore.results)
								{
									resultsToStore.results = {};
									resultsToStore.results[searchType] = resultsObj;
								}
								localStorage["sites_results"] = JSON.stringify(resultsToStore);
								mspshowSearchResults(resultsObj, preventClear);
							}
							jQuery('#MicroStatusImage').fadeOut('fast');
						} catch(exc)
						{
							console.log('Error when parsing search results from StockPhotoFeeds.com (' + exc + ')');
						}
					}
				}
			});
		}
	}
};

function mspsaveNumResults(event)
{
	localStorage['search_numresults'] = jQuery('#MicroSearchCount').val();
	if(mspsearchActivated)
	{
		var lastSearchRun = localStorage["last_search_run"];
		if(lastSearchRun)
		{
			setTimeout(function() { msprunSearch(lastSearchRun); }, 200);
		}
	}
}

function mspsaveSearchType(event)
{
	localStorage['search_type'] = jQuery('#MicroSearchType').val();
	if(mspsearchActivated)
	{
		var lastSearchRun = localStorage["last_search_run"];
		if(lastSearchRun)
		{
			setTimeout(function() { msprunSearch(lastSearchRun); }, 200);
		}
	}
}

function mspsetupSavedState()
{
	var lastBackgroundColor = localStorage["last_background_color"];
	if(lastBackgroundColor)
	{
		jQuery("body").css("background-color",lastBackgroundColor);
	}
	var lastKnownSearch = localStorage["last_search_entered"];
	if(lastKnownSearch)
	{
		jQuery('#MicroSearchText').val(lastKnownSearch);
	}
	
	var defaultSites = localStorage["sites_default"];
	if(!defaultSites || (defaultSites && defaultSites.length < 5))
	{
		defaultSites = ['Fotolia', 'Dreamstime'];
		localStorage["sites_default"] = defaultSites;
	}
	else
	{
		defaultSites = defaultSites.split(',');
	}
	for(var i=0; i<defaultSites.length; i++)
	{
		jQuery('#MicroSearch' + defaultSites[i]).attr('checked', 'true');
	}
	
	//Restore num of results
	var searchNumResults = localStorage['search_numresults'];
	searchNumResults = parseInt(((!searchNumResults) ? localStorage["search_numresults"] = '6' : searchNumResults));
	jQuery('#MicroSearchCount').val(searchNumResults);
	
	//Restore search type
	var searchType = localStorage['search_type'];
	searchType = ((!searchType) ? localStorage['search_type'] = 'engine' : searchType);
	jQuery('#MicroSearchType').val(searchType);
	
	var lastSearchRun = localStorage["last_search_run"];
	if(lastSearchRun)
	{
		setTimeout(function() { msprunSearch(lastSearchRun); }, 200);
	}
};

if(!window.attachEvent)
{
	window.addEventListener('load', function() { mspsetupSavedState(); }, false);
}
else
{
	window.attachEvent('onload', function() { mspsetupSavedState(); });
}