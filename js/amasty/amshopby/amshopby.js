function amshopby_start(){
    $$('.block-layered-nav .form-button').each(function (e){
        e.observe('click', amshopby_price_click_callback);
    });

    $$('.block-layered-nav .input-text').each(function (e){
        e.observe('focus', amshopby_price_focus_callback);
        e.observe('keypress', amshopby_price_click_callback);
    });
    
    $$('a.amshopby-less', 'a.amshopby-more').each(function (a){
        a.observe('click', amshopby_toggle);
    });
    
    $$('span.amshopby-plusminus').each(function (span){
        span.observe('click', amshopby_category_show);
    });
    
    $$('.block-layered-nav dt').each(function (dt){
        dt.observe('click', amshopby_filter_show);
    });
	
    
	
    $$('.block-layered-nav dt img').each(function (img){
        img.observe('mouseover', amshopby_tooltip_show);
        img.observe('mouseout', amshopby_tooltip_hide);
    });
    
    $$('.amshopby-slider-param').each(function (item) {
        param = item.value.split(',');
        amshopby_slider(param[0], param[1], param[2], param[3], param[4], param[5], param[6], param[7]);
    });
} 

function amshopby_price_click_callback(evt, decimal) {
    
    if (evt && evt.type == 'keypress' && 13 != evt.keyCode)
        return;
    
    var prefix = 'amshopby-price';
    // from slider
    if (typeof(evt) == 'string'){
        prefix = evt;    
    } 
    else {
        var el = Event.findElement(evt, 'input');
        if (!Object.isElement(el)){
            el = Event.findElement(evt, 'button');
        }
        prefix = el.name;
    }  
    
    var a = prefix + '-from';
    var b = prefix + '-to';
    
    var numFrom = amshopby_price_format($(a).value, decimal);
    var numTo   = amshopby_price_format($(b).value, decimal);
  
    if ((numFrom < 0.01 && numTo < 0.01) || numFrom < 0 || numTo < 0) {   
        return;   
    }
    
    var url =  $(prefix +'-url').value.gsub(a, numFrom).gsub(b, numTo);
    amshopby_set_location(url);
}

function amshopby_price_focus_callback(evt){
    var el = Event.findElement(evt, 'input');
    if (isNaN(parseFloat(el.value))){
        el.value = '';
    } 
}


function amshopby_price_format(num, decimal){
    num = parseFloat(num);

    if (isNaN(num))
        num = 0;
        
    if (decimal >= 0) { 
        return num.toFixed(decimal);
    } else {
        return Math.round(num); 
    }
}


function amshopby_slider(width, step, from, to, max_value, prefix, min_value, ratePP, decimal) {
    
    width = parseFloat(width);
    from = parseFloat(from);
    to = parseFloat(to);
    max_value = parseFloat(max_value);
    min_value = parseFloat(min_value);
    step = parseFloat(step);

    numArr = Math.round(parseFloat(width/step));

    var slider = $(prefix); 
         var allowedVals = new Array(step + 1);
         for (var i=0; i<allowedVals.length; ++i){
             allowedVals[i] = Math.round(i * numArr);
         }
       
    return new Control.Slider(slider.select('.handle'), slider, {
        range: $R(0, width),
        sliderValue: [from, to],
        restricted: true,
        values: allowedVals,
        
        onChange: function (values){
            this.onSlide(values);  
            amshopby_price_click_callback(prefix, decimal);  
        },
        onSlide: function(values) {

            var fromValue = (min_value + ratePP * values[0]).toFixed(decimal);
            var toValue   = (min_value + ratePP * values[1]).toFixed(decimal);

            if ($(prefix + '-slider-bar')) {
                var barWidth = values[1] - values[0] - 1;
                if (values[1] == values[0]) {
                    barWidth = 0;
                }
                $(prefix + '-slider-bar').setStyle({
                    width : barWidth + 'px',
                    left : values[0] + 'px'
                });
            }
          
          
            if ($(prefix+'-from')) {
                $(prefix+'-from').value = fromValue;
                $(prefix+'-to').value   = toValue;
            }

            if ($(prefix + '-from-slider')) {
                $(prefix + '-from-slider').update('Rp'+parseInt(fromValue).toLocaleString());
                $(prefix + '-to-slider').update('Rp'+parseInt(toValue).toLocaleString());
            }
        }
    });

}

function amshopby_toggle(evt){
    var attr = Event.findElement(evt, 'a').id.substr(14);
    
    $$('.amshopby-attr-' + attr).invoke('toggle');       
        
    $('amshopby-less-' + attr, 'amshopby-more-' + attr).invoke('toggle');
    
    Event.stop(evt);
    return false;
}

function amshopby_category_show(evt){
    var span = Event.findElement(evt, 'span');
    var id = span.id.substr(16);
    
    $$('.amshopby-cat-parentid-' + id).invoke('toggle');

    span.toggleClassName('minus'); 
    Event.stop(evt);
          
    return false;
}

function amshopby_filter_show(evt){
    var dt = Event.findElement(evt, 'dt');
    dt.next('dd').toggle();
    dt.toggleClassName('amshopby-collapsed'); 
    dt.next('dd').toggleClassName('relative'); 
	
  
    Event.stop(evt);
    return false;
}

function amshopby_tooltip_show(evt){
    var img = Event.findElement(evt, 'img');
    var txt = img.alt;
    
    var tooltip = $(img.id + '-tooltip');
    if (!tooltip) {
        tooltip           = document.createElement('div');
        tooltip.className = 'amshopby-tooltip';
        tooltip.id        = img.id + '-tooltip';
        tooltip.innerHTML = img.alt;
    
        document.body.appendChild(tooltip);
    }
    
    var offset = Element.cumulativeOffset(img);
    tooltip.style.top  = offset[1] + 'px';
    tooltip.style.left = (offset[0] + 30) + 'px';
    tooltip.show();
}

function amshopby_tooltip_hide(evt){
    var img = Event.findElement(evt, 'img');
    var tooltip = $(img.id + '-tooltip');
    if (tooltip) {
        tooltip.hide();
    }    
}

function amshopby_set_location(url){
    if (typeof amshopby_working != 'undefined'){
        return amshopby_ajax_request(url);    
    }
    else {
        return setLocation(url);
    }
}

function amshopby_attr_highlight(li, str)
{
	/* 
	 * Remove previous highlight
	 */
	amshopby_attr_unhighlight(li);
	
	var ch = li.childElements();
	if (ch.length >  0) {
		ch = ch[0];
		if (ch.tagName == 'A') {
			ch.innerHTML = ch.innerHTML.replace(new RegExp(str,'gi'), '<span class="amshopby-hightlighted">' + str + '</span>');
		}
	}
}

function amshopby_attr_unhighlight(li)
{
	var ch = li.childElements();
	if (ch.length > 0) {
		ch = ch[0];
		if (ch.tagName == 'A') {
			ch.innerHTML = li.readAttribute('rel');
		}
	}
}


function amshopby_attr_search(searchBox){
    var str = searchBox.value.toLowerCase();
    var all = searchBox.up(1).childElements();

    all.each(function(li) {
		val = li.readAttribute('rel').toLowerCase();
        if (!val || val == 'search' || val.indexOf(str) > -1) {
        	if (str != '' && val.indexOf(str) > -1) {
        		amshopby_attr_highlight(li, str);
        	} else {
        		amshopby_attr_unhighlight(li);	
        	}
            li.show();
        } 
        else {
        	amshopby_attr_unhighlight(li);
            li.hide();
        }
	});
}


document.observe("dom:loaded", function() { amshopby_start(); }); 