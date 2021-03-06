/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'forms' },
		{ name: 'tools' },
		{ name: 'others' },
		{ name: 'scrolls_icons' },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'blocks', 'align', 'bidi' ] },
		{ name: 'styles' },

	];

	// Remove some buttons, provided by the standard plugins, which we don't
	// need to have in the Standard(s) toolbar.


	// Se the most common block elements.
	config.format_tags = 'p;h1;h2;h3';

	// Make dialogs simpler.
	config.removeDialogTabs = 'image:advanced;link:advanced';
	
	config.extraPlugins = 'scrolldier';
	config.allowedContent = true;
};


CKEDITOR.plugins.add('scrolldier',
{
    init: function(editor)
    {
        var pluginName = 'scrolldier';
        
        editor.addCommand( 'decay', {
               exec: function( editor ) {
                   CKEDITOR.instances.editor.insertHtml( '<i class="icon-decay">&nbsp;</i>' );
               }
           } );
       editor.addCommand( 'order', {
              exec: function( editor ) {
                  CKEDITOR.instances.editor.insertHtml( '<i class="icon-order">&nbsp;</i>' );
              }
          } );
      
      editor.addCommand( 'energy', {
             exec: function( editor ) {
                 CKEDITOR.instances.editor.insertHtml( '<i class="icon-energy">&nbsp;</i>' );
             }
         } );
         
         
     editor.addCommand( 'growth', {
            exec: function( editor ) {
                CKEDITOR.instances.editor.insertHtml( '<i class="icon-growth">&nbsp;</i>' );
            }
        } );
     editor.addCommand( 'wild-old', {
            exec: function( editor ) {
                CKEDITOR.instances.editor.insertHtml( '<i class="icon-wild-old">&nbsp;</i>' );
            }
        } );
    editor.addCommand( 'wild', {
           exec: function( editor ) {
               CKEDITOR.instances.editor.insertHtml( '<i class="icon-wild">&nbsp;</i>' );
           }
       } );
   editor.addCommand( 'coin', {
          exec: function( editor ) {
              CKEDITOR.instances.editor.insertHtml( '<i class="icon-coin">&nbsp;</i>' );
          }
      } );
   editor.addCommand( 'shard', {
          exec: function( editor ) {
              CKEDITOR.instances.editor.insertHtml( '<i class="icon-shard">&nbsp;</i>' );
          }
      } );    
        
        editor.ui.addButton('decay_icon',
            {
                label: 'Decay Icon',
                command: 'decay',
                icon : CKEDITOR.plugins.getPath('scrolldier') + 'd.png',
                toolbar: "scrolls_icons,1"
            });
       
       editor.ui.addButton('order_icon',
           {
               label: 'Order Icon',
               command: 'order',
               icon : CKEDITOR.plugins.getPath('scrolldier') + 'o.png',
               toolbar: "scrolls_icons,2"
           });
       editor.ui.addButton('energy_icon',
           {
               label: 'Energy Icon',
               command: 'energy',
               icon : CKEDITOR.plugins.getPath('scrolldier') + 'e.png',
               toolbar: "scrolls_icons,3"
           });
       editor.ui.addButton('growth_icon',
           {
               label: 'Growth Icon',
               command: 'growth',
               icon : CKEDITOR.plugins.getPath('scrolldier') + 'g.png',
               toolbar: "scrolls_icons,4"
           });
      editor.ui.addButton('wild_icon_old',
          {
              label: 'Wild Icon',
              command: 'wild-old',
              icon : CKEDITOR.plugins.getPath('scrolldier') + 'w.png',
              toolbar: "scrolls_icons,5"
          });
      editor.ui.addButton('wild_icon',
          {
              label: 'Wild Icon',
              command: 'wild',
              icon : CKEDITOR.plugins.getPath('scrolldier') + 'w2.png',
              toolbar: "scrolls_icons,6"
          });
          
          
          
      editor.ui.addButton('coin_icon',
          {
              label: 'Gold Coin Icon',
              command: 'coin',
              icon : CKEDITOR.plugins.getPath('scrolldier') + 'c.png',
              toolbar: "scrolls_icons,7"
          });
      editor.ui.addButton('shard_icon',
          {
              label: 'Void Shard Icon',
              command: 'shard',
              icon : CKEDITOR.plugins.getPath('scrolldier') + 'v.png',
              toolbar: "scrolls_icons,8"
          });    
    }
});




