
var container_markup = '<div data-id="${id}" data-type="${type}" data-style="${style}" class="container display ${type} clearfix">'+
        	'<div class="c-tools">'+
                '<span role="sort-handle" class="c-sort-handle c-tool"></span>'+
                '<span role="trash" class="c-trash c-tool"></span>'+
				'<span role="edit" class="c-edit c-tool"></span>'+
				'<span role="box-trash" class="c-box-trash"></span>'+
            '</div>'+
			'<p class="c-title">${title}</p>'+
        	'<div class="c-before">'+
				'<p class="c-titleurl">${titleurl}</p>'+
            	'<p class="c-prolog">${prolog}</p>'+
            '</div>'+
        	'<div class="slots-wrapper clearfix">{{if slots}}{{tmpl(slots) "slotTemplate"}}{{/if}}</div>'+
            '<div class="c-after">'+
            	'<p class="c-epilog">${epilog}</p>'+
				'<p><span class="c-readmore">${readmore}</span>'+
					'{{if readmoreurl}} [ <span class="c-readmoreurl">${readmoreurl}</span> ]'+
					' {{else}} <span class="c-readmoreurl">${readmoreurl}</span>{{/if}}</p>'+
				'<p class="c-style">${styleTitle}</p>'+
            '</div>'+
        '</div>';
jQuery.template( "containerTemplate", container_markup );

var container_editor_markup = '<div data-id="${id}" data-type="${type}" class="container editor ${type} clearfix">'+
			'<div class="c-tools">'+
                '<span role="ok" class="c-ok c-tool"></span>'+
				'<span role="revert" class="c-revert c-tool"></span>'+
                '<span role="sort-handle" class="c-sort-handle c-tool"></span>'+
                '<span role="trash" class="c-trash c-tool"></span>'+
				'<span role="box-trash" class="c-box-trash"></span>'+
            '</div>'+
        	'<div class="c-before">'+
            	'<label for="f-c-title">Title:</label>'+
                '<input type="text" name="f-c-title" id="f-c-title" value="${title}" class="form-text" />'+
                '<label for="f-c-titleurl">Titel URL:</label>'+
                '<input type="text" name="f-c-titleurl" id="f-c-titleurl" value="${titleurl}" class="form-text" />'+
                '<label for="f-c-prolog">Prolog:</label>'+
                '<textarea name="f-c-prolog" id="f-c-prolog" class="form-textarea">${prolog}</textarea>'+
            '</div>'+
        	'<div class="slots-wrapper clearfix">'+
				// slots hier anheften
            '</div>'+
            '<div class="c-after">'+
                '<label for="f-c-epilog">Epilog:</label>'+
        		'<textarea name="f-c-epilog" id="f-c-epilog" class="form-textarea">${epilog}</textarea> ' +              
				'<div class="clearfix">'+
					'<fieldset>'+
						'<label for="f-c-readmore">Readmore Text:</label>'+
						'<input type="text" name="f-c-readmore" id="f-c-readmore" value="${readmore}" class="form-text" />'+
					'</fieldset>'+
					'<fieldset>'+
						'<label for="f-c-readmoreurl">Readmore URL:</label>'+
						'<input type="text" name="f-c-readmoreurl" id="f-c-readmoreurl" value="${readmoreurl}" class="form-text" />'+
					'</fieldset>'+
					'<fieldset>'+
						'<label for="f-c-style">Style</label>'+ 
						'<select name="f-c-style" id="f-c-style" class="form-select">'+
							'<option value="">ohne style</option>'+
							'{{if styles}}{{each styles}}'+
								'<option {{if $value.slug == style }}selected="selected"{{/if}} value="${$value.slug}">${$value.title}</option>'+
							'{{/each}}{{/if}}'
					   '</select>'
					'</fieldset>'+
				'</div>'+
			'</div>'+
        '</div>';
jQuery.template( "containerEditorTemplate", container_editor_markup );

var slot_markup = '<div class="slot" data-id="${id}" data-style="${style}">'+
						'<div class="style-changer">'+
							'<span>{{if style}}${style}{{else}}ohne Style{{/if}}</span>'+
							//'<ul class="choose-style"></ul>'+
						'</div>'+
						'<div class="boxes-wrapper">{{if boxes}}{{tmpl(boxes) "boxTemplate"}}{{/if}}</div>'+
				'</div>';
jQuery.template( "slotTemplate", slot_markup );

var box_markup = '<div class="box" data-id="${id}" data-type="${type}">'+
						'<h3><a href="${titleurl}" class="box-title">${title}</a></h3>'+
						'<span class="edit"></span>'+
                        '<div class="prolog">{{html prolog}}</div>'+
                        '<div class="content">{{html html}}</div>'+
                        '<div class="epilog">{{html epilog}}</div>'+
                        '<p class="readmore"><a href="${readmoreurl}">${readmore}</a></p>'+
				'</div>';
jQuery.template( "boxTemplate", box_markup );

var box_draggable_markup = '<li class="box-dragger" data-id="${id}" data-type="${type}" data-index="${index}"'+
							' data-titleurl="${titleurl}" data-readmore="${readmore}" data-readmoreurl="${readmoreurl}">'+
							'<div class="prolog">{{html prolog}}</div>'+
							'<div class="handle"></div><div class="title">${title}</div>'+
							'<div class="content">{{html html}}</div>'+
							'<div class="epilog">{{html epilog}}</div>'+
							'</li>';
jQuery.template( "boxDraggableTemplate", box_draggable_markup );

var box_drop_place = "<div class=''><div></div></div>";
jQuery.template( 'boxDropPlace', box_drop_place );

var box_editor_markup = '<div class="box-editor" data-b-index="${b_index}" data-id="${box.id}" '+
							'data-c-id="${c_id}" data-s-id="${s_id}" data-type="${box.type}">'+
							'<fieldset class="collapsable dynamic-fields">'+
								'<legend>Konfiguration</legend>'+
								'<div class="field-wrapper"></div>'+
								// inputs for types added by script
							'</fieldset>'+
							'<fieldset class="collapsable collapsable-hidden text-fields">'+
								'<legend>Texte</legend>'+
								'<div class="field-wrapper">'+
									'<label>Titel</label>'+
									'<input name="f-b-title" type="text" value="${box.title}" class="form-text" /> '+
									'<label>URL</label>'+
									'<input name="f-b-titleurl" type="text" value="${box.titleurl}" class="form-text" />'+

									'<label>Prolog</label>'+
									'<textarea name="f-b-prolog" class="form-textarea">${box.prolog}</textarea>'+
									'<label>Epilog</label>'+
									'<textarea name="f-b-epilog"  class="form-textarea">${box.epilog}</textarea>'+

									'<label>Readmore</label>'+
									'<input name="f-b-readmore" type="text" value="${box.readmore}" class="form-text" /> '+
									'<label>URL</label>'+
									'<input name="f-b-readmoreurl" type="text" value="${box.readmoreurl}" class="form-text" />'+

									'<label for="f-b-style">Style</label>'+ 
									'<select name="f-b-style" class="form-select"  id="f-b-style">'+
										'<option value="">ohne style</option>'+
										'{{if styles}}{{each styles}}'+
											'<option {{if $value.slug == box.style }}selected="selected"{{/if}} value="${$value.slug}">'+
											'${$value.title}</option>'+
										'{{/each}}{{/if}}'+
									'</select>'+
								'</div>'+
							'</fieldset>'+
						'</div>';
jQuery.template("boxEditorTemplate", box_editor_markup);

var in_box_autocomplete_markup = "<div class='"+
									"{{if val != ''}}locked {{/if}}"+
									"autocomplete-wrapper form-autocomplete dynamic-value' >"+
									"<input type='text' class='form-text autocomplete' {{if val !=''}}disabled=disabled {{/if}}"+
									"data-key='${key}' data-type='${type}' data-value-key='${val}' value='${label}' />"+
									"<div class='loading rotate'></div>"+
									"<div class='cancle'></div>"+
									"<ul class='suggestion-list'></ul>"+
								"</div>";
jQuery.template("inBoxAutocompleteTemplate", in_box_autocomplete_markup);