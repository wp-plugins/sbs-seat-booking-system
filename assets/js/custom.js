(function($){

    var last_index=9;
    var load_config={};
    var load_data={};
    var canvas_config={};
    var event_scheduled=[];
    var file_frame;
    function triggerdrop(e,ui)
    {
        var dobj = $(ui.draggable);
        var offset = $("#dropdiv").offset();
        var x = e.pageX-offset.left-5;
        var y = e.pageY-offset.top-5;
        var id = "pl"+new Date().getTime();
        var widget = "<div class='widget' id='"+id+"'><img class='full_width' src='"+dobj.attr("src")+"'/></div>";
        $("#dropdiv").append(widget);
        $("#"+id).css({
            "top":y,
            "left":x,
            "width":200,
            "height":200,
            "position":"absolute",
            "z-index":last_index+1,
            "overflow":"hidden"
        });
        attachedDrag(id);
        attachedResize(id);
        AttachConfig(id);
        AttachEditPanel(id);
        SaveConfig();
        loadEditPanel(id);
    }
    function attachedDrag(id)
    {
        $("#"+id).draggable({
            start:function(){
                last_index+=1;
                $("#"+id).css({
                    "zIndex":last_index
                });
            },
            stop:function(){
                var offset = $("#"+id).offset();
                $($("#"+id)).css({
                    "zIndex":(last_index+1)
                });
                AttachConfig(id);
                SaveConfig();
            },
            drag:function(){

            }
        });
    }
    function attachedResize(id)
    {
        $("#"+id).resizable(
        {
            aspectRatio:true,
            stop: function(event, ui) 
            {
                AttachConfig(id);
                SaveConfig();
            }
        });
    }  
    function AttachConfig(id)
    {
        var js_config={};
        js_config["left"]=$("#"+id).css("left");
        js_config["top"]=$("#"+id).css("top");
        js_config["zIndex"]=$("#"+id).css("zIndex");
        js_config["width"]=$("#"+id).css("width");
        js_config["height"]=$("#"+id).css("height");
        js_config["position"]=$("#"+id).css("position");
        js_config["src"]=$("#"+id+" img").attr("src");
        load_config[id]=js_config;
    }
    function resetForm()
    {
        $(".to_select").each(function()
            {
                if($(this).attr("id")=="widget_price")
                {
                    $(this).val(0);        
                }
                else
                {
                    $(this).val(""); 
                }
            }
        );
    }
    function loadEditPanel(id)
    {
        if(load_data[id]!=undefined)
        {
           for(key in load_data[id])
           {
               $("#"+key).val(load_data[id][key]);
           } 
        }
        else
        {
            resetForm();
        }
        $("#widget_id").val(id);
        $(".edit_panel").show();
        $("#wd_settings").trigger("click");
    }
    function AttachEditPanel(id)
    {
        var str="<div class='edit_link_area' id='edit_link_area_"+id+"'><ul><li><a class='edit_link' data-id='"+id+"'>Edit</a></li><li><a class='delete_link' data-id='"+id+"' >Delete</a></li></ul></div>";
        $("#"+id).append(str);
        $(".edit_link").click(function()
        {
            loadEditPanel($(this).attr("data-id"));
        });
        $(".delete_link").click(function()
        {
            DeleteWidget($(this).attr("data-id"));
        });
    }
    function SaveConfig()
    { 
        $("#load_config").val(JSON.stringify(load_config));
        $("#load_data").val(JSON.stringify(load_data));
        $("#last_index").val(last_index);
        $("#room_schedule").val(JSON.stringify(event_scheduled));
        $(".edit_panel").hide();
    }
    function loadConfig()
    {
        for(key in load_config)
        {
            //alert(key);
            var widget = "<div class='widget' id='"+key+"'><img class='full_width' src='"+load_config[key]["src"]+"'/></div>";
            $("#dropdiv").append(widget);
            $("#"+key).css({
                "top":load_config[key]["top"],
                "left":load_config[key]["left"],
                "width":load_config[key]["width"],
                "height":load_config[key]["height"],
                "position":load_config[key]["position"],
                "zIndex":load_config[key]["zIndex"],
                "overflow":"hidden"    
            });
            attachedDrag(key);
            attachedResize(key);
            AttachEditPanel(key);
        }
    }
    function DeleteWidget(id)
    {
        var r=confirm("Are you sure you want to delete this widget?");
        if (r == true) {
            console.log(load_config[id]);

            delete load_config[id];
            delete load_data[id];
            SaveConfig();
            $("#"+id).remove();
        }

    }

    function SetBGProperty()
    {
        $(".page_config").each(function()
        {
             if($(this).attr("type") == "radio")
            {
                if($(this).attr("checked"))
                {
                    canvas_config[$(this).attr("id")]=$(this).val();
                }
                else
                {
                    delete canvas_config[$(this).attr("id")];
                }
            }
            else if($(this).attr("type") == "checkbox")
            {
                if($(this).attr("checked"))
                {
                    canvas_config[$(this).attr("id")]=$(this).val();
                }
                else
                {
                    delete canvas_config[$(this).attr("id")];
                }
            }
            else
            {
                canvas_config[$(this).attr("id")]=$(this).val();
            }
        });
        $("#canvas_config").val(JSON.stringify(canvas_config));
        $("#last_index").val(last_index);
        loadBGFromSettings();
    }
    function loadBGFromSettings()
    {
        var bg_repeat=[];
        for(var key in canvas_config)
        {
            if($("#"+key).attr("type") == "text")
            {
                $("#"+key).val(canvas_config[key]);
                $("#dropdiv").css($("#"+key).attr("data"),$("#"+key).val()+"px");
            }
            else if($("#"+key).attr("type") == "radio" || $("#"+key).attr("type") == "checkbox")
            {
                $("#"+key).attr("checked","checked");
                if($("#"+key).attr("data") == 'background-repeat')
                {
                    bg_repeat.push($("#"+key).val());
                }
                else
                {
                    $("#dropdiv").css($("#"+key).attr("data"),$("#"+key).val());
                }
            }
            else
            {
                $("#"+key).val(canvas_config[key]);
                $("#dropdiv").css($("#"+key).attr("data"),$("#"+key).val());
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
    function formateDate(date_v)
    {
        var str=String(date_v);  
        var parts=str.split(" ");
        delete parts[parts.length-1];
        delete parts[0];
        parts=parts.join(" ");
        var d = new Date(parts);
        var n = d.getMonth(); 
        return d.getFullYear()+"-"+("0" + (d.getMonth() + 1)).slice(-2)+"-"+("0" + d.getDate()).slice(-2)+"T"+("0" + d.getHours()).slice(-2)+":"+("0" + d.getMinutes()).slice(-2)+":"+("0" + d.getSeconds()).slice(-2);
    }
    
    function UpdateEvent(ev)
    {
        for(var key in event_scheduled)
        {
            if(ev.id == event_scheduled[key]["id"])
            {
                event_scheduled[key]["start"]=ev.start.format();
                event_scheduled[key]["end"]=ev.end.format();
                console.log(event_scheduled);
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
    
    function checkEventConflictwithId(start_time,end_time,id)
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
    function checkEventConflict(start_time,end_time)
    {
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
        return conflict;
    }
        $(document).ready(function()
        {
            $("#drag_app").hide();
            if($("#product-type").val() == 'reserve')
            {
                $("#_regular_price").val(0);
                    $("#drag_app").show();
                    $( "#accordion" ).accordion();
            }   
            $("#product-type").change(function()
            {
                if($(this).val() == "reserve")
                {
                    $("#_regular_price").val(0);
                    $("#drag_app").show();
                    $( "#accordion" ).accordion();
                }
                else
                {
                    $("#drag_app").hide();    
                }
            });
            $(".position_radio").click(function()
            {
                if($(this).attr("checked"))
                {
                    SetBGProperty();
                }
            });
            $("#repeat-x,#repeat-y").click(function()
            {
                SetBGProperty(); 
            });
            
            if($("#load_config").val() != "")
            {
                load_config=JSON.parse($("#load_config").val());
            }
            if($("#load_data").val() != "")
            {
                load_data=JSON.parse($("#load_data").val());
            }
            if($("#canvas_config").val() != "")
            {
                canvas_config=JSON.parse($("#canvas_config").val());
            }
            if($("#last_index").val() != "")
            {
                last_index=parseInt($("#last_index").val());
            }
            if($("#room_schedule").val() != "")
            {
                event_scheduled=JSON.parse($("#room_schedule").val());
            }
            loadBGFromSettings();
            loadConfig();
            $("#dropdiv").droppable({
                            accept:".to_drag",
                            drop:function(e,ui){
                                try{
                                    triggerdrop(e,ui);

                                }
                                catch(err){
                                    //alert(err.toString());
                                }
                            }
                        });
            $("#dropdiv").disableSelection();
            $(".to_drag").draggable({zIndex:10000,helper: 'clone',
                start:function(e,ui){

                },
                stop:function(e,ui){

                }
            });  
            $("#save_this").click(function()
            {
                var params={};
                $(".to_select").each(function()
                {
                    params[$(this).attr("id")]=$(this).val();
                });
                
               $.post(rmd.ajaxurl+'?action=rmd_create_resource',params,function(data)
               {
                    params["resource_id"]=data.resource_id;
                    load_data[$("#widget_id").val()]=params;
                    SaveConfig();
               },"json");
                
            });
            $(".page_property").keyup(function()
            {
                if($.isNumeric($(this).val()))
                {   
                    if($(this).attr("data") == "width")
                    {
                        $("#dropdiv").css({"width":$(this).val()+"px"});
                    }   
                    else
                    {
                        $("#dropdiv").css({"height":$(this).val()+"px"});
                    }
                    SetBGProperty();
                }
                
            });
            
                $('#calendar').fullCalendar({
                            header: {
                                    left: 'prev,next today',
                                    center: 'title',
                                    right: 'month,agendaWeek,agendaDay'
                            },
                            
                            defaultDate: '2014-09-12',
                            selectable: true,
                            selectHelper: true,
                            eventDrop: function(event, delta, revertFunc) {
                                if(checkEventConflictwithId(event.start.format(),event.end.format(),event.id) == 0)
                                {
                                    UpdateEvent(event);
                                    SaveConfig();
                                }
                                else
                                {
                                    revertFunc();
                                }
                            },
                            eventResize: function(event, delta, revertFunc) {
                                if(checkEventConflictwithId(event.start.format(),event.end.format(),event.id) == 0)
                                {
                                    UpdateEvent(event);
                                    SaveConfig();
                                }
                                else
                                {
                                    revertFunc();
                                }
                            },
                            select: function(start, end) {
                                    if(checkEventConflict(start.format(),end.format()) == 0)
                                    {
                                        var ev_id = "admin_"+new Date().getTime();
                                        event_scheduled.push({id:ev_id,
                                                       title:"Scheduled",
                                                       start:start.format(),
                                                       end:end.format()
                                                      });
                                        var eventData;
                                        eventData = {
                                                id:ev_id,
                                                title: "Scheduled",
                                                start: start,
                                                end: end
                                        };
                                        $('#calendar').fullCalendar('renderEvent', eventData, true);
                                        SaveConfig();
                                    }
                                    $('#calendar').fullCalendar('unselect');
                            },
                            editable: true,
                            eventLimit: false, // allow "more" link when too many events
                            eventRender: function(event, element) {
                                /*element.qtip({
                                    content: event.description
                                });*/
                                /*if(event.id == 100)
                                {
                                    event.editable = false;
                                   // event.disableDragging();
                                   // event.disableResizing();
                                }*/
                                //element.css("background-color", "red");
                            },
                            events:event_scheduled
                    });
            
            
            $('#upload_image_button').live('click', function( podcast ){

                    podcast.preventDefault();

                    // If the media frame already exists, reopen it.
                    if ( file_frame ) {
                    file_frame.open();
                    return;
                    }

                    // Create the media frame.
                    file_frame = wp.media.frames.file_frame = wp.media({
                    title: $( this ).data( 'uploader_title' ),
                    button: {
                    text: $( this ).data( 'uploader_button_text' ),
                    },
                    multiple: false // Set to true to allow multiple files to be selected
                    });

                    // When a file is selected, run a callback.
                    file_frame.on( 'select', function() {
                    // We set multiple to false so only get one image from the uploader
                    attachment = file_frame.state().get('selection').first().toJSON();

                    // here are some of the variables you could use for the attachment;
                    //var all = JSON.stringify( attachment );      
                    //var id = attachment.id;
                    //var title = attachment.title;
                    //var filename = attachment.filename;
                    var url = attachment.url;
                    //var link = attachment.link;
                    //var alt = attachment.alt;
                    //var author = attachment.author;
                    //var description = attachment.description;
                    //var caption = attachment.caption;
                    //var name = attachment.name;
                    //var status = attachment.status;
                    //var uploadedTo = attachment.uploadedTo;
                    //var date = attachment.date;
                    //var modified = attachment.modified;
                    //var type = attachment.type;
                    //var subtype = attachment.subtype;
                    //var icon = attachment.icon;
                    //var dateFormatted = attachment.dateFormatted;
                    //var editLink = attachment.editLink;
                    //var fileLength = attachment.fileLength;

                    //var field = document.getElementById("page_width");

                    //field.value =url;
                    //alert(url);
                    //setBg(url);
                    $("#background_image_panel").val("url('"+url+"')");
                    SetBGProperty();
                    //set which variable you want the field to have
                    });

                    // Finally, open the modal
                    file_frame.open();
                    });
                    
        });
    })(jQuery);
