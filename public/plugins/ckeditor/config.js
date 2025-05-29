/**
 * @license Copyright (c) 2003-2022, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// https://ckeditor.com/docs/ckeditor4/latest/api/CKEDITOR_config.html

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'forms' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others' },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'styles' },
		{ name: 'colors' },
		{ name: 'about' }
	];

	config.extraPlugins = 'justify'

	// config.extraPlugins = "justify,font,colorbutton"
	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
	config.removeButtons = 'Underline,Subscript,Superscript';

	// Set the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';

	// Simplify the dialog windows.
	config.removeDialogTabs = 'image:advanced;link:advanced';

	config.height = 500
	config.imagePreviewWidth = '500px'
	config.imagePreviewHeight = '500px'
	// config.extraPlugins = 'autosave'

	// config.autosave = { 
	// 		// Auto save Key - The Default autosavekey can be overridden from the config ...
	// 		// Savekey : 'autosave_' + window.location + "_" + $('#' + editor.name).attr('name'),

	// 		// Ignore Content older then X
	// 		//The Default Minutes (Default is 1440 which is one day) after the auto saved content is ignored can be overidden from the config ...
	// 		NotOlderThen : 1440,

	// 		// Save Content on Destroy - Setting to Save content on editor destroy (Default is false) ...
	// 		saveOnDestroy : false,

	// 		// Setting to set the Save button to inform the plugin when the content is saved by the user and doesn't need to be stored temporary ...
	// 		saveDetectionSelectors : "a[href^='javascript:__doPostBack'][id*='Save'],a[id*='Cancel']",

	// 		// Notification Type - Setting to set the if you want to show the "Auto Saved" message, and if yes you can show as Notification or as Message in the Status bar (Default is "notification")
	// 		messageType : "notification",

	// 	// Show in the Status Bar
	// 	//messageType : "statusbar",

	// 	// Show no Message
	// 	//messageType : "no",

	// 	// Delay
	// 	delay : 10,

	// 	// The Default Diff Type for the Compare Dialog, you can choose between "sideBySide" or "inline". Default is "sideBySide"
	// 	diffType : "sideBySide",

	// 	// autoLoad when enabled it directly loads the saved content
	// 	autoLoad: false
	// };
};
