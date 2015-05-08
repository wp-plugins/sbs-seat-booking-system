(function($){
        var event_scheduled=[];
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
        //alert(start_time+"=="+end_time);
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
        //alert(conflict);
        return conflict;
    }
    
    function checkEventConflict(start_time,end_time)
    {
        //alert(start_time+"=="+end_time);
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
        //alert(conflict);
        return conflict;
    }

    function SaveConfig()
    { 
        $(".regular-text-hidden").val(JSON.stringify(event_scheduled));
        //$(".regular-text-hidden").val("");
    }
        $(document).ready(function()
        {
          
                if($(".regular-text-hidden").val() != "")
                {
                    event_scheduled=JSON.parse($(".regular-text-hidden").val());
                }
                $('#calendar_').fullCalendar({
                            header: {
                                    left: 'prev,next today',
                                    center: 'title',
                                    right: 'month,agendaDay'
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
                                        
                                        $('#calendar_').fullCalendar('renderEvent', eventData, true);
                                        SaveConfig();
                                    }
                                    $('#calendar_').fullCalendar('unselect');
                            },
                            editable: true,
                            eventLimit: false, // allow "more" link when too many events
                            eventRender: function(event, element) {
                                
                            },
                            events:event_scheduled
                    });

                    $('#calendar_').fullCalendar('today');    
                    
        });
    })(jQuery);
