(function($){
    var load_config={};
    var load_data={};
    var canvas_config={};
    var event_scheduled=[];
    var event_booked=[];
    var event_created=0;
    var resource_schedule=[];
    var params={"page_width":"width",
            "page_height":"height",  
    "background_image_panel":"background-image",
                        "background-position1":"background-position",
                        "background-position2":"background-position",
                        "background-position3":"background-position",
                        "background-position4":"background-position",
                        "background-position5":"background-position",
                        "background-position6":"background-position",
                        "background-position7":"background-position",
                        "background-position8":"background-position",
                                    "repeat-x":"background-repeat",
                                    "repeat-y":"background-repeat"
                                 };
    $('p.booking_cost').hide();
    $('.product-type-reserve .price').hide();


    /* -------------------------------------------------------------------------
        MEDIA QUERY BREAKPOINT
    ------------------------------------------------------------------------- */

    var uouMediaQueryBreakpoint = function() {

        if ( $( '#media-query-breakpoint' ).length < 1 ) {
            $( 'body' ).append( '<var id="media-query-breakpoint"><span></span></var>' );
        }
        var value = $( '#media-query-breakpoint' ).css( 'content' );
        if ( typeof value !== 'undefined' ) {
            value = value.replace( "\"", "" ).replace( "\"", "" ).replace( "\'", "" ).replace( "\'", "" );
            if ( isNaN( parseInt( value, 10 ) ) ){
                $( '#media-query-breakpoint span' ).each(function(){
                    value = window.getComputedStyle( this, ':before' ).content;
                });
                value = value.replace( "\"", "" ).replace( "\"", "" ).replace( "\'", "" ).replace( "\'", "" );
            }
            if(isNaN(parseInt(value,10))){
                value = 1199;
            }
        }
        else {
            value = 1199;
        }
        return value;

    };

    // SELECT BOX
    $.fn.uouSelectBox = function(){

        var self = $(this),
        select = self.find( 'select' );
        self.prepend( '<ul class="select-clone custom-list"></ul>' );

        var placeholder = select.data( 'placeholder' ) ? select.data( 'placeholder' ) : select.find( 'option:eq(0)' ).text(),
        clone = self.find( '.select-clone' );
        self.prepend( '<input class="value-holder" type="text" disabled="disabled" placeholder="' + placeholder + '"><i class="fa fa-chevron-down"></i>' );
        var value_holder = self.find( '.value-holder' );

        // INPUT PLACEHOLDER FIX FOR IE
        if ( $.fn.placeholder ) {
            self.find( 'input, textarea' ).placeholder();
        }
        select.find( 'option' ).each(function(){
            if ( $(this).attr( 'value' ) ){
                clone.append( '<li data-value="' + $(this).val() + '">' + $(this).text() + '</li>' );
            }
        });
        self.click(function(){
            var media_query_breakpoint = uouMediaQueryBreakpoint();
            if ( media_query_breakpoint > 991 ) {
                clone.slideToggle(100);
                self.toggleClass( 'active' );
            }
            setTimeInHiddenV();
            TriggerChecking();
        });
        clone.find( 'li' ).click(function(){

            value_holder.val( $(this).text() );
            select.find( 'option[value="' + $(this).attr( 'data-value' ) + '"]' ).attr('selected', 'selected');

            // IF LIST OF LINKS
            if ( self.hasClass( 'links' ) ) {
                window.location.href = select.val();
            }

        });
        self.bind( 'clickoutside', function(event){
            clone.slideUp(100);
        });

        // LIST OF LINKS
        if ( self.hasClass( 'links' ) ) {
            select.change( function(){
                window.location.href = select.val();
            });
        }

    };
    function RemoveEventFromBook(event_id,w_id)
    {
        for(var  i in event_booked)
        {
            if(event_booked[i]["id"] == event_id)
            {
                delete event_booked[i];
                updateCartTable(w_id);
                break; 
            }
        }
        saveInSession();
    }
    function updateCartTable(w_id)
    {
        var str="";
        for(var  i in event_booked)
        {
            str+="<tr id='"+event_booked[i]["id"]+"'><td>"+load_data[event_booked[i]["w_id"]]["widget_title"]+"</td><td>"+event_booked[i]["start"]+"</td><td>"+event_booked[i]["end"]+"</td><td><a data-id='"+event_booked[i]["id"]+"' data-object='"+event_booked[i]["w_id"]+"' class='rm_event' style='cussor:pointer;'>Remove</a></td></tr>";
        }
        $("#schedule_table").html(str);
        $(".rm_event").click(function()
        {
            RemoveEventFromBook($(this).attr("data-id"),$(this).attr("data-object"));
        });
        saveInSession();
    }
    function CheckWithSchedule(start_time,end_time)
    {
        var found=0;
        start_time=parseInt(Date.parse(start_time));
        end_time=parseInt(Date.parse(end_time));
        for(var key in event_scheduled)
        {
            //alert(event_scheduled[key]["start"]+" == "+Date.parse(event_scheduled[key]["start"]));
            if(start_time >=parseInt(Date.parse(event_scheduled[key]["start"])) && end_time <= parseInt(Date.parse(event_scheduled[key]["end"])))
            {
                found=1;
                break;
            }
        }
        return found;
    }
    function UpdateEvent(ev)
    {
        for(var key in event_booked)
        {
            if(ev.id == event_booked[key]["id"])
            {
                event_booked[key]["start"]=ev.start.format();
                event_booked[key]["end"]=ev.end.format();
                break;
            }
        }
    }
    function checkOverlap(b1,e1,b2,e2)
    {
        var conflict=0;
        if(e2 > b1 && e1 > b2)
        {
            conflict=1;
        }
        return conflict;
    }
    function checkEventConflictwithId(start_time,end_time,id,event_scheduled)
    {
        var conflict=0;
        start_time=parseInt(Date.parse(start_time));
        end_time=parseInt(Date.parse(end_time));
        for(var key in event_scheduled)
        {
            if(id == event_scheduled[key]["id"])
            {
                continue;
            }
            if(checkOverlap(start_time,end_time,parseInt(Date.parse(event_scheduled[key]["start"])),parseInt(Date.parse(event_scheduled[key]["end"]))) == 1)
            {
                conflict=1;
                break;
            }
        } 
        return conflict;
    }
    function checkEventConflict(start_time,end_time,event_scheduled)
    {
        var widget_id="";
        if(event_scheduled.length > 0)
        {
            widget_id=event_scheduled[0]["w_id"];
        }
        var conflict=0;
        start_time=parseInt(Date.parse(start_time));
        end_time=parseInt(Date.parse(end_time));
        for(var key in event_scheduled)
        {
            if(checkOverlap(start_time,end_time,parseInt(Date.parse(event_scheduled[key]["start"])),parseInt(Date.parse(event_scheduled[key]["end"]))) == 1)
            {
                conflict=1;
                break;
            }
        } 
        return {"conflict":conflict,
                "widget_id":widget_id
               };
    }    
    function LoadPreview()
    {
        var _temp = [];
        for(key in load_config)
        {
            var widget = "<div class='widget_canvas' data='"+load_data[key]["resource_id"]+"' id='"+key+"' data-price='"+load_data[key]["widget_price"]+"'><img class='full_width' src='"+load_config[key]["src"]+"'/><div class='tool-tip  slideIn'><strong>" + load_data[key]["widget_title"] + "</strong><hr> <span class='tipprice'>Price: " + $('#currency_symbol').val() + " " + load_data[key]["widget_price"] + "</span><br>" + load_data[key]["widget_description"] + " </div></div>";
            $("#dropdiv").append(widget);
            $("#"+key).css({
                "top":load_config[key]["top"],
                "left":load_config[key]["left"],
                "width":load_config[key]["width"],
                "height":load_config[key]["height"],
                "position":load_config[key]["position"],
                "zIndex":load_config[key]["zIndex"],
                "margin":0,
                "padding":0,
                "cursor":"pointer"
            });

            _temp.push(load_data[key]["widget_price"]);
        }
        //console.log(_temp);

        var max = Math.max.apply(Math,_temp);
        var min = Math.min.apply(Math,_temp);
        var price;
        if(max != '0') {
            price = $('#currency_symbol').val() + min + " - " + $('#currency_symbol').val() + max;
        }
        $('.single-product .product-type-reserve .price').show().html(price);
        //console.log(min);
        //console.log(max);
    }
    
    function loadBGFromSettings()
    {
        var bg_repeat=[];
        //alert(params["page_width"]);
        for(var key in canvas_config)
        {
            if(params[key] == "width" || params[key] == "height")
            {
                $("#dropdiv").css(params[key],canvas_config[key]+"px");
            }
            else if(params[key] == 'background-repeat')
            {
                bg_repeat.push(canvas_config[key]);
            }    
            else
            {
                $("#dropdiv").css(params[key],canvas_config[key]);
            }
        }
        if(bg_repeat.length >1)
        {
            $("#dropdiv").css("background-repeat","repeat");
        }
        else if(bg_repeat.length >0)
        {
            $("#dropdiv").css("background-repeat",bg_repeat[0]);
        }
        else
        {
            $("#dropdiv").css("background-repeat","no-repeat");
        }
    }

    function saveInSession()
    {
        $.post(rmd.ajaxurl+'?action=rmd_saveIn_session',{"room_schedule":JSON.stringify(event_booked)
                                                                   },function(data)
                                                                   {
                                                                       //event_created=0;
                                                                       //$('#myModal').modal('hide');
                                                                       //event_booked=[];
                                                                   });
    }
    function convertWidthHeight(c_width,c_height,w_width,w_height,offset_widget)
    {
        var n_width=(w_width/parseInt(canvas_config["page_width"]))*c_width;
        var n_height=(w_height/parseInt(canvas_config["page_height"]))*c_height;
        var n_left=(offset_widget.left/parseInt(canvas_config["page_width"]))*c_width;
        var n_top=(offset_widget.top/parseInt(canvas_config["page_height"]))*c_height;
        //alert(n_width);
        return {"n_width":n_width,
                "n_height":n_height,
                "n_left":n_left,
                "n_top":n_top
               };
    }
    function ResizeCanvas(window_width)
    {
        $("#room_canvas").html("<div id='dropdiv'></div>");
        loadBGFromSettings();
        LoadPreview();
        var n_height=(parseInt(canvas_config["page_height"])/parseInt(canvas_config["page_width"]))*window_width;
        $("#dropdiv").width(window_width);
        $("#dropdiv").height(n_height);
        $(".widget_canvas").each(function()
        {
            var left_in_px=$(this).css("left");
            left_in_px=left_in_px.split("px");
            var top_in_px=$(this).css("top");
            top_in_px=top_in_px.split("px");
            var converted_wh=convertWidthHeight(window_width,n_height,$(this).width(),$(this).height(),{"left":parseInt(left_in_px[0]),"top":parseInt(top_in_px[0])});
            $(this).width(converted_wh["n_width"]);
            $(this).height(converted_wh["n_height"]);
            $(this).css({"left":converted_wh["n_left"]+"px","top":converted_wh["n_top"]+"px"});
        });
    }
    function checkResponsive(window_width)
    {
        window_width=(90/100)*window_width;

        if(window_width < parseInt(canvas_config["page_width"]))
        {
            ResizeCanvas(window_width);
        }
    }
    function FormatDate(to_convert)
    {
        return to_convert.replace(" ", "T");     
    }

    function CheckWithExistEvent(w_id,start_time,end_time)
    {
        var found=0;
        for(var i in event_booked)
        {
            if((event_booked[i]["w_id"] == w_id) && (event_booked[i]["start"] == FormatDate(start_time)) && (event_booked[i]["end"] == FormatDate(end_time)))
            {
                found=1;
            }    
        }
        return found;
    }

    function setTimeInHiddenV(){

        if($("#book_date").val() != "" && $("#start_in_time").val() != "" && $("#end_in_time").val() != "")
        {
            $("#start_time").val($("#book_date").val()+" "+$("#start_in_time").val());
            $("#end_time").val($("#book_date").val()+" "+$("#end_in_time").val());
        }
        else
        {
            $("#start_time").val("");
            $("#end_time").val("");
        }
    }
    function TriggerChecking()
    {
        //alert(Date.parse(FormatDate($("#start_time").val())));
        if(($("#start_time").val() != "" && $("#end_time").val() != "") && (parseInt(Date.parse(FormatDate($("#end_time").val()))) > parseInt(Date.parse(FormatDate($("#start_time").val())))))
        {
            var start_var=$("#start_time").val();
            var end_var=$("#end_time").val();
            var parts1=start_var.split(" ");
            var parts2=end_var.split(" ");
            var img_parts;
            if(CheckWithSchedule(parts1.join("T"),parts2.join("T")) == 1)
            {
                var triggered_ids=[];
                for(var i in resource_schedule)
                {
                    var even_conflict_ids=checkEventConflict(parts1.join("T"),parts2.join("T"),resource_schedule[i]); 
                    if(even_conflict_ids.conflict == 0)
                    {
                        triggered_ids.push(even_conflict_ids.widget_id);
                        img_parts=$("#"+even_conflict_ids.widget_id+" img").attr("src");
                        img_parts=img_parts.split("/");
                        $("#"+even_conflict_ids.widget_id+" img").attr("src",objects_color.green+""+img_parts[img_parts.length-1]);
                        $("#"+even_conflict_ids.widget_id).unbind();
                        $("#"+even_conflict_ids.widget_id).click(function()
                        {
                            BindEvent(this);
                        });
                    }    
                    else
                    {
                        triggered_ids.push(even_conflict_ids.widget_id);
                        img_parts=$("#"+even_conflict_ids.widget_id+" img").attr("src");
                        img_parts=img_parts.split("/");
                        $("#"+even_conflict_ids.widget_id+" img").attr("src",objects_color.red+""+img_parts[img_parts.length-1]);
                    }
                }
                $(".widget_canvas").each(function()
                {
                    
                        if($.inArray($(this).attr("id"),triggered_ids) == -1)
                        {
                            img_parts=$("img",this).attr("src");
                            img_parts=img_parts.split("/");
                            $("img",this).attr("src",objects_color.green+""+img_parts[img_parts.length-1]);
                            $(this).unbind();
                            $(this).click(function()
                            {
                                BindEvent(this);
                            });
                        }    
                    
                });
            }
            else
            {
                //console.log(objects_color);
                $(".widget_canvas").each(function()
                {
                     img_parts=$("img",this).attr("src");
                     img_parts=img_parts.split("/");
                     $("img",this).attr("src",objects_color.grey+""+img_parts[img_parts.length-1]);
                });
            }
            for(var i in event_booked)
            {
                
                if(checkOverlap(FormatDate($("#start_time").val()),FormatDate($("#end_time").val()),event_booked[i]["start"],event_booked[i]["end"]) == 1)
                {
                    if((FormatDate($("#start_time").val())==event_booked[i]["start"]) && (FormatDate($("#end_time").val())==event_booked[i]["end"]))
                    {
                        img_parts=$("#"+event_booked[i]["w_id"]+" img").attr("src");
                        img_parts=img_parts.split("/");
                        $("#"+event_booked[i]["w_id"]+" img").attr("src",objects_color.orange+""+img_parts[img_parts.length-1]);
                        $("#"+event_booked[i]["w_id"]).unbind();
                        $("#"+event_booked[i]["w_id"]).click(function()
                        {
                            UnsetEvent(this);
                        });
                    }
                    else
                    {
                        img_parts=$("#"+event_booked[i]["w_id"]+" img").attr("src");
                        img_parts=img_parts.split("/");
                        $("#"+event_booked[i]["w_id"]+" img").attr("src",objects_color.red+""+img_parts[img_parts.length-1]);
                    }
                    
                }
            }

        }
        
    }
    var temp_price = 0;
    function BindEvent(obj)
    {
        if(CheckWithExistEvent($(obj).attr("id"),$("#start_time").val(),$("#end_time").val())==0)
        {
            var ev_id = "booked_"+new Date().getTime();
            event_booked.push({id:ev_id,
                            title:load_data[$(obj).attr("id")]["widget_title"],
                            start:FormatDate($("#start_time").val()),
                              end:FormatDate($("#end_time").val()),
                       resouce_id:load_data[$(obj).attr("id")]["resource_id"],
                       price:load_data[$(obj).attr("id")]["widget_price"],
                             w_id:$(obj).attr("id")
                            });
            var parts=$("img",obj).attr("src");
            parts=parts.split("/");

            
            temp_price += parseInt(load_data[$(obj).attr("id")]["widget_price"]);
            $('p.booking_cost').show();
            $('span.total_cost').html(temp_price);
            $('#reserve_price').val(temp_price);
            // $('').html('<span class="amount">'+load_data[$(obj).attr("id")]["widget_price"]+'</span>');
            // $('meta[itemprop=price]').attr('content', load_data[$(obj).attr("id")]["widget_price"]);
            
            $("img",obj).attr("src",objects_color.orange+""+parts[parts.length-1]);
            saveInSession();
            $(obj).unbind();
            $(obj).click(function()
            {
                UnsetEvent(obj);
                
            });
        }     
        if(event_booked.length > 0)
        {
            $(".single_add_to_cart_button").attr("disabled",false);
        }  
        else
        {
            $(".single_add_to_cart_button").attr("disabled",true);
        }                             
    }
    function UnsetEvent(obj)
    {
        if(temp_price > 0 )
        {
            temp_price -=parseInt(load_data[$(obj).attr("id")]["widget_price"]);
            $('span.total_cost').html(temp_price);
            $('#reserve_price').val(temp_price);
        }
        for(var i in event_booked)
        {
            if((event_booked[i]["start"] == FormatDate($("#start_time").val())) && (event_booked[i]["end"] == FormatDate($("#end_time").val())) && (event_booked[i]["w_id"] == $(obj).attr("id")))
            {
                event_booked.splice(i,1);
                TriggerChecking();
                break; 
            }
        }
        saveInSession();
        if(event_booked.length > 0)
        {
            $(".product-type-reserve .single_add_to_cart_button").attr("disabled",false);
        }  
        else
        {
            $(".product-type-reserve .single_add_to_cart_button").attr("disabled",true);
        } 
    }
    $(document).ready(function()
    {
        $( '.select-box' ).each(function(){
        $(this).uouSelectBox();
        });
        $(".product-quantity").hide();
        $(".variation-wdm_user_custom_data").hide();
        $(".single_add_to_cart_button").attr("disabled",true);
        $(".single_add_to_cart_button").css("margin-top","10px");
        $.post(rmd.ajaxurl+'?action=rmd_process',{"post_id":post.post_id},function(data)
        {
            load_config=data.load_config;
            load_data=data.load_data;
            canvas_config=data.canvas_config;
            event_scheduled=data.room_schedule;
            resource_schedule=data.resource_schedule;
            $("#room_canvas").html("<div id='dropdiv'></div>");
            loadBGFromSettings();
            LoadPreview();
            checkResponsive($("#dropdiv").parent().width());
            $('.calendar-input' ).each(function(){

                var input = $(this).find( 'input' ),
                dateformat = input.data( 'dateformat' ) ? input.data( 'dateformat' ) : 'yy-mm-dd',
                icon = $(this).find( '.fa' ),
                widget = input.datepicker( 'widget' );
                input.datepicker({
                    dateFormat: dateformat,
                    minDate: 0,
                    beforeShow: function(){
                        input.addClass( 'active' );
                    },
                    onClose: function(){
                        input.removeClass( 'active' );
                        // TRANSPLANT WIDGET BACK TO THE END OF BODY IF NEEDED
                        widget.hide();
                        setTimeInHiddenV();
                        TriggerChecking();
                        if ( ! widget.parent().is( 'body' ) ) {
                            widget.detach().appendTo( $( 'body' ) );
                        }
                    }
                });
                icon.click(function(){
                    input.focus();
                });
                });
        },"json");
        $(window).resize(function()
        {
            checkResponsive($("#dropdiv").parent().width());
        });
    });
})(jQuery);
