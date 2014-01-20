/**
 *
 * Code adapted in part from https://github.com/abrimo/TinyMCE-Autocomplete-Plugin (under MIT licence)
 *
 */
/*global tinymce, autocomplete, ajaxurl */
(function($) {
	"use strict";

	var DOWN_ARROW_KEY = 40;
	var UP_ARROW_KEY = 38;
	var ESC_KEY = 27;
	var ENTER_KEY = 13;
	var AJAX_ON = false;

	tinymce.create('tinymce.plugins.autocomplete', {

		init: function(ed, url) {

			// All the plugin parameters
			var params = {
				list: $('<ul class="shortcode-suggest"></ul>').appendTo('.wp-editor-container'),
				visible: false,
				cancelEnter: false,
				delimiter: ['160', '32'],
				options: {},
				trigger: '[',
				minLength: '0',
				ajax_list: [],
				nodeClass: 'autocomplete'
			};

			/**
			 * Handle keyup event
			 *
			 * @param {object} ed - The editor instance
			 * @param {object} e - The event instance
			 *
			 * @return {void}
			 */
			function keyUpEvent(ed, e) {
				if (!params.visible &&
					$.inArray(e.keyCode, [ENTER_KEY, ESC_KEY]) === -1 ||
					$.inArray(e.keyCode, [DOWN_ARROW_KEY, UP_ARROW_KEY, ENTER_KEY, ESC_KEY]) === -1
				) {
					var currentWord = getCurrentWord(ed);
					var matches = matchingOptions(currentWord);
					if (currentWord.length > 0) {
						populateList(currentWord);
					}
					if (currentWord.length === 0 || matches.length === 0) {
						hideOptionList();
					}
				}
			}

			/**
			 * Handle keydown event
			 *
			 * @param {object} ed - The editor instance
			 * @param {object} e - The event instance
			 *
			 * @return {mixed|boolean}
			 */
			function keyDownEvent(ed, e) {
				if (params.visible) {
					switch (e.keyCode) {
						case DOWN_ARROW_KEY:
							highlightNextOption();
							return tinymce.dom.Event.cancel(e);
						case UP_ARROW_KEY:
							highlightPreviousOption();
							return tinymce.dom.Event.cancel(e);
						case ENTER_KEY:
							selectOption(ed, getCurrentWord(ed));
							params.cancelEnter = true;
							// the enter event needs to be cancelled on keypress so
							// it doesn't register a carriage return
							return false;
						case ESC_KEY:
							hideOptionList();
							return tinymce.dom.Event.cancel(e);
					}

				}
			}

			/**
			 * Handle keypress event
			 *
			 * @param {object} ed - The editor instance
			 * @param {object} e - The event instance
			 *
			 * @return {void|object} - tinycemce cancel event
			 */
			function keyPressEvent(ed, e) {
				if (e.keyCode === ENTER_KEY && params.cancelEnter) {
					params.cancelEnter = false;
					params.ajax_list = [];
					return tinymce.dom.Event.cancel(e);
				}
			}

			/**
			 * Return the current typed word for ajax searching
			 *
			 * @param {Object} ed - The editor instance
			 *
			 * @return {String}
			 */
			function getCurrentWord(ed) {
				var nodeText = ed.selection.getSel().focusNode === null ? "" : ed.selection.getSel().focusNode.nodeValue;
				var positionInNode = ed.selection.getSel().focusOffset;

				if (nodeText === null || nodeText.length === 0 || ed.selection.getNode().className === params.nodeClass) {
					return "";
				}

				var lastDelimiter = 0;
				for (var i = 0; i < positionInNode; i++) {
					if (params.delimiter.indexOf(nodeText.charCodeAt(i).toString()) !== -1) {
						lastDelimiter = i + 1;
					}
				}

				var word = nodeText.substr(lastDelimiter, positionInNode - lastDelimiter);
				var retWord = "";
				if (word.length >= params.minLength > 0 && word.charAt(0).toString() === params.trigger) {
					retWord = word.substring(1);
				}

				//Replace underscore by space so user can type full name
				retWord = retWord.replace('_', ' ');

				return retWord;
			}

			/**
			 * Create an array of matching options
			 *
			 * @param {String} currentWord - The current type word already processed for searching the database
			 *
			 * @return {Array}
			 */
			function matchingOptions(currentWord) {
				var options = params.options;
				var matches = [];
				for (var key in options) {
					if (currentWord.length > 0 && wordMatches(currentWord, key)) {
						matches.push(key);
					}
				}
				return matches;
			}

			/**
			 * Check is the word passed if it matched the provided key
			 *
			 * @param {string} word
			 * @param {string} key
			 *
			 * @return {Array|{index: number, input: string}}
			 */
			function wordMatches(word, key) {
				var test = new RegExp(word, 'gi');
				return key.match(test);
			}

			/**
			 * Populate a list of results based on ajax request to the database.
			 * There is a mechanism in place to prevent too much ajax at the same time.
			 * By default if the request is not done, we create a array of requests for later use.
			 *
			 * @param {string} currentWord
			 *
			 * @return {void}
			 */
			function populateList(currentWord) {
				params.ajax_list.push(currentWord);

				// if query term search before in this session then do not make extra ajax call get result from local storage
				if (typeof(Storage) !== "undefined") {
					if (sessionStorage[currentWord]) {
						var stored_data = JSON.parse( sessionStorage[currentWord] );

						// Merge both object together
						$.extend(params.options, stored_data);

						//Get the matches
						var matches = matchingOptions(currentWord);

						if (matches.length > 0) {
							displayOptionList(matches, currentWord, ed);
							highlightNextOption();
						}

						// stop ajax call
						AJAX_ON = true;
					}
				}

				// Sometimes ajax is not fast enough
				if (!AJAX_ON) {
					AJAX_ON = true;

					params.ajaxRequest = $.ajax({
						type: 'GET',
						url: ajaxurl,
						data: {
							action: autocomplete.action,
							autocomplete_nonce: autocomplete.nonce,
							shortcode_suggest: currentWord
						},
						dataType: 'json',
						success: function(response) {
							if (true === response.success && params.ajax_list !== 0) {
								var data = response.data;

								//store result in local storage ( per session ) which saving ajax call for same query term
								if (typeof(Storage) !== "undefined")
									sessionStorage[currentWord] = JSON.stringify(data);


								// Merge both object together
								$.extend(params.options, data);

								// Get the matches
								var matches = matchingOptions(currentWord);
								if (matches.length > 0) {
									displayOptionList(matches, currentWord, ed);
									highlightNextOption();
								}
							}

							AJAX_ON = false;

							if (params.ajax_list.length > 0) {
								populateList(params.ajax_list.pop());
								params.ajax_list = [];
							}
						},
						error: function(jqXHR, textStatus) {
							// error
						}
					});
				}
			}

			/**
			 * Add all the options to the option list and display it right beneath
			 * the caret where the user is entering text. There didn't appear to be
			 * an easy way to retrieve the exact pixel position of the caret inside
			 * tinyMCE so the difficult method had to suffice.
			 *
			 * @param {Array} matches
			 * @param {String} matchedText
			 * @param {Object} ed
			 *
			 * @return {Void}
			 */
			function displayOptionList(matches, matchedText, ed) {
				var matchesList = "";
				var highlightRegex = new RegExp("(" + matchedText + ")");

				for (var key in matches) {
					if (matches.hasOwnProperty(key)) {
						var docs = params.options[matches[key].toString()].description;
						matchesList +=
							'<li data-value="' + matches[key] +
							'">[' + matches[key].replace(highlightRegex, "<mark>$1</mark>") + '] <div class="docs">' + docs + '</div></li>';
					}
				}
				params.list.html(matchesList);

				// Work out the position of the caret
				var tinymcePosition = $(ed.getContainer()).position();
				var toolbarPosition = $(ed.getContainer()).find(".mceToolbar").first();
				var nodePosition = $(ed.selection.getNode()).position();
				var textareaTop = 0;
				var textareaLeft = 0;
				if (ed.selection.getRng().getClientRects().length > 0) {
					textareaTop = ed.selection.getRng().getClientRects()[0].top + ed.selection.getRng().getClientRects()[0].height;
					textareaLeft = ed.selection.getRng().getClientRects()[0].left;
				} else {
					textareaTop = parseInt($(ed.selection.getNode()).css("font-size"), 10) * 1.3 + nodePosition.top;
					textareaLeft = nodePosition.left;
				}

				params.list.css("margin-top", tinymcePosition.top + toolbarPosition.innerHeight() + textareaTop);
				params.list.css("margin-left", tinymcePosition.left + textareaLeft);
				params.list.css("display", "block");
				params.visible = true;

				optionListEventHandlers(ed);
			}

			/**
			 * Hide the autocomplete list
			 *
			 * @return {void}
			 */
			function hideOptionList() {
				params.list.css("display", "none");
				params.visible = false;
			}

			/**
			 * Highlight next posssible option in the autocomplete list
			 *
			 * @return {void}
			 */
			function highlightNextOption() {
				var current = params.list.find("[data-selected=true]");
				if (current.size() === 0 || current.next().size() === 0) {
					params.list.find("li:first-child").attr("data-selected", "true");
				} else {
					current.next().attr("data-selected", "true");
				}
				current.attr("data-selected", "false");
			}

			/**
			 * Highlight previous posssible option in the autocomplete list
			 *
			 * @return {void}
			 */
			function highlightPreviousOption() {
				var current = params.list.find("[data-selected=true]");
				if (current.size() === 0 || current.prev().size() === 0) {
					params.list.find("li:last-child").attr("data-selected", "true");
				} else {
					current.prev().attr("data-selected", "true");
				}
				current.attr("data-selected", "false");
			}

			/**
			 * Handle the hover and click event for the autocomplete list
			 *
			 * @param {Object} ed - The editor instance
			 *
			 * @return {void}
			 */
			function optionListEventHandlers(ed) {
				params.list.find("li").hover(function() {
					params.list.find("[data-selected=true]").attr("data-selected", "false");
					$(this).attr("data-selected", "true");
				});
				params.list.find("li").click(function() {
					selectOption(ed, getCurrentWord(ed));
				});
			}

			/**
			 * Select/insert the currently selected option. Hide the autocomplete list in the end.
			 *
			 * @param {object} ed - The editor instance
			 * @param {string} matchedText
			 *
			 * @returns {void}
			 */
			function selectOption(ed, matchedText) {
				var $selection = $(params.list).find("[data-selected=true]");
				if ($selection === null) {
					$selection = $(params.list).find("li:first-child");
				}

				var currentValue = $selection.attr("data-value");
				var currentID = $selection.attr("data-id");
				var currentURL = $selection.attr("data-url");

				// Modify the range to replace overwrite the option text that has already been entered
				var range = ed.selection.getRng();
				range.setStart(range.startContainer, range.startOffset - matchedText.length - 1);
				ed.selection.setRng(range);
				ed.selection.setContent('[' + currentValue + ']');

				/*				params.nodeClass +
								'" href="' +
								currentURL +
								'" data-autocomplete="' +
								currentID +
								'">' +
								currentValue +
								'</a>'
				);*/

				hideOptionList();
			}

			// Add event listener here
			ed.onKeyUp.addToTop(keyUpEvent);
			ed.onKeyDown.addToTop(keyDownEvent);
			ed.onKeyPress.addToTop(keyPressEvent);
			ed.onClick.add(hideOptionList);
		}

	});

	tinymce.PluginManager.add('autocomplete', tinymce.plugins.autocomplete);
})(jQuery);
